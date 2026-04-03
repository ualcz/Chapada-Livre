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
use App\Models\Section;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Larapen\CodeFormatter\Facades\CodeFormatter;

class CacheHomepageCss extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'homepage-css:cache {--clear : Clear existing cache before regenerating}';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate and cache homepage CSS file';
	
	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$cacheDir = public_path('cache/css');
		$cacheFile = $cacheDir . '/front-homepage.css';
		
		// Clear cache if requested
		if ($this->option('clear')) {
			if (File::exists($cacheFile)) {
				File::delete($cacheFile);
				$this->info('Homepage CSS cache cleared.');
			}
		}
		
		// Ensure cache directory exists
		if (!File::isDirectory($cacheDir)) {
			File::makeDirectory($cacheDir, 0755, true);
		}
		
		// Generate and cache the CSS
		$this->info('Generating homepage CSS cache...');
		
		try {
			// Get homepage section data
			$sections = Section::getFormattedSections();
			$searchFormOptions = data_get($sections, 'search_form.field_values') ?? [];
			$locationsOptions = data_get($sections, 'locations.field_values') ?? [];
			
			// Render the CSS
			$css = View::make('front.common.css.front-homepage', [
				'searchFormOptions' => $searchFormOptions,
				'locationsOptions'  => $locationsOptions,
			])->render();
			
			// Extract CSS content (remove <style> tags if present)
			$css = $this->extractCssContent($css);
			
			// Format the code
			$isMinifyEnabled = (config('settings.optimization.minify_html_activation') == 1);
			$css = $isMinifyEnabled
				? CodeFormatter::minify($css, 'css')
				: CodeFormatter::prettyPrint($css, 'css');
			
			// Save to cache file
			File::put($cacheFile, $css);
			
			$this->info('Homepage CSS cache generated successfully!');
			$this->line('Cache file: ' . $cacheFile);
			
			// Update static file version for cache busting
			StaticFileVersion::update();
			
			return Command::SUCCESS;
		} catch (\Throwable $e) {
			$this->error('Failed to generate homepage CSS cache: ' . $e->getMessage());
			
			return Command::FAILURE;
		}
	}
	
	/**
	 * Extract CSS content from rendered view
	 *
	 * @param string $content
	 * @return string
	 */
	private function extractCssContent(string $content): string
	{
		// Remove <style> tags if present
		$content = preg_replace('/<style[^>]*>/', '', $content);
		$content = str_replace('</style>', '', $content);
		
		// Trim whitespace
		return trim($content);
	}
}
