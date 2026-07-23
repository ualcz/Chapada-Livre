<?php
/*
 * LaraClassifier - Classified Ads Web Application
 * Copyright (c) BeDigit. All Rights Reserved
 *
 * Website: https://laraclassifier.com
 * Author: Mayeul Akpovi (BeDigit - https://bedigit.com)
 *
 * LICENSE
 * -------
 * This software is provided under a license agreement and may only be used or copied
 * in accordance with its terms, including the inclusion of the above copyright notice.
 * As this software is sold exclusively on CodeCanyon,
 * please review the full license details here: https://codecanyon.net/licenses/standard
 */

namespace App\Providers;

use App\Providers\AddonsService\AddonsTrait;
use Illuminate\Support\ServiceProvider;

class AddonServiceProvider extends ServiceProvider
{
	use AddonsTrait;
	
	/**
	 * Perform post-registration booting of services.
	 *
	 * @return void
	 */
	public function boot(): void
	{
		// Load the addons
		$this->loadAddons();
	}
	
	/**
	 * Register any package services.
	 *
	 * @return void
	 */
	public function register(): void
	{
		$this->registerAddonsServiceProviders();
	}
	
	/**
	 * Register the addons services provider
	 *
	 * @return void
	 */
	private function registerAddonsServiceProviders(): void
	{
		// Load the addons Services Provider & register them
		$addonsDirs = glob(config('larapen.core.addon.path') . '*', GLOB_ONLYDIR);
		if (!empty($addonsDirs)) {
			foreach ($addonsDirs as $addonDir) {
				$addon = load_addon(basename($addonDir));
				if (!empty($addon)) {
					// Fallback if class isn't loaded by composer autoloader (e.g. casing difference on Linux)
					if (!class_exists($addon->provider)) {
						$addonFiles = glob($addonDir . '/*.php');
						$targetFilename = strtolower(class_basename($addon->provider) . '.php');
						foreach ($addonFiles as $file) {
							if (strtolower(basename($file)) === $targetFilename) {
								require_once $file;
								break;
							}
						}
					}
					
					if (class_exists($addon->provider)) {
						$this->app->register($addon->provider);
					}
				}
			}
		}
	}
	
	/**
	 * Autoload the addons files dynamically
	 *
	 * @return void
	 */
	private function autoloadAddons(): void
	{
		$addonsPath = base_path('extras/addons');
		
		if (!is_dir($addonsPath)) {
			return;
		}
		
		// Recursively scan the directory for PHP files
		$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($addonsPath));
		foreach ($files as $file) {
			if ($file->isFile() && $file->getExtension() === 'php') {
				require_once $file->getPathname();
			}
		}
	}
}
