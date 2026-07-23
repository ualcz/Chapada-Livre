<?php

namespace extras\addons\mercadopago\app\Traits;

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
		
		$paymentMethod = PaymentMethod::isActive()->where('name', 'mercadopago')->first();
		
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
		
		$paymentMethod = cache()->remember('addons.mercadopago.installed', $cacheExpiration, function () {
			return PaymentMethod::isActive()->where('name', 'mercadopago')->first();
		});
		
		return !empty($paymentMethod);
	}
	
	/**
	 * @return bool
	 */
	public static function install(): bool
	{
		// Remove existing entry
		self::uninstall();
		
		// Addon data
		$data = [
			'name'              => 'mercadopago',
			'display_name'      => 'Mercado Pago',
			'description'       => 'Mercado Pago (Cartão de Crédito e PIX)',
			'has_ccbox'         => 0,
			'is_compatible_api' => 1,
			'countries'         => 'BR',
			'lft'               => 0,
			'rgt'               => 0,
			'depth'             => 1,
			'active'            => 1,
		];
		
		try {
			$paymentMethod = PaymentMethod::create($data);
			if (empty($paymentMethod)) {
				return false;
			}
			
			self::copyPublicAssets('mercadopago', __DIR__ . '/../../public');
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
			cache()->forget('addons.mercadopago.installed');
		} catch (Throwable $e) {
		}
		
		self::removePublicAssets('mercadopago');
		
		$paymentMethod = PaymentMethod::where('name', 'mercadopago')->first();
		
		if (!empty($paymentMethod)) {
			$deletedResult = $paymentMethod->delete();
			
			return ($deletedResult > 0);
		}
		
		return true;
	}
}
