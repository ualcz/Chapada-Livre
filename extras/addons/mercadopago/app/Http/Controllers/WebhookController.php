<?php

namespace extras\addons\mercadopago\app\Http\Controllers;

use App\Helpers\Services\Payment as PaymentService;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WebhookController extends Controller
{
	/**
	 * Handle Mercado Pago Webhook / IPN Notification
	 * Supports both direct payment notifications and Checkout Pro merchant order notifications.
	 *
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function handleNotification(Request $request): JsonResponse
	{
		$topic = $request->input('type') ?? $request->input('topic');

		Log::info('MercadoPago Webhook received', [
			'topic'   => $topic,
			'payload' => $request->all(),
		]);

		// --- Checkout Pro: Merchant Order notification ---
		// type = "topic_merchant_order_wh" or topic = "merchant_order"
		if (in_array($topic, ['topic_merchant_order_wh', 'merchant_order'])) {
			$orderId = $request->input('id') ?? $request->input('data.id');
			if (empty($orderId)) {
				return response()->json(['status' => 'ignored', 'message' => 'No merchant order ID'], 200);
			}
			return $this->processMerchantOrder((string)$orderId);
		}

		// --- Direct Payment notification ---
		// type = "payment" or topic = "payment"
		$paymentId = $request->input('data.id')
			?? $request->input('id')
			?? $request->input('data_id');

		if (empty($paymentId)) {
			return response()->json(['status' => 'ignored', 'message' => 'No payment ID found'], 200);
		}

		if ($topic && !in_array($topic, ['payment', 'merchant_order', 'topic_merchant_order_wh'])) {
			return response()->json(['status' => 'ignored', 'message' => 'Not a payment event'], 200);
		}

		return $this->processPayment((string)$paymentId);
	}

	/**
	 * Handle a Checkout Pro Merchant Order by fetching its payments.
	 * When using Checkout Pro, Mercado Pago sends a merchant_order notification
	 * instead of a direct payment notification.
	 *
	 * @param string $orderId
	 * @return JsonResponse
	 */
	private function processMerchantOrder(string $orderId): JsonResponse
	{
		try {
			$accessToken = config('payment.mercadopago.accessToken');
			if (empty($accessToken)) {
				Log::error('MercadoPago: Access token not configured for merchant order processing.');
				return response()->json(['status' => 'error', 'message' => 'Config error'], 500);
			}

			// Fetch the merchant order from Mercado Pago
			$response = Http::withToken($accessToken)
				->get("https://api.mercadopago.com/merchant_orders/{$orderId}");

			if (!$response->successful()) {
				Log::error("MercadoPago: Failed to fetch merchant order {$orderId}", ['body' => $response->body()]);
				return response()->json([
					'status'  => 'error',
					'message' => 'Failed to fetch merchant order from Mercado Pago.',
				], 400);
			}

			$orderData = $response->json();
			$orderStatus = $orderData['status'] ?? null;
			$payments    = $orderData['payments'] ?? [];

			Log::info("MercadoPago: Merchant order {$orderId} fetched", [
				'order_status'    => $orderStatus,
				'payment_count'   => count($payments),
			]);

			// Query each payment inside the merchant order so the webhook still processes
			// payments that arrive in an intermediate state before they become approved.
			$fallbackRef = $orderData['external_reference'] ?? null;
			$processedAnyPayment = false;

			foreach ($payments as $payment) {
				$paymentId = (string)($payment['id'] ?? '');
				if (empty($paymentId)) {
					continue;
				}

				$processedAnyPayment = true;
				Log::info("MercadoPago: Processing payment {$paymentId} from merchant order {$orderId}", [
					'payment_status' => $payment['status'] ?? null,
				]);

				$result     = $this->processPayment($paymentId, $fallbackRef);
				$resultData = $result->getData(true);
				if ($resultData['approved'] ?? false) {
					return $result;
				}
			}

			if (!$processedAnyPayment) {
				return response()->json([
					'status'  => 'pending',
					'message' => "Merchant order {$orderId} processed — no payments available yet (status: {$orderStatus}).",
				], 200);
			}

			return response()->json([
				'status'  => 'pending',
				'message' => "Merchant order {$orderId} processed — payment is still pending or awaiting approval (status: {$orderStatus}).",
			], 200);

		} catch (Throwable $e) {
			Log::error('MercadoPago MerchantOrder Exception: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
			return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
		}
	}

	/**
	 * Endpoint to check payment status directly (used by React polling / manual check)
	 *
	 * @param Request $request
	 * @param string|int $paymentId
	 * @return JsonResponse
	 */
	public function checkStatus(Request $request, $paymentId): JsonResponse
	{
		if (empty($paymentId)) {
			return response()->json(['status' => 'error', 'message' => 'ID de pagamento ausente.'], 400);
		}

		return $this->processPayment((string)$paymentId);
	}

	/**
	 * Process a Mercado Pago payment ID by querying MP API and registering in LaraClassifier
	 *
	 * @param string $paymentId
	 * @return JsonResponse
	 */
	private function processPayment(string $paymentId, ?string $fallbackExternalReference = null): JsonResponse
	{
		try {
			$accessToken = config('payment.mercadopago.accessToken');
			if (empty($accessToken)) {
				Log::error('MercadoPago: Access token not configured.');
				return response()->json(['status' => 'error', 'message' => 'Config error'], 500);
			}

			// Query Mercado Pago API for payment details
			$response = Http::withToken($accessToken)
				->get("https://api.mercadopago.com/v1/payments/{$paymentId}");

			if (!$response->successful()) {
				Log::error("MercadoPago: Failed to fetch payment {$paymentId}", ['body' => $response->body()]);
				return response()->json([
					'approved' => false,
					'status'   => 'error',
					'message'  => 'Não foi possível consultar o pagamento na API do Mercado Pago.',
				], 400);
			}

			$paymentData    = $response->json();
			$status         = $paymentData['status'] ?? null;
			$externalRefRaw = $paymentData['external_reference'] ?? null;

			// If external_reference is missing on the payment, try fallback from merchant_order
			if (empty($externalRefRaw) && !empty($fallbackExternalReference)) {
				$externalRefRaw = $fallbackExternalReference;
				Log::info("MercadoPago: Using fallback external_reference from merchant_order for payment {$paymentId}");
			}

			if ($status !== 'approved') {
				return response()->json([
					'approved' => false,
					'status'   => $status,
					'message'  => "O pagamento ainda está com status '{$status}'. Aguardando aprovação.",
				], 200);
			}

			if (empty($externalRefRaw)) {
				return response()->json([
					'approved' => false,
					'status'   => 'invalid_reference',
					'message'  => 'Referência externa não encontrada no pagamento.',
				], 400);
			}

			// Decode external reference
			$refData = json_decode($externalRefRaw, true);
			if (!is_array($refData) || empty($refData['payable_id']) || empty($refData['payable_type'])) {
				Log::warning('MercadoPago: Invalid external reference format.', ['raw' => $externalRefRaw]);
				return response()->json(['approved' => false, 'status' => 'error', 'message' => 'Formato de referência inválido.'], 400);
			}

			// Locate payable model (Post or User)
			$payableType = $refData['payable_type'];
			$payableId   = $refData['payable_id'];

			if (str_ends_with($payableType, 'Post')) {
				$payable = Post::find($payableId);
			} elseif (str_ends_with($payableType, 'User')) {
				$payable = User::find($payableId);
			} else {
				$payable = null;
			}

			if (empty($payable)) {
				Log::warning("MercadoPago: Payable {$payableType} #{$payableId} not found.");
				return response()->json(['approved' => false, 'status' => 'error', 'message' => 'Anúncio ou usuário não encontrado.'], 404);
			}

			// Check if payment was already recorded
			$existingPayment = Payment::query()
				->where('transaction_id', (string)$paymentId)
				->first();

			if (!empty($existingPayment)) {
				if ((int)($existingPayment->active ?? 0) !== 1) {
					$existingPayment->active = 1;
					$existingPayment->save();
				}
				
				// Ensure payable is marked as featured if not yet marked
				if (isset($payable->featured) && $payable->featured != 1) {
					$payable->featured = 1;
					$payable->save();
				}
				return response()->json([
					'approved' => true,
					'status'   => 'already_processed',
					'message'  => 'Pagamento já processado. Anúncio já está destacado!',
				], 200);
			}

			// Fetch package to get correct type
			$package = \App\Models\Package::find($refData['package_id'] ?? null);
			$packageType = $package->type ?? (str_ends_with($payableType, 'Post') ? 'promotion' : 'subscription');

			// Build parameters for LaraClassifier Payment::register
			$params = [
				'package' => [
					'id'            => $refData['package_id'] ?? null,
					'type'          => $packageType,
					'price'         => $paymentData['transaction_amount'] ?? 0,
					'currency_code' => $paymentData['currency_id'] ?? 'BRL',
					'period_start'  => now()->startOfDay(),
					'period_end'    => now()->addDays((int)($refData['promotion_time'] ?? 30))->endOfDay(),
				],
				'paymentMethod' => [
					'id' => $refData['payment_method_id'] ?? null,
				],
				'transaction_id' => (string)$paymentId,
			];

			PaymentService::$msg['checkout']['success'] = 'Pagamento aprovado e anúncio destacado com sucesso!';
			PaymentService::register($payable, $params);

			// Explicit safeguard: ensure payable is marked featured
			if (isset($payable->featured) && $payable->featured != 1) {
				$payable->featured = 1;
				$payable->save();
			}

			Log::info("MercadoPago: Payment {$paymentId} successfully registered for {$payableType} #{$payableId}.");

			return response()->json([
				'approved' => true,
				'status'   => 'approved',
				'message'  => 'Pagamento aprovado e anúncio destacado com sucesso!',
			], 200);

		} catch (Throwable $e) {
			Log::error('MercadoPago Process Exception: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
			return response()->json(['approved' => false, 'status' => 'error', 'message' => $e->getMessage()], 500);
		}
	}
}
