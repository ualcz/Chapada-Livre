<?php

namespace extras\addons\mercadopago;

use Illuminate\Support\ServiceProvider;

/**
 * LaraClassifier resolves the addon ServiceProvider via:
 *   ucfirst($addon->name) . 'ServiceProvider'
 * With name='mercadopago' this becomes 'MercadopagoServiceProvider'.
 * On Linux (case-sensitive), both the file name AND the class name must match
 * exactly — hence this file is named MercadopagoServiceProvider.php with
 * a lowercase 'p' in 'pago'.
 */
class MercadopagoServiceProvider extends ServiceProvider
{
	/**
	 * Boot of services.
	 */
	public function boot(): void
	{
		// Load addon views
		if (file_exists(__DIR__ . '/resources/views')) {
			$this->loadViewsFrom(realpath(__DIR__ . '/resources/views'), 'payment');
		}
		
		// Load addon translations
		$this->loadTranslationsFrom(realpath(__DIR__ . '/lang'), 'mercadopago');
		
		// Merge addon config
		$this->mergeConfigFrom(realpath(__DIR__ . '/config.php'), 'payment');
		
		// Load routes
		if (file_exists(__DIR__ . '/routes/web.php')) {
			$this->loadRoutesFrom(__DIR__ . '/routes/web.php');
		}
	}
	
	/**
	 * Register services.
	 */
	public function register(): void
	{
		$this->app->bind('mercadopago', fn () => new Mercadopago());
	}
}

if (!class_exists('extras\\addons\\mercadopago\\MercadoPagoServiceProvider', false)) {
    class_alias(MercadopagoServiceProvider::class, 'extras\\addons\\mercadopago\\MercadoPagoServiceProvider');
}
