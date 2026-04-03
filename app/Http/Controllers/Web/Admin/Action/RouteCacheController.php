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

class RouteCacheController extends Controller
{
	/**
	 * Regenerate the route cache
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function regenerate(): RedirectResponse
	{
		$errorFound = false;
		
		// Regenerate route cache (clear + cache)
		try {
			Artisan::call('route:clear');
			Artisan::call('route:cache');
		} catch (Throwable $e) {
			notification($e->getMessage(), 'error');
			$errorFound = true;
		}
		
		// Check if an error occurred
		if (!$errorFound) {
			$message = trans('admin.route_cache_regenerated_successfully');
			notification($message, 'success');
		}
		
		return redirect()->back();
	}
	
	/**
	 * Generate the route cache (when it doesn't exist)
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function generate(): RedirectResponse
	{
		$errorFound = false;
		
		// Generate route cache
		try {
			Artisan::call('route:cache');
		} catch (Throwable $e) {
			notification($e->getMessage(), 'error');
			$errorFound = true;
		}
		
		// Check if an error occurred
		if (!$errorFound) {
			$message = trans('admin.route_cache_generated_successfully');
			notification($message, 'success');
		}
		
		return redirect()->back();
	}
	
	/**
	 * Clear the route cache (without regenerating)
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function clear(): RedirectResponse
	{
		$errorFound = false;
		
		// Clear route cache only
		try {
			Artisan::call('route:clear');
		} catch (Throwable $e) {
			notification($e->getMessage(), 'error');
			$errorFound = true;
		}
		
		// Check if an error occurred
		if (!$errorFound) {
			$message = trans('admin.route_cache_cleared_successfully');
			notification($message, 'success');
		}
		
		return redirect()->back();
	}
}
