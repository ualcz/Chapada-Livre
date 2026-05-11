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

namespace App\Http\Controllers\Api;

use App\Services\SectionService;
use Illuminate\Http\JsonResponse;

/**
 * @group Home
 */
class SectionController extends BaseController
{
	protected SectionService $sectionService;
	
	/**
	 * @param \App\Services\SectionService $sectionService
	 */
	public function __construct(SectionService $sectionService)
	{
		parent::__construct();
		
		$this->sectionService = $sectionService;
	}
	
	/**
	 * List sections
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index(): JsonResponse
	{
		return $this->sectionService->getSections();
	}
	
	/**
	 * Get section
	 *
	 * Get section by its name.
	 *
	 * @urlParam name string required The key/method of the section. Example: getCategories
	 *
	 * @param $name
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($name): JsonResponse
	{
		$params = [
			'unactivatedIncluded' => (request()->integer('unactivatedIncluded') == 1),
			'fetchData'           => (request()->integer('fetchData') == 1),
		];
		
		return $this->sectionService->getSectionByKey($name, $params);
	}

	/**
	 * Get homepage banner data
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function banner(): JsonResponse
	{
		$countryCode = config('country.code', config('settings.app.default_country'));
		$appLocale = config('app.locale', 'pt');
		
		$section = \App\Models\Section::query()->where('name', 'search_form')->first();
		$country = \App\Models\Country::query()->where('code', $countryCode)->first();
		
		$options = $section->field_values ?? [];
		
		// Titles
		$title = $options['title_' . $appLocale] ?? $options['title_en'] ?? null;
		$subTitle = $options['sub_title_' . $appLocale] ?? $options['sub_title_en'] ?? null;
		
		// Background Image
		$bgImage = $options['background_image_url'] ?? null;
		if (empty($bgImage)) {
			$bgImage = $country->background_image_url ?? null;
		}
		
		$data = [
			'title'                   => $title ? replaceGlobalPatterns($title) : null,
			'sub_title'               => $subTitle ? replaceGlobalPatterns($subTitle) : null,
			'background_image_url'    => $bgImage,
			'background_image_darken' => $options['background_image_darken'] ?? 0.4,
			'full_height'             => $options['full_height'] ?? '0',
		];
		
		return apiResponse()->json([
			'success' => true,
			'result'  => $data,
		]);
	}
}
