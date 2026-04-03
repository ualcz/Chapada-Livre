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
use Illuminate\Support\Facades\View;
use Larapen\CodeFormatter\Facades\CodeFormatter;
use Throwable;

class CacheStyleCss extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'style-css:cache {--clear : Clear existing cache before regenerating}';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate and cache static CSS from style template';
	
	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$cacheDir = public_path('cache/css');
		$cacheFile = $cacheDir . '/front-style.css';
		
		// Clear cache if requested
		if ($this->option('clear')) {
			$this->info('Clearing existing cache...');
			if (File::exists($cacheFile)) {
				File::delete($cacheFile);
				$this->info('Cache cleared.');
			}
		}
		
		// Ensure cache directory exists
		if (!File::isDirectory($cacheDir)) {
			File::makeDirectory($cacheDir, 0755, true);
		}
		
		try {
			// Render the style blade template
			$css = View::make('front.common.css.front-style')->render();
			
			// Extract CSS content from <style> tags
			$css = $this->extractCssContent($css);
			
			if (empty($css)) {
				$this->error('Failed to generate CSS: Empty content');
				
				return self::FAILURE;
			}
			
			// Format the code
			$isMinifyEnabled = (config('settings.optimization.minify_html_activation') == 1);
			$css = $isMinifyEnabled
				? CodeFormatter::minify($css, 'css')
				: CodeFormatter::prettyPrint($css, 'css');
			
			// Save to cache file
			File::put($cacheFile, $css);
			
			// Get file size for display
			$fileSize = File::size($cacheFile);
			
			$this->info('✓ Style CSS cached successfully');
			$this->line("  File: {$cacheFile}");
			$this->line("  Size: " . number_format($fileSize) . " bytes");
			
			// Update static file version for cache busting
			StaticFileVersion::update();
			
			return self::SUCCESS;
		} catch (Throwable $e) {
			$this->error('✗ Failed to cache style CSS: ' . $e->getMessage());
			
			return self::FAILURE;
		}
	}
	
	/**
	 * Extract CSS content from rendered view (remove <style> tags and clean up)
	 *
	 * @param string $rendered
	 * @return string
	 */
	protected function extractCssContent(string $rendered): string
	{
		// Remove <style> tags
		$css = preg_replace('/<\/?style[^>]*>/i', '', $rendered);
		
		// Trim whitespace
		return trim($css);
	}
}
