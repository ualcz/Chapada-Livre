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

namespace App\Http\Controllers\Web\Admin\Action\Heavy;

use App\Http\Controllers\Web\Admin\Controller;
use App\Jobs\ClearCacheJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Throwable;

class ClearCache extends Controller
{
	/**
	 * Clear Cache (Background Job)
	 *
	 * Dispatches a background job to clear application cache, view cache, and logs.
	 * This prevents blocking the user's request while heavy operations run.
	 *
	 * Flow:
	 * 1. Generate unique job ID (UUID)
	 * 2. Store job ID in session for JavaScript to detect
	 * 3. Dispatch job to the 'maintenance' queue
	 * 4. Redirect the user immediately with the "task queued" message
	 * 5. JavaScript automatically polls status and shows notification when done
	 *
	 * Prerequisites:
	 * - Queue worker must be running: php artisan queue:listen --queue=maintenance,default
	 * - JavaScript monitor: public/assets/admin/js/monitors/clear-cache.js
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function index(): RedirectResponse
	{
		// For LaraClassifier
		if (session()->has('curr')) {
			session()->forget('curr');
		}
		
		try {
			// Generate a unique job ID (UUID) for tracking this specific job
			$jobId = Str::uuid()->toString();
			
			// Store the job ID in the user's session so JavaScript can detect it
			// The job ID will be rendered in a meta-tag on the next page load
			session(['clearCacheJobId' => $jobId]);
			
			// Dispatch the cache clearing job to run in the background
			// The job will run on the 'maintenance' queue with priority
			ClearCacheJob::dispatch($jobId);
			
			// Show immediate feedback to the user that a job is queued
			$message = trans('admin.cache_clear_queued_message');
			notification($message, 'info');
		} catch (Throwable $e) {
			// If job dispatch fails, show error to user
			notification($e->getMessage(), 'error');
		}
		
		// Redirect back immediately (non-blocking response)
		return redirect()->back();
	}
	
	/**
	 * Check Cache Clear Job Status (API Endpoint)
	 *
	 * Polling endpoint called by JavaScript every 2 seconds to check job progress.
	 * Returns the current status of a cache clearing job.
	 *
	 * Called by: public/assets/admin/js/monitors/cache-clear.js
	 * Route: GET /admin/actions/heavy/clear-cache/status?jobId={uuid}
	 *
	 * Response statuses:
	 * - 'queued': Job is waiting in queue
	 * - 'processing': Job is currently running
	 * - 'completed': Job finished successfully
	 * - 'failed': Job encountered an error
	 * - 'not_found': Job ID not found or expired (404)
	 *
	 * Cache TTL: 10 minutes (jobs older than this return 'not_found')
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function status(Request $request): JsonResponse
	{
		// Get job ID from query parameter
		$jobId = $request->input('jobId');
		
		// Validate job ID is provided
		if (!$jobId) {
			return response()->json([
				'status'  => 'error',
				'message' => 'Job ID is required',
			], 400);
		}
		
		// Retrieve job status from the cache.
		// Cache key format: "cacheClearJob:{uuid}"
		$jobStatus = Cache::get("cacheClearJob:{$jobId}");
		
		// Job isn't found or expired (cache TTL exceeded)
		if (empty($jobStatus)) {
			session()->forget('clearCacheJobId');
			
			return response()->json([
				'status'  => 'not_found',
				'message' => 'Job not found or expired',
			], 404);
		}
		
		// Clean up: If a job is finished, remove session tracking
		// This prevents the JavaScript from polling on subsequent page loads
		if (in_array($jobStatus['status'], ['completed', 'failed'])) {
			session()->forget('clearCacheJobId');
		}
		
		// Return job status as JSON
		// Example: {"status": "completed", "message": "Cache cleared", "level": "success"}
		return response()->json($jobStatus);
	}
}
