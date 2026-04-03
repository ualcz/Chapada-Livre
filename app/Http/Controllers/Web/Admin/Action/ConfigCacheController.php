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

namespace App\Http\Controllers\Web\Admin\Action;

use App\Http\Controllers\Web\Admin\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class ConfigCacheController extends Controller
{
	/**
	 * Regenerate the config cache
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function regenerate(): RedirectResponse
	{
		$errorFound = false;
		
		// Regenerate config cache (clear + cache)
		try {
			Artisan::call('config:clear');
			Artisan::call('config:cache');
		} catch (Throwable $e) {
			notification($e->getMessage(), 'error');
			$errorFound = true;
		}
		
		// Check if an error occurred
		if (!$errorFound) {
			$message = trans('admin.config_cache_regenerated_successfully');
			notification($message, 'success');
		}
		
		return redirect()->back();
	}
	
	/**
	 * Generate the config cache (when it doesn't exist)
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function generate(): RedirectResponse
	{
		$errorFound = false;
		
		// Generate config cache
		try {
			Artisan::call('config:cache');
		} catch (Throwable $e) {
			notification($e->getMessage(), 'error');
			$errorFound = true;
		}
		
		// Check if an error occurred
		if (!$errorFound) {
			$message = trans('admin.config_cache_generated_successfully');
			notification($message, 'success');
		}
		
		return redirect()->back();
	}
	
	/**
	 * Clear the config cache (without regenerating)
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function clear(): RedirectResponse
	{
		$errorFound = false;
		
		// Clear config cache only
		try {
			Artisan::call('config:clear');
		} catch (Throwable $e) {
			notification($e->getMessage(), 'error');
			$errorFound = true;
		}
		
		// Check if an error occurred
		if (!$errorFound) {
			$message = trans('admin.config_cache_cleared_successfully');
			notification($message, 'success');
		}
		
		return redirect()->back();
	}
}
