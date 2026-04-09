<style>
	.location-card .card {
		border: none;
		box-shadow: none;
		background: transparent !important;
	}
	.section-title-underline {
		position: relative;
		padding-bottom: 10px;
		margin-bottom: 25px;
		display: inline-block;
	}
	.section-title-underline::after {
		content: "";
		position: absolute;
		left: 0;
		bottom: 0;
		width: 40px;
		height: 4px;
		background: var(--bs-primary);
		border-radius: 2px;
	}
	.city-chips-container {
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
	}
	.city-chip {
		display: inline-flex;
		align-items: center;
		background: #fff;
		border: 1px solid rgba(0,0,0,0.06);
		padding: 8px 14px;
		border-radius: 20px;
		color: #2d3748;
		transition: all 0.25s ease;
		text-decoration: none;
		font-size: 0.88rem;
		box-shadow: 0 1px 3px rgba(0,0,0,0.02);
	}
	.city-chip:hover {
		background: #fff;
		border-color: var(--bs-primary);
		color: var(--bs-primary);
		box-shadow: 0 4px 10px rgba(0,0,0,0.05);
		transform: translateY(-2px);
	}
	.city-chip i {
		margin-right: 5px;
		font-size: 0.95rem;
		color: #a0aec0;
	}
	@media (max-width: 575px) {
		.city-chips-container { gap: 6px; }
		.city-chip {
			font-size: 0.8rem;
			padding: 6px 12px;
			border-radius: 16px;
		}
		.city-chip i { font-size: 0.85rem; }
	}
</style>

@php
	$sectionOptions = $locationsOptions ?? [];
	
	// Get Admin Map's values
	$locCanBeShown = (data_get($sectionOptions, 'show_cities') == '1');
	$locColumns = (int)(data_get($sectionOptions, 'items_cols') ?? 3);
	$locCountListingsPerCity = (config('settings.listings_list.count_cities_listings'));
	$mapCanBeShown = (
		file_exists(config('larapen.core.maps.path') . config('country.icode') . '.svg')
		&& data_get($sectionOptions, 'enable_map') == '1'
	);
	
	$showListingBtn = (data_get($sectionOptions, 'show_listing_btn') == '1');
	
	$fullHeight = $sectionOptions['full_height'] ?? '0';
	$isFullHeightEnabled = ($fullHeight == '1');
	$style = $isFullHeightEnabled ? 'height: 100vh; min-height: 100dvh;' : '';
	
	$htmlAttr = $sectionOptions['html_attributes'] ?? '';
	$htmlAttr = !empty($htmlAttr) ? " {$htmlAttr}" : '';
	
	$cssClasses = $sectionOptions['css_classes'] ?? '';
	$cssClasses = !empty($cssClasses) ? " {$cssClasses}" : '';
	
	$sectionData ??= [];
	$cities = (array)($sectionData['cities'] ?? []);
