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

namespace App\Http\Controllers\Web\Admin;

// Increase the server resources
$iniConfigFile = __DIR__ . '/../../../Helpers/Common/Functions/ini.php';
if (file_exists($iniConfigFile)) {
	include_once $iniConfigFile;
}

use App\Helpers\Common\DBUtils\DBEncoding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Throwable;

class ActionController extends Controller
{
	/**
	 * Put & Back to Maintenance Mode
	 *
	 * @param $mode ('down' or 'up')
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function maintenance($mode, Request $request): RedirectResponse
	{
		$messageFilePath = storage_path('framework/down-message');
		
		// Create or delete a maintenance message
		if ($mode == 'down') {
			$rules = ['message' => ['nullable', 'string', 'max:500']];
			$validated = $request->validate($rules);
			$message = $validated['message'] ?? null;
			
			// Save the maintenance mode message
			$data = ['message' => $message];
			File::put($messageFilePath, json_encode($data));
		} else {
			if (File::exists($messageFilePath)) {
				File::delete($messageFilePath);
			}
		}
		
		$errorFound = false;
		
		// Go to maintenance with DOWN status
		try {
			Artisan::call($mode);
		} catch (Throwable $e) {
			notification($e->getMessage(), 'error');
			$errorFound = true;
		}
		
		// Check if an error occurred
		if (!$errorFound) {
			$message = ($mode == 'down')
				? trans('admin.The website has been putted in maintenance mode')
				: trans('admin.The website has left the maintenance mode');
			notification($message, 'success');
		}
		
		return redirect()->back();
	}
	
	/**
	 * Clear all cached asset files (CSS, JS)
	 * Files will be regenerated automatically on the next page load
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function clearCachedAssets(): RedirectResponse
	{
		$errorFound = false;
		$clearedFiles = 0;
		
		try {
			// Clear CSS cache files
			$cssFiles = [
				public_path('cache/css/front-style.css'),
				public_path('cache/css/front-homepage.css'),
			];
			
			foreach ($cssFiles as $file) {
				if (File::exists($file)) {
					File::delete($file);
					$clearedFiles++;
				}
			}
			
			// Clear skin cache files
			$skinDirs = [
				public_path('cache/css/skins/front'),
				public_path('cache/css/skins/auth'),
			];
			
			foreach ($skinDirs as $dir) {
				if (File::isDirectory($dir)) {
					$files = File::glob($dir . '/*.css');
					foreach ($files as $file) {
						if (File::exists($file)) {
							File::delete($file);
							$clearedFiles++;
						}
					}
				}
			}
			
			// Clear fileinput locales cache
			$fileinputDir = public_path('cache/plugins/bootstrap-fileinput/js/locales');
			if (File::isDirectory($fileinputDir)) {
				$files = File::glob($fileinputDir . '/*.js');
				foreach ($files as $file) {
					if (File::exists($file)) {
						File::delete($file);
						$clearedFiles++;
					}
				}
			}
		} catch (Throwable $e) {
			notification($e->getMessage(), 'error');
			$errorFound = true;
		}
		
		// Check if an error occurred
		if (!$errorFound) {
			$message = trans('admin.cached_assets_cleared_successfully', ['count' => $clearedFiles]);
			notification($message, 'success');
		}
		
		return redirect()->back();
	}
	
	/**
	 * Update the database connection charset and collation
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function updateDbConnectionCharsetAndCollation(): RedirectResponse
	{
		$errorFound = false;
		
		// Run the Cron Job command manually
		try {
			DBEncoding::tryToFixConnectionCharsetAndCollation();
		} catch (Throwable $e) {
			notification($e->getMessage(), 'error');
			$errorFound = true;
		}
		
		// Check if an error occurred
		if (!$errorFound) {
			$message = trans('admin.database_charset_collation_updated_successfully');
			notification($message, 'success');
		}
		
		return redirect()->back();
	}
	
	/**
	 * Test the Listings Cleaner Command
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function callAdsCleanerCommand(): RedirectResponse
	{
		$errorFound = false;
		
		// Run the Cron Job command manually
		try {
			Artisan::call('listings:purge');
		} catch (Throwable $e) {
			notification($e->getMessage(), 'error');
			$errorFound = true;
		}
		
		// Check if an error occurred
		if (!$errorFound) {
			$message = trans('admin.The Listings Clear command was successfully run');
			notification($message, 'success');
		}
		
		return redirect()->back();
	}
}
