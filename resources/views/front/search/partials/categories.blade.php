@php
	$hideOnXsOrLower = 'd-none d-sm-block';
	$linkClass = linkClass();
@endphp
@if (!empty($cat) || !empty($cats))
<div class="container mb-3 {{ $hideOnXsOrLower }}">
	@if (!empty($cat))
		@if (!empty(data_get($cat, 'children')))
			<div class="row row-cols-lg-4 row-cols-md-3 row-cols-2 p-2 g-2" id="categoryBadge">
				@foreach (data_get($cat, 'children') as $iSubCat)
					<div class="col">
						<a href="{{ urlGen()->category($iSubCat, null, $city ?? null) }}" class="btn btn-outline-success fw-medium w-100 text-start shadow-sm border-0 d-flex align-items-center hover-shadow bg-white">
							@if (in_array(config('settings.listings_list.show_category_icon'), [3, 5, 7, 8]))
								<i class="{{ data_get($iSubCat, 'icon_class') ?? 'bi bi-folder-fill' }} me-2 text-success"></i>
							@endif
							{{ data_get($iSubCat, 'name') }}
						</a>
					</div>
				@endforeach
			</div>
		@else
			@if (!empty(data_get($cat, 'parent.children')))
				<div class="row row-cols-lg-4 row-cols-md-3 row-cols-2 p-2 g-2" id="categoryBadge">
					@foreach (data_get($cat, 'parent.children') as $iSubCat)
						<div class="col">
							@if (data_get($iSubCat, 'id') == data_get($cat, 'id'))
								<span class="btn btn-success fw-bold w-100 text-start shadow-sm border-0 d-flex align-items-center">
									@if (in_array(config('settings.listings_list.show_category_icon'), [3, 5, 7, 8]))
										<i class="{{ data_get($iSubCat, 'icon_class') ?? 'bi bi-folder-fill' }} me-2"></i>
									@endif
									{{ data_get($iSubCat, 'name') }}
								</span>
							@else
								<a href="{{ urlGen()->category($iSubCat, null, $city ?? null) }}" class="btn btn-outline-success fw-medium w-100 text-start shadow-sm border-0 d-flex align-items-center hover-shadow bg-white">
									@if (in_array(config('settings.listings_list.show_category_icon'), [3, 5, 7, 8]))
										<i class="{{ data_get($iSubCat, 'icon_class') ?? 'bi bi-folder-fill' }} me-2 text-success"></i>
									@endif
									{{ data_get($iSubCat, 'name') }}
								</a>
							@endif
						</div>
					@endforeach
				</div>
			@else
				
				@include('front.search.partials.categories-root')
				
			@endif
		@endif
	@else
		
		@include('front.search.partials.categories-root')
		
	@endif
</div>
@endif
