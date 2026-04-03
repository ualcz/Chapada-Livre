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

namespace App\Helpers\Services;

use Illuminate\Support\Facades\Artisan;
use Throwable;

class CacheRegenerator
{
	/**
	 * Regenerate route cache if enabled
	 *
	 * Checks if route caching is enabled in optimization settings and regenerates the cache.
	 * Can accept settings from either an array or read from config.
	 *
	 * Note that, if the route cache is enabled, it will be necessary to be regenerated when:
	 * - the "routes.php" file is created or updated (from "larapen/routes.php")
	 * - the getCountryCodeRoutePattern() fn source is updated (not programmatically)
	 *
	 * @param array|null $settings Optional settings array. If null, reads from config('settings.optimization')
	 * @return void
	 */
	public static function regenerateRouteCacheIfEnabled(?array $settings = null): void
	{
		try {
			// Get route cache enabled status
			$routeCacheEnabled = self::isRouteCacheEnabled($settings);
			
			// Clear the existing route cache first
			Artisan::call('route:clear');
			
			// Generate a new route cache if enabled
			if ($routeCacheEnabled) {
				Artisan::call('route:cache');
			}
		} catch (Throwable $e) {
			// Silently fail - cache regeneration is not critical
			// The admin can manually regenerate if needed
			// logger()->error('Failed to regenerate route cache: ' . $e->getMessage());
		}
	}
	
	/**
	 * Regenerate config cache if enabled
	 *
	 * Checks if config caching is enabled in optimization settings and regenerates the cache.
	 * Can accept settings from either an array or read from config.
	 *
	 * Note that, since all the system dynamic settings are stored as config,
	 * if the config cache is enabled, it will be necessary to be regenerated when any setting is saved
	 *
	 * @param array|null $settings Optional settings array. If null, reads from config('settings.optimization')
	 * @return void
	 */
	public static function regenerateConfigCacheIfEnabled(?array $settings = null): void
	{
		try {
			// Get config cache enabled status
			$configCacheEnabled = self::isConfigCacheEnabled($settings);
			
			// Clear the existing config cache first
			Artisan::call('config:clear');
			
			// Generate new config cache if enabled
			if ($configCacheEnabled) {
				Artisan::call('config:cache');
			}
		} catch (Throwable $e) {
			// Silently fail - cache regeneration is not critical
			// The admin can manually regenerate if needed
			// logger()->error('Failed to regenerate config cache: ' . $e->getMessage());
		}
	}
	
	/**
	 * Regenerate both route and config cache if enabled
	 *
	 * Convenience method to regenerate both caches in one call.
	 *
	 * @param array|null $settings Optional settings array. If null, reads from config('settings.optimization')
	 * @return void
	 */
	public static function regenerateAllCaches(?array $settings = null): void
	{
		self::regenerateRouteCacheIfEnabled($settings);
		self::regenerateConfigCacheIfEnabled($settings);
	}
	
	/**
	 * Check if the route cache is enabled
	 *
	 * @param array|null $settings Optional settings array. If null, reads from config
	 * @return bool
	 */
	private static function isRouteCacheEnabled(?array $settings = null): bool
	{
		if ($settings !== null) {
			return ($settings['route_cache_enabled'] ?? '0') == '1';
		}
		
		return config('settings.optimization.route_cache_enabled') == 1;
	}
	
	/**
	 * Check if the config cache is enabled
	 *
	 * @param array|null $settings Optional settings array. If null, reads from config
	 * @return bool
	 */
	private static function isConfigCacheEnabled(?array $settings = null): bool
	{
		if ($settings !== null) {
			return ($settings['config_cache_enabled'] ?? '0') == '1';
		}
		
		return config('settings.optimization.config_cache_enabled') == 1;
	}
}
