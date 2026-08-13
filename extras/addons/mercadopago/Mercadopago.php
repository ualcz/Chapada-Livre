<?php

namespace extras\addons\mercadopago;

use App\Helpers\Common\Num;
use App\Helpers\Services\Payment;
use App\Models\Package;
use App\Models\Post;
use App\Models\User;
use extras\addons\mercadopago\app\Traits\InstallTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Throwable;

class Mercadopago extends Payment
{
	use InstallTrait;

	/**
	 * Send Payment to Mercado Pago (Creates Preference for Checkout Pro)
	 * The user is redirected to Mercado Pago's hosted page where they can pay
	 * with PIX, credit card, or boleto.
	 *
	 * @param Request $request
	 * @param Post|User $payable
	 * @param array $resData
	 * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
	 */
	public static function sendPayment(Request $request, Post|User $payable, array $resData = [])
	{
		// Set right URLs
		parent::setRightUrls($resData);

		// Get the Package
		$package = Package::find($request->input('package_id'));

		// Don't make payment if price <= 0
		if (empty($package) || $package->price <= 0) {
			return redirect()->to(parent::$uri['previousUrl'] . '?error=package')->withInput();
		}

		// Don't make payment if package is not compatible with payable
		if (!parent::isPayableCompatibleWithPackage($payable, $package)) {
			return redirect()->to(parent::$uri['previousUrl'] . '?error=packageType')->withInput();
		}

		$amount       = Num::toFloat($package->price);
		$currencyCode = !empty($package->currency_code) ? strtoupper($package->currency_code) : 'BRL';

		// Session & Callbacks
		$sessionId = session()->getId();

		$paymentReturnUrl  = parent::$uri['paymentReturnUrl'];
		$paymentReturnUrl .= str_contains($paymentReturnUrl, '?') ? '&' : '?';
		$paymentReturnUrl .= 'sessionId=' . $sessionId;

		$paymentCancelUrl  = parent::$uri['paymentCancelUrl'];
		$paymentCancelUrl .= str_contains($paymentCancelUrl, '?') ? '&' : '?';
		$paymentCancelUrl .= 'sessionId=' . $sessionId;

		// Notification URL for Webhooks
		$notificationUrl = route('mercadopago.webhook');

		// External Reference — carries all context needed when the webhook fires
		$externalReference = json_encode([
			'payable_id'        => $payable->id,
			'payable_type'      => get_class($payable),
			'package_id'        => $package->id,
			'payment_method_id' => $request->input('payment_method_id'),
			'promotion_time'    => $package->promotion_time ?? 30,
			'token'             => uniqid('mp_', true),
		]);

		// Build Checkout Pro preference — all payment methods enabled (PIX, card, boleto)
		$preferenceData = [
			'items' => [
				[
					'id'          => 'package_' . $package->id,
					'title'       => str($package->name)->limit(120)->toString(),
					'description' => str($package->description_string ?? $package->name)->limit(250)->toString(),
					'quantity'    => 1,
					'currency_id' => $currencyCode,
					'unit_price'  => $amount,
				],
			],
			'payer' => [
				'name' => $payable->contact_name ?? $payable->name ?? 'Cliente',
			],
			'back_urls' => [
				'success' => $paymentReturnUrl,
				'failure' => $paymentCancelUrl,
				'pending' => $paymentReturnUrl,
			],
			'auto_return'        => 'approved',
			'notification_url'   => $notificationUrl,
			'external_reference' => $externalReference,
		];

		// Local Parameters for LaraClassifier session
		$localParams = parent::getLocalParameters($request, $payable, $package);

		try {
			$accessToken = config('payment.mercadopago.accessToken');
			if (empty($accessToken)) {
				return parent::paymentFailureActions($payable, 'Mercado Pago Access Token missing in configuration.');
			}

			// Create preference via Mercado Pago API
			$response = Http::withToken($accessToken)
				->post('https://api.mercadopago.com/checkout/preferences', $preferenceData);

			if (!$response->successful()) {
				$errorMessage = $response->json('message') ?? trans('mercadopago::messages.error_creating_preference');
				return parent::paymentFailureActions($payable, $errorMessage);
			}

			$responseData = $response->json();
			$mode         = config('payment.mercadopago.mode', 'sandbox');

			// In sandbox mode use sandbox_init_point; in production use init_point
			$paymentUrl = ($mode === 'sandbox' && !empty($responseData['sandbox_init_point']))
				? $responseData['sandbox_init_point']
				: ($responseData['init_point'] ?? null);

			if (empty($paymentUrl)) {
				return parent::paymentFailureActions($payable, trans('mercadopago::messages.payment_page_url_not_found'));
			}

			// Store preference ID as transaction_id for later lookup
			if (isset($responseData['id'])) {
				$localParams['transaction_id'] = $responseData['id'];
			}

			// Persist session params so paymentConfirmation can retrieve them on return
			session()->put('params', $localParams);
			session()->save();

			// For API/React requests: return the Checkout Pro URL so the frontend can redirect the user
			if (isApiRoute()) {
				$resData['extra']['payment']['success'] = true;
				$resData['extra']['paymentUrl']         = $paymentUrl;
				$resData['extra']['payment']['result']  = [
					'preference_id' => $responseData['id'] ?? null,
					'redirect_url'  => $paymentUrl,
				];
				return apiResponse()->json($resData);
			}

			// For standard web requests: redirect directly to Mercado Pago Checkout Pro
			return redirect()->away($paymentUrl);

		} catch (Throwable $e) {
			return parent::paymentApiErrorActions($payable, $e);
		}
	}

	/**
	 * Confirm payment after user returns from Mercado Pago via back_url
	 *
	 * @param Post|User $payable
	 * @param array $params
	 * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
	 */
	public static function paymentConfirmation(Post|User $payable, array $params)
	{
		parent::$uri = parent::replacePatternsInUrls($payable, parent::$uri);

		$request   = request();
		$paymentId = $request->input('payment_id') ?? $request->input('collection_id');
		$status    = $request->input('status') ?? $request->input('collection_status');

		if (!empty($paymentId)) {
			$params['transaction_id'] = $paymentId;
		}

		// Verify payment status directly with Mercado Pago API
		if (!empty($paymentId)) {
			try {
				$accessToken = config('payment.mercadopago.accessToken');
				if (!empty($accessToken)) {
					$response = Http::withToken($accessToken)
						->get("https://api.mercadopago.com/v1/payments/{$paymentId}");

					if ($response->successful()) {
						$mpStatus = $response->json('status');
						if (!empty($mpStatus)) {
							$status = $mpStatus;
						}
					}
				}
			} catch (Throwable $e) {
				// Fallback to query parameter status if API call fails
			}
		}

		if (in_array($status, ['approved', 'authorized'])) {
			return parent::paymentConfirmationActions($payable, $params);
		} elseif (in_array($status, ['pending', 'in_process'])) {
			flash(trans('mercadopago::messages.payment_pending'))->warning();
			return parent::paymentConfirmationActions($payable, $params);
		} else {
			return parent::paymentFailureActions($payable, 'Pagamento não aprovado ou cancelado.');
		}
	}
}

// Alias so any code referencing PascalCase MercadoPago still works
if (!class_exists('extras\\addons\\mercadopago\\MercadoPago', false)) {
	class_alias(Mercadopago::class, 'extras\\addons\\mercadopago\\MercadoPago');
}
