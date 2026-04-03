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

use App\Helpers\Services\StaticFileVersion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Throwable;

class CacheFileinputLocales extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'fileinput:cache-locales {--clear : Clear existing cache before regenerating}';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate and cache bootstrap-fileinput locale files';
	
	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$cacheDir = public_path('cache/plugins/bootstrap-fileinput/js/locales');
		
		// Clear cache if requested
		if ($this->option('clear')) {
			$this->info('Clearing existing cache...');
			if (is_dir($cacheDir)) {
				$files = glob($cacheDir . '/*.js');
				foreach ($files as $file) {
					if (is_file($file)) {
						unlink($file);
					}
				}
				$this->info('Cache cleared.');
			}
		}
		
		// Ensure cache directory exists
		if (!File::isDirectory($cacheDir)) {
			File::makeDirectory($cacheDir, 0755, true);
		}
		
		// Get all available languages from the lang directory
		$languages = $this->getAvailableLanguages();
		
		if (empty($languages)) {
			$this->warn('No languages found in lang directory.');
			
			return self::FAILURE;
		}
		
		$this->info('Generating cache files for ' . count($languages) . ' locale(s)...');
		
		$successCount = 0;
		$failureCount = 0;
		
		foreach ($languages as $langCode) {
			try {
				$cached = $this->generateLocaleCache($langCode);
				if ($cached) {
					$this->line("✓ Cached: {$langCode}.js");
					$successCount++;
				} else {
					$this->warn("⚠ Skipped: {$langCode}.js (no translations found)");
				}
			} catch (Throwable $e) {
				$this->error("✗ Failed: {$langCode}.js - " . $e->getMessage());
				$failureCount++;
			}
		}
		
		$this->newLine();
		$this->info("Cache generation complete:");
		$this->line("  Success: {$successCount}");
		if ($failureCount > 0) {
			$this->line("  Failed: {$failureCount}");
		}
		
		// Update static file version for cache busting
		if ($successCount > 0) {
			StaticFileVersion::update();
		}
		
		return self::SUCCESS;
	}
	
	/**
	 * Get available languages from the lang directory
	 *
	 * @return array
	 */
	protected function getAvailableLanguages(): array
	{
		$langPath = base_path('lang');
		if (!is_dir($langPath)) {
			return [];
		}
		
		$languages = [];
		$directories = File::directories($langPath);
		
		foreach ($directories as $dir) {
			$langCode = basename($dir);
			// Check if fileinput.php translation file exists
			if (file_exists($dir . '/fileinput.php')) {
				$languages[] = $langCode;
			}
		}
		
		return $languages;
	}
	
	/**
	 * Generate and cache locale file for a specific language
	 *
	 * @param string $code
	 * @return bool
	 */
	protected function generateLocaleCache(string $code): bool
	{
		$fileInputArray = trans('fileinput', [], $code);
		
		if (!is_array($fileInputArray) || empty($fileInputArray)) {
			return false;
		}
		
		// Format the code
		$isMinifyEnabled = (config('settings.optimization.minify_html_activation') == 1);
		$fileInputJson = $isMinifyEnabled
			? json_encode($fileInputArray, JSON_UNESCAPED_UNICODE)
			: json_encode($fileInputArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
		
		if (empty($fileInputJson)) {
			return false;
		}
		
		$out = "(function (factory) {" . "\n";
		$out .= "   'use strict';" . "\n";
		$out .= "if (typeof define === 'function' && define.amd) {" . "\n";
		$out .= "   define(['jquery'], factory);" . "\n";
		$out .= "} else if (typeof module === 'object' && typeof module.exports === 'object') {" . "\n";
		$out .= "   factory(require('jquery'));" . "\n";
		$out .= "} else {" . "\n";
		$out .= "   factory(window.jQuery);" . "\n";
		$out .= "}" . "\n";
		$out .= '}(function ($) {' . "\n";
		$out .= '"use strict";' . "\n\n";
		$out .= "$.fn.fileinputLocales['$code'] = ";
		$out .= $fileInputJson . ';' . "\n";
		$out .= '}));' . "\n";
		
		$cachedFilePath = public_path('cache/plugins/bootstrap-fileinput/js/locales/' . $code . '.js');
		
		return file_put_contents($cachedFilePath, $out) !== false;
	}
}