@endphp
@if ($locCanBeShown || $mapCanBeShown)
	<div class="container{{ $cssClasses }} pt-2 pb-4 location-card" style="{!! $style !!}">
		<div class="card border-0 bg-transparent"{!! $htmlAttr !!}>
			<div class="card-body p-0">
				
				<div class="row">
					@if (!$mapCanBeShown)
						<div class="d-flex justify-content-between align-items-end mb-4">
							<div>
								<h2 class="mb-0 fw-black text-dark" style="letter-spacing: -0.5px; font-size: clamp(1.2rem, 3vw, 1.7rem);">
									<i class="bi bi-geo-alt text-primary me-2"></i>{{ trans('global.Choose a city') }}
								</h2>
								<div class="bg-primary rounded-pill mt-2" style="width: 48px; height: 4px;"></div>
							</div>
						</div>
					@endif

					@php
						$leftClassCol = '';
						$rightClassCol = '';
						$rowCol = 'row-cols-lg-4 row-cols-md-3 row-cols-sm-2 row-cols-1'; // Cities Columns
						
						if ($locCanBeShown && $mapCanBeShown) {
							// Display the Cities & the Map
							$leftClassCol = 'col-lg-8 col-md-12';
							$rightClassCol = 'col-lg-3 col-md-12 mt-3 mt-xl-0 mt-lg-0';
							$rowCol = 'row-cols-lg-3 row-cols-md-2 row-cols-sm-1 row-cols-1';
							
							if ($locColumns == 2) {
								$leftClassCol = 'col-md-6 col-sm-12';
								$rightClassCol = 'col-md-5 col-sm-12';
								$rowCol = 'row-cols-lg-2 row-cols-md-2 row-cols-sm-1 row-cols-1';
							}
							if ($locColumns == 1) {
								$leftClassCol = 'col-md-3 col-sm-12';
								$rightClassCol = 'col-md-8 col-sm-12';
								$rowCol = 'row-cols-lg-1 row-cols-md-1 row-cols-sm-1 row-cols-1';
							}
						} else {
							if ($locCanBeShown && !$mapCanBeShown) {
								// Display the Cities & Hide the Map
								$leftClassCol = 'col-xl-12';
							}
							if (!$locCanBeShown && $mapCanBeShown) {
								// Display the Map & Hide the Cities
								$rightClassCol = 'col-xl-12';
							}
						}
					@endphp
					@if ($locCanBeShown)
						<div class="{{ $leftClassCol }} m-0 p-0">
							@if (!empty($cities))
								@if ($mapCanBeShown)
									<div class="d-flex justify-content-between align-items-end mb-4 pt-2">
										<div>
											<h2 class="mb-0 fw-black text-dark" style="letter-spacing: -0.5px; font-size: clamp(1.2rem, 3vw, 1.7rem);">
												<i class="bi bi-geo-alt text-primary me-2"></i>{{ trans('global.Choose a city or region') }}
											</h2>
											<div class="bg-primary rounded-pill mt-2" style="width: 48px; height: 4px;"></div>
										</div>
									</div>
								@endif
								<div class="row">
									<div class="col-xl-12">
										<div id="cityList" class="city-chips-container align-items-center">
											@foreach ($cities as $key => $city)
												@if (data_get($city, 'id') == 0)
													<a href="#browseLocations"
													   class="btn btn-outline-primary rounded-pill fw-semibold d-flex align-items-center gap-2"
													   style="padding: 6px 14px; font-size: 0.88rem;"
													   data-bs-toggle="modal"
													   data-admin-code="0"
													   data-city-id="0"
													>
														{!! data_get($city, 'name') !!} <i class="fa-solid fa-arrow-right"></i>
													</a>
												@else
													<a href="{{ urlGen()->city($city) }}" class="city-chip">
														<i class="bi bi-geo-alt"></i>
														{{ data_get($city, 'name') }}
														@if ($locCountListingsPerCity)
															<span class="ms-1 opacity-50">({{ data_get($city, 'posts_count') ?? 0 }})</span>
														@endif
													</a>
												@endif
											@endforeach
										</div>
									</div>
									
									@if ($showListingBtn)
										@php
											[$createListingLinkUrl, $createListingLinkAttr] = getCreateListingLinkInfo();
										@endphp
										<div class="col-xl-12 text-center pt-5">
											<a class="btn btn-primary rounded-pill px-4 py-2 fw-bold"
											   href="{{ $createListingLinkUrl }}"{!! $createListingLinkAttr !!}
											>
												<i class="fa-regular fa-pen-to-square me-2"></i> {{ trans('global.create_listing') }}
											</a>
										</div>
									@endif
			
								</div>
							@endif
						</div>
					@endif
					
					@include('front.sections.home.locations.svgmap')
				</div>
				
			</div>
		</div>
	</div>
@endif

@section('modal_location')
	@parent
	@if ($locCanBeShown || $mapCanBeShown)
		@include('front.layouts.partials.modal.location')
	@endif
@endsection

@section('after_scripts')
	@parent
@endsection
