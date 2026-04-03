<?php

namespace App\Exceptions\Handler\Addon;

use Illuminate\Support\Facades\File;

trait OutToDateAddon
{
	/**
	 * Move addon files to the backup folder if a specific error found
	 * e.g.: When the error message contains "must be compatible"
	 *
	 * @param $message
	 * @return string|void
	 */
	public function tryToArchiveAddon($message)
	{
		if (empty($message)) {
			return;
		}
		
		// Get the broken addon name
		$matches = [];
		preg_match('|extras\\\addons\\\([^\\\]+)\\\|ui', $message, $matches);
		$brokenAddonName = $matches[1] ?? null;
		
		if (empty($brokenAddonName)) {
			return;
		}
		
		$addonsBasePath = config('larapen.core.addon.path');
		$destinationDirectory = __DIR__ . '/../../../../storage/framework/cache/addons.backup/';
		
		$sourceDirectory = $addonsBasePath . $brokenAddonName;
		
		$issueFixed = false;
		$isDirectoryLinkedToSystemFiles = (
			$brokenAddonName == 'paypal'
			|| str_ends_with($sourceDirectory, 'addons' . DIRECTORY_SEPARATOR)
			|| str_ends_with($sourceDirectory, 'addons')
		);
		if (!$isDirectoryLinkedToSystemFiles) {
			try {
				$issueFixed = $this->archiveTheAddon($sourceDirectory, $destinationDirectory, $brokenAddonName);
			} catch (\Throwable $e) {
			}
			
			// Remove the broken addon event its archiving failed
			if (!$issueFixed) {
				$issueFixed = File::deleteDirectory($sourceDirectory);
			}
		}
		
		if ($issueFixed) {
			// Customize and Redirect to the previous URL
			$previousUrl = url()->previous();
			$baseUrl = url('/');
			
			// Check if redirection is allowed
			// That avoids infinite redirections and redirections to external URLs
			$isRedirectionAllowed = (
				request()->input('archivedAddon') != $brokenAddonName
				&& str_starts_with($previousUrl, $baseUrl)
			);
			
			if ($isRedirectionAllowed) {
				// Add the addon name to query parameters
				$previousUrl = urlBuilder($previousUrl)
					->setParameters(['archivedAddon' => $brokenAddonName])
					->toString();
				
				// Redirect
				redirectUrl($previousUrl, 301, config('larapen.core.noCacheHeaders'));
			} else {
				$errorMessage = 'The "<code>%s</code>" addon was broken probably due to version compatibility with the core app.
				The script tried to back up the addon\'s files in the <code>/storage/framework/cache/backup</code>...
				By refreshing this page, the error message should be disappeared, and you can try to re-install the newer version of the addon.
				If it is not the case, please reread the documentation on the installation of this addon to fix the issue manually.';
				
				return sprintf($errorMessage, $brokenAddonName);
			}
		}
	}
	
	// PRIVATE
	
	/**
	 * Back up all the out-to-date addons files
	 *
	 * @param string $sourceDir
	 * @param string $destinationDir
	 * @param string $zipFileName
	 * @return bool
	 */
	private function archiveTheAddon(string $sourceDir, string $destinationDir, string $zipFileName): bool
	{
		// Check if the source directory exists
		if (!File::isDirectory($sourceDir)) {
			return false;
		}
		
		$zipFile = $destinationDir . $zipFileName . '.zip';
		
		// Remove any existing file
		if (File::exists($zipFile)) {
			if (File::isDirectory($zipFile)) {
				File::deleteDirectory($zipFile, true);
			} else {
				File::delete($zipFile);
			}
		}
		
		// Zip the directory and its contents, then remove it
		$issueFixed = false;
		if (zipDirectory($sourceDir, $zipFile)) {
			$issueFixed = File::deleteDirectory($sourceDir);
		}
		
		return $issueFixed;
	}
}
