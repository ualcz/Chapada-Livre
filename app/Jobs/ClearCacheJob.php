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

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Throwable;

/*
 * Clear Cache Background Job
 *
 * Purpose: Runs cache clearing operations in the background to prevent blocking user requests
 * Queue: Runs on the 'maintenance' queue (priority queue for maintenance operations)
 *
 * How it works:
 * 1. User clicks "Clear Cache" button
 * 2. Controller dispatches this job with a unique job ID
 * 3. Job status is tracked in cache (queued -> processing -> completed/failed)
 * 4. JavaScript polls the status endpoint every 2 seconds
 * 5. When completed, user sees PNotify notification automatically
 *
 * Running the Queue Worker:
 * - Development: php artisan queue:listen --queue=maintenance,default --tries=1
 * - Production: Configure Supervisor to run queue:work with maintenance queue
 *
 * IMPORTANT: Queue worker MUST listen to 'maintenance' queue or jobs won't process!
 *
 * Documentation:
 * https://laravel.com/docs/12.x/queues#running-the-queue-worker
 *
 * Supervisor:
 * https://medium.com/@danielarcher/how-to-use-supervisord-for-your-laravel-application-66015f104703
 * https://gist.github.com/deepak-cotocus/6b9865784dee18966e15c74ec6e487c4
 * https://dev.to/edgaras/supervisor-guide-for-laravel-developers-configuration-and-use-cases-20i4
 */

class ClearCacheJob implements ShouldQueue
{
	use Queueable;
	
	/**
	 * The number of times the job may be attempted.
	 * Set to 1 because cache clearing should not be retried if it fails.
	 *
	 * @var int
	 */
	public int $tries = 1;
	
	/**
	 * The number of seconds the job can run before timing out.
	 * Cache clearing typically takes 3-5 seconds, but we allow 5 minutes for safety.
	 *
	 * @var int
	 */
	public int $timeout = 300;
	
	/**
	 * Unique identifier for tracking this job's status.
	 * Used to store and retrieve job progress in cache.
	 *
	 * @var string
	 */
	protected string $jobId;
	
	/**
	 * Create a new job instance.
	 *
	 * @param string $jobId Unique identifier (UUID) for tracking job status
	 */
	public function __construct(string $jobId)
	{
		$this->jobId = $jobId;
		
		// Assign this job to the 'maintenance' queue (priority queue)
		$this->onQueue('maintenance');
		
		// Set initial status in cache so polling can immediately detect the job
		// Cache TTL: 600 seconds (10 minutes) - enough time for job to complete and user to see result
		Cache::put("cacheClearJob:{$jobId}", [
			'status'    => 'queued',
			'message'   => trans('admin.cache_clear_job_queued'),
			'timestamp' => now()->toDateTimeString(),
		], 600);
	}
	
	/**
	 * Execute the cache clearing job.
	 *
	 * This method performs the following operations:
	 * 1. Flush application cache
	 * 2. Clear compiled views
	 * 3. Delete log files
	 *
	 * Status is tracked in cache throughout the process so the frontend
	 * can poll and show real-time progress to the user.
	 *
	 * @return void
	 */
	public function handle(): void
	{
		// Update status to 'processing' so user knows job started
		Cache::put("cacheClearJob:{$this->jobId}", [
			'status'    => 'processing',
			'message'   => trans('admin.cache_clear_job_processing'),
			'timestamp' => now()->toDateTimeString(),
		], 600);
		
		// Track any errors that occur
		$errors = [];
		
		// ===================================================================
		// Step 1: Flush Application Cache
		// ===================================================================
		try {
			// IMPORTANT: Save job status before flushing cache
			// The flush() operation will delete ALL cache including our job status
			// We need to preserve it so JavaScript polling continues to work
			$jobStatusKey = "cacheClearJob:{$this->jobId}";
			$currentJobStatus = Cache::get($jobStatusKey);
			
			// Flush all cache
			cache()->flush();
			Log::info('Cache flushed successfully', ['jobId' => $this->jobId]);
			
			// Restore job status immediately after flush
			if (!empty($currentJobStatus)) {
				Cache::put($jobStatusKey, $currentJobStatus, 600);
			}
		} catch (Throwable $e) {
			$errors[] = 'Cache flush error: ' . $e->getMessage();
			Log::error('Cache flush error', [
				'jobId' => $this->jobId,
				'error' => $e->getMessage(),
			]);
		}
		
		// Brief pause to prevent overwhelming the system
		sleep(1);
		
		// ===================================================================
		// Step 2: Clear Compiled Views
		// ===================================================================
		try {
			Artisan::call('view:clear');
			Log::info('View cache cleared successfully', ['jobId' => $this->jobId]);
		} catch (Throwable $e) {
			$errors[] = 'View cache clear error: ' . $e->getMessage();
			Log::error('View cache clear error', [
				'jobId' => $this->jobId,
				'error' => $e->getMessage(),
			]);
		}
		
		// Brief pause
		sleep(1);
		
		// ===================================================================
		// Step 6: Delete Log Files
		// ===================================================================
		try {
			// Delete all .log files in storage/logs
			File::delete(File::glob(storage_path('logs') . DIRECTORY_SEPARATOR . '*.log'));
			
			// Delete debugbar JSON files if debugbar directory exists
			$debugBarPath = storage_path('debugbar');
			if (File::exists($debugBarPath)) {
				File::delete(File::glob($debugBarPath . DIRECTORY_SEPARATOR . '*.json'));
			}
			
			Log::info('Logs cleared successfully', ['jobId' => $this->jobId]);
		} catch (Throwable $e) {
			$errors[] = 'Logs clear error: ' . $e->getMessage();
			Log::error('Logs clear error', [
				'jobId' => $this->jobId,
				'error' => $e->getMessage(),
			]);
		}
		
		// ===================================================================
		// Step 7: Update Final Status
		// ===================================================================
		if (empty($errors)) {
			// Success! Update cache with completed status
			Cache::put("cacheClearJob:{$this->jobId}", [
				'status'    => 'completed',
				'message'   => trans('admin.cache_clear_job_completed'),
				'level'     => 'success',
				'timestamp' => now()->toDateTimeString(),
			], 600);
			
			Log::info('ClearCacheJob completed successfully', ['jobId' => $this->jobId]);
		} else {
			// Partial failure - some operations had errors
			$message = 'Cache clearing completed with errors: ' . implode('; ', $errors);
			
			Cache::put("cacheClearJob:{$this->jobId}", [
				'status'    => 'failed',
				'message'   => $message,
				'level'     => 'error',
				'errors'    => $errors,
				'timestamp' => now()->toDateTimeString(),
			], 600);
			
			Log::warning('ClearCacheJob completed with errors', [
				'jobId'  => $this->jobId,
				'errors' => $errors,
			]);
		}
	}
	
	/**
	 * Handle a job failure.
	 *
	 * Called when the job throws an exception or times out.
	 * Updates cache status so the frontend can show an error notification.
	 *
	 * @param \Throwable $exception
	 * @return void
	 */
	public function failed(Throwable $exception): void
	{
		$message = 'Cache clearing failed: ' . $exception->getMessage();
		
		// Update cache with failed status
		Cache::put("cacheClearJob:{$this->jobId}", [
			'status'    => 'failed',
			'message'   => $message,
			'level'     => 'error',
			'timestamp' => now()->toDateTimeString(),
		], 600);
		
		// Log the failure for debugging
		Log::error('ClearCacheJob failed', [
			'jobId'     => $this->jobId,
			'exception' => $exception->getMessage(),
			'trace'     => $exception->getTraceAsString(),
		]);
	}
}
