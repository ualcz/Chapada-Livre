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

use App\Helpers\Common\Files\Storage\StorageDisk;
use App\Helpers\Common\Files\Tools\FileStorage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/*
 * Clear Image Thumbnails Background Job
 *
 * Purpose: Runs image thumbnails clearing operations in the background to prevent blocking user requests
 * Queue: Runs on the 'maintenance' queue (priority queue for maintenance operations)
 *
 * How it works:
 * 1. User clicks "Clear Image Thumbnails" button
 * 2. Controller dispatches this job with a unique job ID
 * 3. Job status is tracked in cache (queued -> processing -> completed/failed)
 * 4. JavaScript polls the status endpoint every 10 seconds
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

class ClearImageThumbsJob implements ShouldQueue
{
	use Queueable;
	
	/**
	 * The number of times the job may be attempted.
	 * Set to 1 because thumbnails clearing should not be retried if it fails.
	 *
	 * @var int
	 */
	public int $tries = 1;
	
	/**
	 * The number of seconds the job can run before timing out.
	 * Thumbnails clearing can take longer on large sites, so we allow 10 minutes.
	 *
	 * @var int
	 */
	public int $timeout = 600;
	
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
		Cache::put("clearImageThumbsJob:{$jobId}", [
			'status'    => 'queued',
			'message'   => trans('admin.clear_image_thumbnails_job_queued'),
			'timestamp' => now()->toDateTimeString(),
		], 600);
	}
	
	/**
	 * Execute the images thumbnails clearing job.
	 *
	 * This method performs the following operations:
	 * 1. Initialize storage disk
	 * 2. Remove all thumbnails directories
	 * 3. Remove all thumbnail files matching pattern
	 * 4. Remove empty subdirectories
	 * 5. Clear cache
	 *
	 * Status is tracked in cache throughout the process so the frontend
	 * can poll and show real-time progress to the user.
	 *
	 * @return void
	 */
	public function handle(): void
	{
		// Update status to 'processing' so user knows job started
		Cache::put("clearImageThumbsJob:{$this->jobId}", [
			'status'    => 'processing',
			'message'   => trans('admin.clear_image_thumbnails_job_processing'),
			'timestamp' => now()->toDateTimeString(),
		], 600);
		
		// Get the storage disk
		$disk = StorageDisk::getDisk();
		
		// Track any errors that occur
		$errorFound = false;
		
		// Get the upload paths to process
		$uploadPaths = [
			'app' . DIRECTORY_SEPARATOR,
			'files' . DIRECTORY_SEPARATOR,    // New path
			'pictures' . DIRECTORY_SEPARATOR, // Old path
		];
		
		// ===================================================================
		// Process each upload path
		// ===================================================================
		foreach ($uploadPaths as $uploadPath) {
			if (!$disk->exists($uploadPath)) {
				continue;
			}
			
			if (!$disk->directoryExists($uploadPath)) {
				continue;
			}
			
			// ===============================================================
			// Step 1: Remove thumbnails directories
			// ===============================================================
			try {
				$directoryName = 'thumbnails';
				FileStorage::removeSubDirRecursive($disk, $uploadPath, $directoryName);
				Log::info("Removed thumbnails directory in {$uploadPath}", ['jobId' => $this->jobId]);
			} catch (Throwable $e) {
				$errorFound = true;
				Log::error("Failed to remove thumbnails directory in {$uploadPath}", [
					'jobId' => $this->jobId,
					'error' => $e->getMessage(),
				]);
				break;
			}
			
			// ===============================================================
			// Step 2: Remove thumbnail files matching pattern
			// ===============================================================
			try {
				$pattern = '~thumb-.*\\.[a-z]*~ui';
				FileStorage::removeMatchedFilesRecursive($disk, $uploadPath, $pattern);
				Log::info("Removed thumbnail files in {$uploadPath}", ['jobId' => $this->jobId]);
			} catch (Throwable $e) {
				$errorFound = true;
				Log::error("Failed to remove thumbnail files in {$uploadPath}", [
					'jobId' => $this->jobId,
					'error' => $e->getMessage(),
				]);
				break;
			}
			
			// ===============================================================
			// Step 3: Remove empty subdirectories and create .gitignore
			// ===============================================================
			// Don't create '.gitignore' file or remove empty directories in the '/storage/app/public/app/' dir
			$appUploadedFilesPath = DIRECTORY_SEPARATOR
				. 'app' . DIRECTORY_SEPARATOR
				. 'public' . DIRECTORY_SEPARATOR
				. 'app' . DIRECTORY_SEPARATOR;
			
			if (!str_contains($appUploadedFilesPath, $uploadPath)) {
				try {
					// Check if the .gitignore file exists in the root directory to prevent its removal
					if (!$disk->exists($uploadPath . '.gitignore')) {
						$content = '*' . "\n"
							. '!.gitignore' . "\n";
						$disk->put($uploadPath . '.gitignore', $content);
					}
					
					// Remove all empty subdirectories
					FileStorage::removeEmptySubDirs($disk, $uploadPath);
					Log::info("Cleaned empty directories in {$uploadPath}", ['jobId' => $this->jobId]);
				} catch (Throwable $e) {
					$errorFound = true;
					Log::error("Failed to clean empty directories in {$uploadPath}", [
						'jobId' => $this->jobId,
						'error' => $e->getMessage(),
					]);
					break;
				}
			}
		}

		// ===================================================================
		// Step 4: Clear Cache (with job status preservation)
		// ===================================================================
		if (!$errorFound) {
			try {
				// IMPORTANT: Save job status before flushing cache
				// The flush() operation will delete ALL cache including our job status
				// We need to preserve it so JavaScript polling continues to work
				$jobStatusKey = "clearImageThumbsJob:{$this->jobId}";
				$currentJobStatus = Cache::get($jobStatusKey);

				// Flush all cache
				cache()->flush();
				Log::info('Cache flushed successfully', ['jobId' => $this->jobId]);

				// Restore job status immediately after flush
				if ($currentJobStatus) {
					Cache::put($jobStatusKey, $currentJobStatus, 600);
				}
			} catch (Throwable $e) {
				$errorFound = true;
				Log::error('Cache flush error', [
					'jobId' => $this->jobId,
					'error' => $e->getMessage(),
				]);
			}
		}

		// ===================================================================
		// Step 5: Update Final Status
		// ===================================================================
		if (!$errorFound) {
			// Success! Update cache with completed status
			Cache::put("clearImageThumbsJob:{$this->jobId}", [
				'status'    => 'completed',
				'message'   => trans('admin.clear_image_thumbnails_job_completed'),
				'level'     => 'success',
				'timestamp' => now()->toDateTimeString(),
			], 600);

			Log::info('clearImageThumbsJob completed successfully', ['jobId' => $this->jobId]);
		} else {
			// Failure - operation had errors
			$message = trans('admin.clear_image_thumbnails_job_failed');
			
			Cache::put("clearImageThumbsJob:{$this->jobId}", [
				'status'    => 'failed',
				'message'   => $message,
				'level'     => 'error',
				'timestamp' => now()->toDateTimeString(),
			], 600);
			
			Log::warning('clearImageThumbsJob completed with errors', [
				'jobId' => $this->jobId,
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
		$message = trans('admin.clear_image_thumbnails_job_failed') . ': ' . $exception->getMessage();
		
		// Update cache with failed status
		Cache::put("clearImageThumbsJob:{$this->jobId}", [
			'status'    => 'failed',
			'message'   => $message,
			'level'     => 'error',
			'timestamp' => now()->toDateTimeString(),
		], 600);
		
		// Log the failure for debugging
		Log::error('clearImageThumbsJob failed', [
			'jobId'     => $this->jobId,
			'exception' => $exception->getMessage(),
			'trace'     => $exception->getTraceAsString(),
		]);
	}
}
