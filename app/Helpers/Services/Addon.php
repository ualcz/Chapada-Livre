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

use Illuminate\Support\Facades\File;
use Throwable;

/*
 * Base class for all addons
 * Provides common functionality for addon installation and uninstallation
 */

class Addon
{
	/**
	 * Copy addon's public folder contents to "public/cache/addons/{addonName}/"
	 *
	 * @param string $addonName The name of the addon
	 * @param string $publicPath The path to the addon's public folder
	 */
	protected static function copyPublicAssets(string $addonName, string $publicPath): void
	{
		$cacheAddonsPath = public_path('cache/addons/' . $addonName);
		
		// Check if addon has a public folder
		if (!File::isDirectory($publicPath)) {
			return;
		}
		
		try {
			// Ensure the "public/cache/addons/" directory exists
			if (!File::isDirectory(public_path('cache/addons'))) {
				File::makeDirectory(public_path('cache/addons'), 0755, true);
			}
			
			// Remove existing addon cache if exists
			if (File::isDirectory($cacheAddonsPath)) {
				File::deleteDirectory($cacheAddonsPath);
			}
			
			// Copy the public folder contents
			File::copyDirectory($publicPath, $cacheAddonsPath);
			
			// Update static file version to bust browser cache
			StaticFileVersion::update();
		} catch (Throwable $e) {
			// Silent fail - assets will use original paths
		}
	}
	
	/**
	 * Remove addon's public assets from "public/cache/addons/{addonName}/"
	 *
	 * @param string $addonName The name of the addon
	 */
	protected static function removePublicAssets(string $addonName): void
	{
		$cacheAddonsPath = public_path('cache/addons/' . $addonName);
		
		try {
			if (File::isDirectory($cacheAddonsPath)) {
				File::deleteDirectory($cacheAddonsPath);
			}
		} catch (Throwable $e) {
			// Silent fail
		}
	}
}
