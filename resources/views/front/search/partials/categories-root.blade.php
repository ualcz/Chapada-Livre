@php
	$hideOnlyOnXs = 'd-none d-sm-block';
	$linkClass = linkClass();
@endphp
@if (!empty($cats))
	<div class="row row-cols-lg-4 row-cols-md-3 row-cols-2 p-2 g-2" id="categoryBadge">
		@foreach ($cats as $iCat)
			<div class="col">
				@if (!empty($cat) && data_get($iCat, 'id') == data_get($cat, 'id'))
					<span class="btn btn-success fw-bold w-100 text-start shadow-sm border-0 d-flex align-items-center">
						@if (in_array(config('settings.listings_list.show_category_icon'), [3, 5, 7, 8]))
							<i class="{{ data_get($iCat, 'icon_class') ?? 'bi bi-folder-fill' }} me-2"></i>
						@endif
						{{ data_get($iCat, 'name') }}
					</span>
				@else
					<a href="{{ urlGen()->category($iCat, null, $city ?? null) }}"
						class="btn btn-outline-success fw-medium w-100 text-start shadow-sm border-0 d-flex align-items-center hover-shadow bg-white">
						@if (in_array(config('settings.listings_list.show_category_icon'), [3, 5, 7, 8]))
							<i class="{{ data_get($iCat, 'icon_class') ?? 'bi bi-folder-fill' }} me-2 text-success"></i>
						@endif
						{{ data_get($iCat, 'name') }}
					</a>
				@endif
			</div>
		@endforeach
	</div>
@endif