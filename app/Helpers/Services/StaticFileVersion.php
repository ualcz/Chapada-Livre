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

namespace App\Helpers\Services;

use App\Helpers\Common\DotenvEditor;
use Throwable;

/*
 * Service for managing static file versioning
 * Used for cache busting when static assets are updated
 */

class StaticFileVersion
{
	/**
	 * Update the STATIC_FILE_VERSION in .env file
	 * This forces browser cache reload for static files via mixStaticFile() function
	 */
	public static function update(): void
	{
		try {
			// Generate unique version string (timestamp)
			$version = time();
			
			// Update or add STATIC_FILE_VERSION in .env
			DotenvEditor::setKey('STATIC_FILE_VERSION', $version);
			DotenvEditor::save();
			
		} catch (Throwable $e) {
			// Silent fail - version update is not critical
		}
	}
}
