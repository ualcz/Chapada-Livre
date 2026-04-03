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

namespace App\Http\Middleware\EnsureCachedAssetsExist;

use App\Helpers\Services\StaticFileVersion;
use Illuminate\Support\Facades\File;

trait AddonAssets
{
	/**
	 * Copy public assets from installed addons to cache directory
	 */
	private function syncAddonAssets(): void
	{
		$installedAddons = addon_installed_list();
		
		if (empty($installedAddons)) {
			return;
		}
		
		$cacheDir = public_path('cache/addons');
		
		// Ensure the cache/addons directory exists
		if (!is_dir($cacheDir)) {
			File::makeDirectory($cacheDir, 0755, true);
		}
		
		$assetsCopied = false;
		
		foreach ($installedAddons as $addon) {
			$addonPublicPath = addon_path($addon->name, 'public');
			
			// Skip if addon has no public directory
			if (!is_dir($addonPublicPath)) {
				continue;
			}
			
			$addonCachePath = $cacheDir . DIRECTORY_SEPARATOR . $addon->name;
			
			// Copy assets if cache doesn't exist or is outdated
			if (!is_dir($addonCachePath) || $this->isAddonAssetOutdated($addonPublicPath, $addonCachePath)) {
				// Remove old cache if it exists
				if (is_dir($addonCachePath)) {
					File::deleteDirectory($addonCachePath);
				}
				
				// Copy the public directory
				File::copyDirectory($addonPublicPath, $addonCachePath);
				
				$assetsCopied = true;
			}
		}
		
		// Update static file version if any assets were copied to bust browser cache
		if ($assetsCopied) {
			StaticFileVersion::update();
		}
	}
	
	/**
	 * Remove cached assets for unavailable addons
	 */
	private function cleanupUnavailableAddonAssets(): void
	{
		$cacheDir = public_path('cache/addons');
		
		if (!is_dir($cacheDir)) {
			return;
		}
		
		$installedAddons = addon_installed_list();
		$installedAddonNames = array_keys($installedAddons);
		
		// Get all cached addon directories
		$cachedAddonDirs = File::directories($cacheDir);
		
		foreach ($cachedAddonDirs as $cachedAddonDir) {
			$addonName = basename($cachedAddonDir);
			
			// Remove if addon is not installed
			if (!in_array($addonName, $installedAddonNames)) {
				File::deleteDirectory($cachedAddonDir);
			}
		}
	}
	
	/**
	 * Check if cached add-on assets are outdated
	 */
	private function isAddonAssetOutdated(string $sourcePath, string $cachePath): bool
	{
		if (!is_dir($cachePath)) {
			return true;
		}
		
		// Compare modification times of source and cache directories
		$sourceTime = File::lastModified($sourcePath);
		$cacheTime = File::lastModified($cachePath);
		
		return $sourceTime > $cacheTime;
	}
}
