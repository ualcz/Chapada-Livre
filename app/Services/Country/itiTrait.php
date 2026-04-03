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

namespace App\Services\Country;

use App\Exceptions\Custom\CustomException;
use App\Helpers\Common\Arr;
use App\Models\Country;
use App\Models\Scopes\ActiveScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Throwable;

trait itiTrait
{
	/**
	 * Get the "intl-tel-input" i18n option data
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	public function i18n(): JsonResponse
	{
		// Get the countries from DB
		$dbCountries = $this->getItiCountriesFromDb();
		
		if ($dbCountries->isEmpty()) {
			return apiResponse()->noContent();
		}
		
		try {
			$i18n = $dbCountries
				->mapWithKeys(function ($item) {
					$code = $item['code'] ?? null;
					$name = $item['name'] ?? null;
					
					return [strtolower($code) => $name];
				})
				->merge([
					'selectedCountryAriaLabel' => trans('global.iti.selectedCountryAriaLabel'),
					'noCountrySelected'        => trans('global.iti.noCountrySelected'),
					'countryListAriaLabel'     => trans('global.iti.countryListAriaLabel'),
					'searchPlaceholder'        => trans('global.iti.searchPlaceholder'),
					'zeroSearchResults'        => trans('global.iti.zeroSearchResults'),
					'oneSearchResult'          => trans('global.iti.oneSearchResult'),
					'multipleSearchResults'    => trans('global.iti.multipleSearchResults'),
				])->toArray();
		} catch (\Throwable $e) {
			return apiResponse()->error($e->getMessage());
		}
		
		$data = [
			'success' => true,
			'result'  => $i18n,
		];
		
		return apiResponse()->json($data);
	}
	
	/**
	 * Get the "intl-tel-input" countries to display in the dropdown
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	public function onlyCountries(): JsonResponse
	{
		// Get the countries from DB
		$dbCountries = $this->getItiCountriesFromDb();
		
		if ($dbCountries->isEmpty()) {
			return apiResponse()->noContent();
		}
		
		try {
			$countries = $dbCountries->mapWithKeys(function ($item) {
				$code = strtolower($item['code'] ?? null);
				
				return [$code => $code];
			})->flatten();
		} catch (\Throwable $e) {
			return apiResponse()->error($e->getMessage());
		}
		
		$data = [
			'success' => true,
			'result'  => $countries,
		];
		
		return apiResponse()->json($data);
	}
	
	/**
	 * Get the countries from DB
	 *
	 * @return \Illuminate\Support\Collection
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	private function getItiCountriesFromDb(): Collection
	{
		$phoneOfCountries = config('settings.sms.phone_of_countries', 'local');
		$isAdminPanelRoute = (request()->filled('isAdminPanelRoute') && (int)request()->input('isAdminPanelRoute') == 1);
		$countryCode = config('country.code', 'US');
		
		$dbQueryCanBeSkipped = (!isAdminPanelRoute() && $phoneOfCountries == 'local' && !empty(config('country')));
		if ($dbQueryCanBeSkipped) {
			return collect([$countryCode => collect(config('country'))]);
		}
		
		$selectColumns = ['code', 'name'];
		
		try {
			// Cache Parameters
			$cacheParams = [
				'action'           => 'get.countries.iti',
				'isAdminPanelRoute' => $isAdminPanelRoute,
				'phoneOfCountries' => $phoneOfCountries,
				'country'          => $countryCode,
				'locale'           => app()->getLocale(),
				'orderBy'          => 'name',
				'select'           => implode(',', $selectColumns),
			];
			
			$countries = caching()->remember(Country::class, $cacheParams, function () use (
				$phoneOfCountries, $isAdminPanelRoute, $countryCode, $selectColumns
			) {
				$countries = Country::query();
				
				if ($isAdminPanelRoute) {
					$countries->withoutGlobalScopes([ActiveScope::class]);
				} else {
					// Skipped
					if ($phoneOfCountries == 'local') {
						$countries->where('code', '=', $countryCode);
					}
					if ($phoneOfCountries == 'activated') {
						$countries->isActive();
					}
					if ($phoneOfCountries == 'all') {
						$countries->withoutGlobalScopes([ActiveScope::class]);
					}
				}
				
				$countries = $countries->orderBy('name')->get($selectColumns);
				
				if ($countries->count() > 0) {
					$countries = $countries->keyBy('code');
				}
				
				return $countries;
			});
		} catch (Throwable $e) {
			$message = 'Impossible to get countries from database. Error: ' . $e->getMessage();
			throw new CustomException($message);
		}
		
		$countries = collect($countries);
		
		// Sort
		return Arr::mbSortBy($countries, 'name', app()->getLocale());
	}
}
