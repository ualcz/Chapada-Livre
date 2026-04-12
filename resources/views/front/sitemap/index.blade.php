{{--
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
--}}
@extends('front.layouts.master')

@php
	$cats ??= [];
	$cities ??= [];
	
	$wrapperClass = ' my-4 px-1';
	$itemClass = ' mb-4';
	$itemBorderClass = ' border-0 shadow-sm rounded-4';
	$h5BgColor = ' bg-primary bg-opacity-10 text-primary rounded-pill px-4 py-2 border border-primary border-opacity-25';
	$subCatIcon = '<i class="bi bi-chevron-right fs-xs me-2 opacity-50"></i> ';
@endphp

@section('after_styles')
	@parent
	<style>
		.sitemap-cat-card {
			transition: all 0.3s ease;
			background: #fff;
			height: fit-content;
		}
		.sitemap-cat-card:hover {
			transform: translateY(-5px);
			box-shadow: 0 12px 24px rgba(0,0,0,0.08) !important;
		}
		.cat-title-link {
			color: #1a202c;
			transition: color 0.2s;
		}
		.cat-title-link:hover {
			color: var(--bs-primary);
		}
		.city-pill {
			display: inline-flex;
			align-items: center;
			padding: 8px 16px;
			background: #fff;
			border: 1px solid rgba(0,0,0,0.08);
			border-radius: 50px;
			color: #4a5568;
			font-weight: 500;
			font-size: 0.9rem;
			transition: all 0.2s;
			text-decoration: none;
			box-shadow: 0 1px 2px rgba(0,0,0,0.03);
		}
		.city-pill:hover {
			background: var(--bs-primary);
			color: #fff !important;
			border-color: var(--bs-primary);
			transform: translateY(-2px);
			box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.2);
		}
		.city-pill i {
			margin-right: 6px;
			opacity: 0.6;
		}
		.city-pill:hover i {
			opacity: 1;
		}
		.fs-xs { font-size: 0.75rem; }
		
		/* Masonry-like Layout for Sitemap */
		.sitemap-columns {
			column-count: 3;
			column-gap: 1.5rem;
		}
		@media (max-width: 991px) {
			.sitemap-columns {
				column-count: 2;
			}
		}
		@media (max-width: 767px) {
			.sitemap-columns {
				column-count: 1;
			}
		}
		.sitemap-item-wrapper {
			display: inline-block;
			width: 100%;
			break-inside: avoid;
			margin-bottom: 1.5rem;
		}
	</style>
@endsection

@section('search')
	@parent
@endsection

@section('content')
	@include('front.common.spacer')
	<div class="main-container">
		<div class="container mt-5">
			@include('helpers.titles.title-2', ['title' => trans('global.sitemap')])
			@include('front.common.spacer')
			
			<div class="row">
				<div class="col-12">
					<div class="card">
						<div class="card-header">
							<h3 class="mb-0 fs-5 fw-bold">
								{{ trans('global.list_of_categories_and_sub_categories') }}
							</h3>
						</div>
						
						<div class="card-body">
							<div class="container">
								<div class="sitemap-columns{{ $wrapperClass }}">
									@foreach ($cats as $key => $iCat)
										@php
											$randomId = '-' . generateRandomString(5);
											
											$domElId = $iCat->id . $randomId;
											$catIconClass = $iCat->icon_class ?? 'bi bi-folder';
											$catIcon = !empty($catIconClass) ? '<i class="' . $catIconClass . ' me-2"></i> ' : '';
										@endphp
										<div class="sitemap-item-wrapper">
											<div class="w-100 p-4 sitemap-cat-card{{ $itemBorderClass }}">
												<h5 class="mb-3 fs-5 fw-bold d-flex justify-content-between align-items-center{{ $h5BgColor }}">
													<span class="d-flex align-items-center">
														{!! $catIcon !!}
														<a href="{{ urlGen()->category($iCat) }}" class="cat-title-link text-decoration-none">
															{{ $iCat->name }}
														</a>
													</span>
													@if (isset($iCat->children) && $iCat->children->count() > 0)
														<a class="text-primary opacity-75"
														   data-bs-toggle="collapse"
														   data-bs-target="#parentCat{{ $domElId }}"
														   href="#parentCat{{ $domElId }}"
														   role="button"
														   aria-expanded="false"
														   aria-controls="parentCat{{ $domElId }}"
														>
															<i class="bi bi-chevron-down fw-bold"></i>
														</a>
													@endif
												</h5>
												@if (isset($iCat->children) && $iCat->children->count() > 0)
													<div class="collapse show mt-3 ms-1" id="parentCat{{ $domElId }}">
														<ul class="list-unstyled mb-0">
															@foreach ($iCat->children as $iSubCat)
																<li class="py-1">
																	<a href="{{ urlGen()->category($iSubCat) }}" class="text-body text-decoration-none hover-text-primary d-flex align-items-center fw-normal opacity-75">
																		{!! $subCatIcon !!}
																		{{ $iSubCat->name }}
																	</a>
																</li>
															@endforeach
														</ul>
													</div>
												@endif
											</div>
										</div>
									@endforeach
								</div>
							</div>
						</div>
					</div>
				</div>
				
				@if (isset($cities))
					@include('front.common.spacer')
					<div class="col-12">
						<div class="card">
							<div class="card-header">
								<h3 class="mb-0 fs-5 fw-bold">
									<i class="bi bi-geo-alt"></i> {{ trans('global.list_of_cities_in') }} {{ config('country.name') }}
								</h3>
							</div>
							
							<div class="card-body p-4">
								<div class="container-fluid">
									<div class="row g-3">
										@foreach ($cities as $key => $city)
											<div class="col-auto">
												<a href="{{ urlGen()->city($city) }}"
												   class="city-pill"
												   title="{{ trans('global.Free Listings') . ' ' . $city->name }}"
												>
													<i class="bi bi-geo-alt"></i> {{ $city->name }}
												</a>
											</div>
										@endforeach
									</div>
								</div>
							</div>
						</div>
					</div>
				@endif

			</div>
			
			@include('front.layouts.partials.social.horizontal')
		</div>
	</div>
@endsection

@section('before_scripts')
	@parent
	<script>
		var maxSubCats = 5;
	</script>
@endsection
