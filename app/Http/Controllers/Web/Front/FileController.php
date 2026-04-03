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

namespace App\Http\Controllers\Web\Front;

use App\Helpers\Common\Files\Response\FileContentResponseCreator;
use App\Helpers\Common\Files\Storage\StorageDisk;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Routing\Controllers\Middleware;
use Throwable;

class FileController extends Controller
{
	protected Filesystem $disk;
	private static ?string $diskName = null;
	
	public function __construct()
	{
		$tmpDiskName = request()->input('disk');
		if (!empty($tmpDiskName) && is_string($tmpDiskName)) {
			$allowedNames = ['private', 'public'];
			if (config('filesystems.disks.' . $tmpDiskName) && in_array($tmpDiskName, $allowedNames)) {
				self::$diskName = $tmpDiskName;
			}
		}
		
		$this->disk = StorageDisk::getDisk(self::$diskName);
	}
	
	/**
	 * Get the middleware that should be assigned to the controller.
	 */
	public static function middleware(): array
	{
		$array = [];
		
		if (self::$diskName == 'private') {
			$array[] = new Middleware('auth', only: ['show']);
		}
		
		return array_merge(parent::middleware(), $array);
	}
	
	/**
	 * Get & watch media file (image, audio & video) content
	 *
	 * @param \App\Helpers\Common\Files\Response\FileContentResponseCreator $response
	 * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\StreamedResponse|null
	 */
	public function watchMediaContent(FileContentResponseCreator $response)
	{
		$filePath = request()->input('path');
		$filePath = preg_replace('|\?.*|ui', '', $filePath);
		
		try {
			$out = $response::create($this->disk, $filePath);
		} catch (Throwable $e) {
			abort(400, $e->getMessage());
		}
		
		if (ob_get_length()) {
			ob_end_clean(); // HERE IS THE MAGIC
		}
		
		return $out;
	}
}
