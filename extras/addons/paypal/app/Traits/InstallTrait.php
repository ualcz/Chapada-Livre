<?php

namespace extras\addons\paypal\app\Traits;

use App\Models\PaymentMethod;
use Throwable;

trait InstallTrait
{
	/**
	 * @return array
	 */
	public static function getOptions(): array
	{
		$options = [];
		
		$paymentMethod = PaymentMethod::isActive()->where('name', 'paypal')->first();
		
		if (!empty($paymentMethod)) {
			$options[] = (object)[
				'name'     => mb_ucfirst(trans('admin.settings')),
				'url'      => urlGen()->adminUrl('payment-methods/' . $paymentMethod->id . '/edit'),
				'btnClass' => 'btn-info',
			];
		}
		
		return $options;
	}
	
	/**
	 * @return bool
	 */
	public static function installed(): bool
	{
		$cacheExpiration = 86400; // Cache for 1 day (60 * 60 * 24)
		
		$paymentMethod = cache()->remember('addons.paypal.installed', $cacheExpiration, function () {
			return PaymentMethod::isActive()->where('name', 'paypal')->first();
		});
		
		return !empty($paymentMethod);
	}
	
	/**
	 * @return bool
	 */
	public static function install(): bool
	{
		// Remove the addon entry
		self::uninstall();
		
		// Addon data
		$data = [
			'id'                => 1,
			'name'              => 'paypal',
			'display_name'      => 'PayPal',
			'description'       => 'Payment with PayPal',
			'has_ccbox'         => 0,
			'is_compatible_api' => 0,
			'countries'         => null,
			'lft'               => 0,
			'rgt'               => 0,
			'depth'             => 1,
			'active'            => 1,
		];
		
		try {
			// Create addon data
			$paymentMethod = PaymentMethod::create($data);
			if (empty($paymentMethod)) {
				return false;
			}
			
			// Copy public folder contents to public/cache/addons/paypal/
			self::copyPublicAssets('paypal', __DIR__ . '/../../public');
		} catch (Throwable $e) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @return bool
	 */
	public static function uninstall(): bool
	{
		try {
			cache()->forget('addons.paypal.installed');
		} catch (Throwable $e) {
		}
		
		// Remove copied public assets
		self::removePublicAssets('paypal');
		
		$paymentMethod = PaymentMethod::where('name', 'paypal')->first();
		
		if (!empty($paymentMethod)) {
			$deletedResult = $paymentMethod->delete();
			
			return ($deletedResult > 0);
		}
		
		return true;
	}
}
