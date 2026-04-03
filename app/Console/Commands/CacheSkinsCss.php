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

use App\Helpers\Common\BsThemeGenerator;
use App\Helpers\Services\StaticFileVersion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Larapen\CodeFormatter\Facades\CodeFormatter;
use Throwable;

class CacheSkinsCss extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'skins-css:cache {--clear : Clear existing cache before regenerating}';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate and cache CSS skin files';
	
	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		// Define templates with their respective cache directories
		$templates = $this->getTemplates();
		
		// Clear cache if requested
		if ($this->option('clear')) {
			$this->info('Clearing existing cache...');
			foreach ($templates as $template) {
				$cacheDir = $template['cacheDir'];
				if (File::isDirectory($cacheDir)) {
					$files = File::glob($cacheDir . '/*.css');
					foreach ($files as $file) {
						if (File::isFile($file)) {
							File::delete($file);
						}
					}
				}
			}
			$this->info('Cache cleared.');
		}
		
		// Get all available skins
		$skins = $this->getAvailableSkins();
		
		if (empty($skins)) {
			$this->warn('No skins found.');
			
			return self::FAILURE;
		}
		
		$totalSkinsCount = count($skins);
		$totalTemplatesCount = count($templates);
		$this->info("Generating cache files for {$totalSkinsCount} skin(s) across {$totalTemplatesCount} template(s)...");
		
		$successCount = 0;
		$failureCount = 0;
		$skippedCount = 0;
		
		foreach ($templates as $templateName => $template) {
			$this->newLine();
			$this->line("Processing template: {$templateName}");
			
			foreach ($skins as $skinKey => $skinData) {
				try {
					$cached = $this->generateSkinCache($skinKey, $skinData, $template);
					if ($cached) {
						$this->line("  ✓ Cached: {$templateName}/{$skinKey}.css");
						$successCount++;
					} else {
						$skippedCount++;
					}
				} catch (Throwable $e) {
					$this->error("  ✗ Failed: {$templateName}/{$skinKey}.css - " . $e->getMessage());
					$failureCount++;
				}
			}
		}
		
		$this->newLine();
		$this->info("Cache generation complete:");
		$this->line("  Success: {$successCount}");
		if ($skippedCount > 0) {
			$this->line("  Skipped: {$skippedCount} (default skin or no color)");
		}
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
	 * Get skin templates with their paths and cache directories
	 *
	 * @return array
	 */
	protected function getTemplates(): array
	{
		return [
			'front' => [
				'templatePath' => resource_path('views/front/common/css/front-primary-color.css'),
				'cacheDir'     => public_path('cache/css/skins/front'),
			],
			'auth'  => [
				'templatePath' => resource_path('views/front/common/css/auth-primary-color.css'),
				'cacheDir'     => public_path('cache/css/skins/auth'),
			],
		];
	}
	
	/**
	 * Get available skins
	 *
	 * @return array
	 */
	protected function getAvailableSkins(): array
	{
		$skins = getCachedReferrerList('skins');
		
		// Add the custom skin color from settings
		if (isset($skins['custom'])) {
			$customColor = config('settings.style.custom_skin_color');
			if (!empty($customColor)) {
				$skins['custom']['color'] = $customColor;
			}
		}
		
		return $skins;
	}
	
	/**
	 * Generate and cache CSS for a specific skin
	 *
	 * @param string $skinKey
	 * @param array $skinData
	 * @param array $template
	 * @return bool
	 * @throws \Throwable
	 */
	protected function generateSkinCache(string $skinKey, array $skinData, array $template): bool
	{
		// Skip default skin (no custom styling needed)
		if ($skinKey === 'default' || empty($skinData['color'])) {
			return false;
		}
		
		try {
			$primaryColor = $skinData['color'];
			$templatePath = $template['templatePath'];
			$cacheDir = $template['cacheDir'];
			
			// Ensure cache directory exists
			if (!File::isDirectory($cacheDir)) {
				File::makeDirectory($cacheDir, 0755, true);
			}
			
			// Generate CSS from template
			$generator = new BsThemeGenerator($primaryColor, $templatePath);
			$css = $generator->generateCss();
			
			if (empty($css)) {
				return false;
			}
			
			// Format the code
			$isMinifyEnabled = (config('settings.optimization.minify_html_activation') == 1);
			$css = $isMinifyEnabled
				? CodeFormatter::minify($css, 'css')
				: $css; // CodeFormatter::prettyPrint($css, 'css');
			
			$cachedFilePath = $cacheDir . '/' . $skinKey . '.css';
			
			return File::put($cachedFilePath, $css) !== false;
		} catch (Throwable $e) {
			throw $e;
		}
	}
}
