@php
	// Clear Filter Button
	$clearFilterBtn = urlGen()->getCityFilterClearLink($cat ?? null, $city ?? null);
	
	/*
	 * Check if the City Model exists in the Cities eloquent collection
	 * If it doesn't exist in the collection,
	 * Then, add it into the Cities eloquent collection
	 */
	if (isset($cities, $city) && !collect($cities)->contains($city)) {
		collect($cities)->push($city)->toArray();
	}
	
	// Links CSS Class
	$linkClass = linkClass('body-emphasis');
@endphp
{{-- City --}}
<div class="container p-0 vstack gap-2">
	<h5 class="border-bottom border-success border-opacity-50 pb-2 d-flex justify-content-between align-items-center">
		<span class="fw-bold text-success text-uppercase fs-6 mb-0">{{ trans('global.locations') }}</span> {!! $clearFilterBtn !!}
	</h5>
	<div>
		<div class="list-group list-group-flush mb-0 long-list">
			@if (!empty($cities))
				@foreach ($cities as $iCity)
						@if (
							(
								isset($city)
								&& data_get($city, 'id') == data_get($iCity, 'id')
							)
							|| request()->input('l') == data_get($iCity, 'id')
							)
							<span class="list-group-item list-group-item-success fw-bold d-flex justify-content-between align-items-center">
								<span><i class="fa-solid fa-location-dot me-2"></i> {{ data_get($iCity, 'name') }}</span>
								@if (config('settings.listings_list.count_cities_listings'))
									<span class="badge bg-success rounded-pill">{{ data_get($iCity, 'posts_count') ?? 0 }}</span>
								@endif
							</span>
						@else
							<a href="{!! urlGen()->city($iCity, null, $cat ?? null) !!}"
							   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center border-0"
							   title="{{ data_get($iCity, 'name') }}"
							>
								<span><i class="fa-solid fa-location-dot me-2 text-secondary"></i> {{ data_get($iCity, 'name') }}</span>
								@if (config('settings.listings_list.count_cities_listings'))
									<span class="badge bg-secondary rounded-pill">{{ data_get($iCity, 'posts_count') ?? 0 }}</span>
								@endif
							</a>
						@endif
				@endforeach
			@endif
		</div>
	</div>
</div>
