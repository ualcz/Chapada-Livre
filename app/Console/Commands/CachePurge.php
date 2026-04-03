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

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class CachePurge extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'expired-cache:purge';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Delete only expired cache files and clean up empty cache directories.';
	
	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle()
	{
		$cacheDriver = config('cache.default');
		$cachePath = castToStringOrNull(config('cache.stores.file.path'));
		$cachePath = !empty($cachePath) ? $cachePath : storage_path('framework/cache/data');
		
		// 1. Initial Checks
		if ($cacheDriver !== 'file') {
			$this->error("This command only supports the 'file' cache driver. Current driver: {$cacheDriver}");
			
			return Command::FAILURE;
		}
		
		if (!is_dir($cachePath)) {
			$this->info('Cache directory does not exist. Nothing to clean.');
			
			return Command::SUCCESS;
		}
		
		$this->info("Starting cache cleanup in: {$cachePath}");
		$this->newLine();
		
		// 2. Delete Expired Files
		$this->info('1/2 Deleting expired files...');
		$this->deleteExpiredFiles($cachePath);
		
		// 3. Clean up Empty Directories
		$this->info('2/2 Cleaning up empty directories...');
		$removedDirCount = $this->removeEmptyDirectories($cachePath);
		$this->info("Removed **{$removedDirCount}** empty director(ies).");
		$this->newLine();
		
		return Command::SUCCESS;
	}
	
	/**
	 * Finds and deletes all expired cache files using an iterator for memory efficiency.
	 * Ultra-fast version without progress bar.
	 *
	 * @param string $cachePath
	 * @return void
	 */
	protected function deleteExpiredFiles(string $cachePath): void
	{
		$deletedCount = 0;
		$skippedCount = 0;
		$now = time();
		
		try {
			// Use RecursiveIteratorIterator for memory-efficient file traversal
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($cachePath, RecursiveDirectoryIterator::SKIP_DOTS),
				RecursiveIteratorIterator::LEAVES_ONLY
			);
			
			foreach ($iterator as $file) {
				/** @var SplFileInfo $file */
				if (!$file->isFile()) {
					continue;
				}
				
				$filePath = $file->getPathname();
				
				// Read only the first 10 bytes
				$handle = @fopen($filePath, 'rb');
				
				if ($handle === false) {
					continue;
				}
				
				$expiration = (int)@fread($handle, 10);
				fclose($handle);
				
				// If expired (and not forever cache with expiration 0)
				if ($expiration !== 0 && $now > $expiration) {
					@unlink($filePath);
					$deletedCount++;
				} else {
					$skippedCount++;
				}
			}
			
		} catch (\Exception $e) {
			$this->error("Error during file iteration: {$e->getMessage()}");
		}
		
		// Files Clean Up Summary
		$this->info("Deleted **{$deletedCount}** expired cache file(s).");
		$this->info("Skipped **{$skippedCount}** active cache file(s).");
		$this->newLine();
	}
	
	/**
	 * Recursively removes empty directories within the cache path.
	 * Uses a bottom-up approach for efficiency.
	 *
	 * @param string $cachePath
	 * @return int
	 */
	protected function removeEmptyDirectories(string $cachePath): int
	{
		$removedCount = 0;
		$directories = [];
		
		try {
			// Collect all directories in bottom-up order (deepest first)
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($cachePath, RecursiveDirectoryIterator::SKIP_DOTS),
				RecursiveIteratorIterator::CHILD_FIRST
			);
			
			foreach ($iterator as $file) {
				/** @var SplFileInfo $file */
				if ($file->isDir()) {
					$directories[] = $file->getPathname();
				}
			}
			
			// Process directories in reverse order (deepest first)
			foreach ($directories as $directory) {
				// Check if directory is empty
				if ($this->isDirectoryEmpty($directory)) {
					@rmdir($directory);
					$removedCount++;
				}
			}
			
		} catch (\Exception $e) {
			$this->warn("Error during directory cleanup: {$e->getMessage()}");
		}
		
		return $removedCount;
	}
	
	/**
	 * Check if a directory is empty (no files or subdirectories).
	 *
	 * @param string $directory
	 * @return bool
	 */
	protected function isDirectoryEmpty(string $directory): bool
	{
		$handle = @opendir($directory);
		
		if ($handle === false) {
			return false;
		}
		
		while (($entry = readdir($handle)) !== false) {
			if ($entry !== '.' && $entry !== '..') {
				closedir($handle);
				return false;
			}
		}
		
		closedir($handle);
		return true;
	}
}
