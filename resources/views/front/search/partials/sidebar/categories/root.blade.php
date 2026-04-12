{{-- Category --}}
@if (!empty($cats))
	@php
		// dd($cats);
		$countPostsPerCat ??= [];
		$linkClass = linkClass('body-emphasis');
	@endphp
	<div id="catsList">
		<div class="container p-0 vstack gap-2">
			<h5 class="border-bottom border-success border-opacity-50 pb-2 d-flex justify-content-between align-items-center">
				<span class="fw-bold text-success text-uppercase fs-6 mb-0">{{ trans('global.all_categories') }}</span> {!! $clearFilterBtn ?? '' !!}
			</h5>
			<div class="list-group list-group-flush mb-0">
				@foreach ($cats as $iCat)
					@if (isset($cat) && data_get($iCat, 'id') == data_get($cat, 'id'))
						<span class="list-group-item list-group-item-success fw-bold d-flex justify-content-between align-items-center">
							<span>
								@if (in_array(config('settings.listings_list.show_category_icon'), [4, 5, 6, 8]))
									<i class="{{ data_get($iCat, 'icon_class') ?? 'bi bi-folder-fill' }} me-2"></i>
								@endif
								{{ data_get($iCat, 'name') }}
							</span>
							@if (config('settings.listings_list.count_categories_listings'))
								<span class="badge bg-success rounded-pill">{{ $countPostsPerCat[data_get($iCat, 'id')]['total'] ?? 0 }}</span>
							@endif
						</span>
					@else
						<a href="{{ urlGen()->category($iCat, null, $city ?? null) }}"
						   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
						   title="{{ data_get($iCat, 'name') }}"
						>
							<span>
								@if (in_array(config('settings.listings_list.show_category_icon'), [4, 5, 6, 8]))
									<i class="{{ data_get($iCat, 'icon_class') ?? 'bi bi-folder-fill' }} me-2 text-secondary"></i>
								@endif
								{{ data_get($iCat, 'name') }}
							</span>
							@if (config('settings.listings_list.count_categories_listings'))
								<span class="badge bg-secondary rounded-pill">{{ $countPostsPerCat[data_get($iCat, 'id')]['total'] ?? 0 }}</span>
							@endif
						</a>
					@endif
				@endforeach
			</div>
		</div>
	</div>
@endif
