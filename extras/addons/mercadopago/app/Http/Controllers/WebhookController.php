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
	 *
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function handleNotification(Request $request): JsonResponse
	{
		try {
			// Extract payment ID from various possible MP payload formats
			$paymentId = $request->input('data.id')
				?? $request->input('id')
				?? $request->input('data_id');

			$topic = $request->input('type') ?? $request->input('topic');

			if (empty($paymentId) || ($topic && !in_array($topic, ['payment', 'merchant_order']))) {
				return response()->json(['status' => 'ignored', 'message' => 'Not a payment event'], 200);
			}

			$accessToken = config('payment.mercadopago.accessToken');
			if (empty($accessToken)) {
				Log::error('MercadoPago Webhook: Access token not configured.');
				return response()->json(['status' => 'error', 'message' => 'Config error'], 500);
			}

			// Query Mercado Pago API for payment details
			$response = Http::withToken($accessToken)
				->get("https://api.mercadopago.com/v1/payments/{$paymentId}");

			if (!$response->successful()) {
				Log::error("MercadoPago Webhook: Failed to fetch payment {$paymentId}", ['body' => $response->body()]);
				return response()->json(['status' => 'error', 'message' => 'API request failed'], 400);
			}

			$paymentData = $response->json();
			$status = $paymentData['status'] ?? null;
			$externalRefRaw = $paymentData['external_reference'] ?? null;

			if ($status !== 'approved' || empty($externalRefRaw)) {
				return response()->json([
					'status'  => 'info',
					'message' => "Payment status is {$status}",
				], 200);
			}

			// Decode external reference
			$refData = json_decode($externalRefRaw, true);
			if (!is_array($refData) || empty($refData['payable_id']) || empty($refData['payable_type'])) {
				Log::warning('MercadoPago Webhook: Invalid external reference format.', ['raw' => $externalRefRaw]);
				return response()->json(['status' => 'error', 'message' => 'Invalid reference'], 400);
			}

			// Locate payable model (Post or User)
			$payableType = $refData['payable_type'];
			$payableId = $refData['payable_id'];

			if (str_ends_with($payableType, 'Post')) {
				$payable = Post::find($payableId);
			} elseif (str_ends_with($payableType, 'User')) {
				$payable = User::find($payableId);
			} else {
				$payable = null;
			}

			if (empty($payable)) {
				Log::warning("MercadoPago Webhook: Payable {$payableType} #{$payableId} not found.");
				return response()->json(['status' => 'error', 'message' => 'Payable not found'], 404);
			}

			// Check if payment was already recorded
			$existingPayment = Payment::query()
				->where('transaction_id', (string)$paymentId)
				->first();

			if (!empty($existingPayment)) {
				return response()->json(['status' => 'already_processed'], 200);
			}

			// Build parameters for LaraClassifier Payment::register
			$params = [
				'package' => [
					'id'            => $refData['package_id'] ?? null,
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

			PaymentService::register($payable, $params);

			Log::info("MercadoPago Webhook: Payment {$paymentId} successfully registered for {$payableType} #{$payableId}.");

			return response()->json(['status' => 'success'], 200);
		} catch (Throwable $e) {
			Log::error('MercadoPago Webhook Exception: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
			return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
		}
	}
}
