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

		$amount = Num::toFloat($package->price);
		$currencyCode = !empty($package->currency_code) ? strtoupper($package->currency_code) : 'BRL';

		// Session & Callbacks
		$sessionId = session()->getId();

		$paymentReturnUrl = parent::$uri['paymentReturnUrl'];
		$paymentReturnUrl .= str_contains($paymentReturnUrl, '?') ? '&' : '?';
		$paymentReturnUrl .= 'sessionId=' . $sessionId;

		$paymentCancelUrl = parent::$uri['paymentCancelUrl'];
		$paymentCancelUrl .= str_contains($paymentCancelUrl, '?') ? '&' : '?';
		$paymentCancelUrl .= 'sessionId=' . $sessionId;

		// Notification URL for Webhooks
		$notificationUrl = route('mercadopago.webhook');

		// External Reference payload
		$externalReference = json_encode([
			'payable_id'        => $payable->id,
			'payable_type'      => get_class($payable),
			'package_id'        => $package->id,
			'payment_method_id' => $request->input('payment_method_id'),
			'promotion_time'    => $package->promotion_time ?? 30,
			'token'             => uniqid('mp_', true),
		]);

		// Build Mercado Pago Preference Request Payload
		// Restricting payment methods to Credit Card & PIX for MVP (excluding ticket/boleto & atm)
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
				'name'  => $payable->contact_name ?? $payable->name ?? 'Cliente',
				'email' => $payable->email ?? auth()->user()?->email ?? 'cliente@chapadalivre.com.br',
			],
			'payment_methods' => [
				'excluded_payment_types' => [
					['id' => 'ticket'], // Excludes Boleto
					['id' => 'atm'],    // Excludes ATM/Cash
				],
			],
			'back_urls' => [
				'success' => $paymentReturnUrl,
				'failure' => $paymentCancelUrl,
				'pending' => $paymentReturnUrl,
			],
			'auto_return'          => 'approved',
			'notification_url'     => $notificationUrl,
			'external_reference'   => $externalReference,
		];

		// Local Parameters for LaraClassifier session
		$localParams = parent::getLocalParameters($request, $payable, $package);

		try {
			$accessToken = config('payment.mercadopago.accessToken');
			if (empty($accessToken)) {
				return parent::paymentFailureActions($payable, 'Mercado Pago Access Token missing in configuration.');
			}

			// Call Mercado Pago Preferences API
			$response = Http::withToken($accessToken)
				->post('https://api.mercadopago.com/checkout/preferences', $preferenceData);

			if (!$response->successful()) {
				$errorMessage = $response->json('message') ?? trans('mercadopago::messages.error_creating_preference');
				return parent::paymentFailureActions($payable, $errorMessage);
			}

			$responseData = $response->json();
			$mode = config('payment.mercadopago.mode', 'sandbox');
			$paymentUrl = ($mode === 'sandbox' && !empty($responseData['sandbox_init_point']))
				? $responseData['sandbox_init_point']
				: ($responseData['init_point'] ?? null);

			if (empty($paymentUrl)) {
				$errorMessage = trans('mercadopago::messages.payment_page_url_not_found');
				return parent::paymentFailureActions($payable, $errorMessage);
			}

			// Save preference ID in transaction_id for session tracking
			if (isset($responseData['id'])) {
				$localParams['transaction_id'] = $responseData['id'];
			}

			// Save parameters into session
			session()->put('params', $localParams);
			session()->save();

			if (isApiRoute()) {
				$resData['extra']['paymentUrl'] = $paymentUrl;
				$resData['extra']['payment']['success'] = true;
				$resData['extra']['payment']['result'] = [
					'preference_id' => $responseData['id'] ?? null,
					'redirect_url'  => $paymentUrl,
				];
				return apiResponse()->json($resData);
			} else {
				redirectUrl($paymentUrl);
			}
		} catch (Throwable $e) {
			return parent::paymentApiErrorActions($payable, $e);
		}
	}

	/**
	 * Confirm payment after user returns from Mercado Pago back_url
	 *
	 * @param Post|User $payable
	 * @param array $params
	 * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
	 */
	public static function paymentConfirmation(Post|User $payable, array $params)
	{
		parent::$uri = parent::replacePatternsInUrls($payable, parent::$uri);

		$request = request();
		$paymentId = $request->input('payment_id') ?? $request->input('collection_id');
		$status = $request->input('status') ?? $request->input('collection_status');

		if (!empty($paymentId)) {
			$params['transaction_id'] = $paymentId;
		}

		// Verify payment status with Mercado Pago API if paymentId is available
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
			// Register payment and notify pending status
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
