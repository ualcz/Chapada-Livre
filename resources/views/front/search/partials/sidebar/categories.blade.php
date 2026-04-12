@php
	$countPostsPerCat ??= [];
	
	// Clear Filter Button
	$clearFilterBtn = urlGen()->getCategoryFilterClearLink($cat ?? null, $city ?? null);
	
	// Links CSS Class
	$linkClass = linkClass('body-emphasis');
@endphp
@if (!empty($cat))
	@php
		$categoryParent = data_get($cat, 'parent') ?? null;
		$categoryChildren = data_get($cat, 'children') ?? [];
		$categoryParentOfParent = data_get($cat, 'parent.parent') ?? null;
		$categoryParentChildren = data_get($cat, 'parent.children') ?? [];
		
		/*dump($cat);
		dump($categoryParent);
		dump($categoryChildren);
		dump($categoryParentOfParent);
		dump($categoryParentChildren);
		dd('STOP');*/
		
		$catParentUrl = urlGen()->parentCategory($categoryParent ?? null, $city ?? null);
	@endphp
	
	{{-- SubCategory --}}
	<div id="subCatsList">
		@if (!empty($categoryChildren))
			
			<div class="container p-0 vstack gap-2">
				<h5 class="border-bottom border-success border-opacity-50 pb-2 d-flex justify-content-between align-items-center mb-0 mt-3 mt-md-0">
					<span class="fw-bold text-success text-uppercase fs-6 mb-0">
						@if (!empty($categoryParent))
							<a href="{{ urlGen()->category($categoryParent, null, $city ?? null) }}"
							   class="{{ $linkClass }}"
							>
								<i class="fa-solid fa-reply"></i> {{ data_get($cat, 'parent.name') }}
							</a>
						@else
							<a href="{{ $catParentUrl }}" class="{{ $linkClass }}">
								<i class="fa-solid fa-reply"></i> {{ trans('global.all_categories') }}
							</a>
						@endif
					</span> {!! $clearFilterBtn !!}
				</h5>
				<ul class="mb-0 list-unstyled">
					<li class="py-1">
						<div class="border-bottom pb-2 mb-3">
							<span class="fs-5">
								@if (in_array(config('settings.listings_list.show_category_icon'), [4, 5, 6, 8]))
									<i class="{{ data_get($cat, 'icon_class') ?? 'bi bi-folder-fill' }}"></i>
								@endif
								{{ data_get($cat, 'name') }}
							</span>
							@if (config('settings.listings_list.count_categories_listings'))
								&nbsp;<span class="fw-normal">({{ $countPostsPerCat[data_get($cat, 'id')]['total'] ?? 0 }})</span>
							@endif
						</div>
						<div class="list-group list-group-flush mb-0 ps-2">
							@foreach ($categoryChildren as $iSubCat)
								<a href="{{ urlGen()->category($iSubCat, null, $city ?? null) }}"
								   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center border-0"
								   title="{{ data_get($iSubCat, 'name') }}"
								>
									<span>
										@if (in_array(config('settings.listings_list.show_category_icon'), [4, 5, 6, 8]))
											<i class="{{ data_get($iSubCat, 'icon_class') ?? 'bi bi-folder-fill' }} me-2 text-secondary"></i>
										@endif
										{{ str(data_get($iSubCat, 'name'))->limit(100) }}
									</span>
									@if (config('settings.listings_list.count_categories_listings'))
										<span class="badge bg-secondary rounded-pill">{{ $countPostsPerCat[data_get($iSubCat, 'id')]['total'] ?? 0 }}</span>
									@endif
								</a>
							@endforeach
						</div>
					</li>
				</ul>
			</div>
			
		@else
			
			@if (!empty($categoryParentChildren))
				<div class="container p-0 vstack gap-2">
					<h5 class="border-bottom border-success border-opacity-50 pb-2 d-flex justify-content-between align-items-center mt-3 mt-md-0">
						<span class="fw-bold text-success text-uppercase fs-6 mb-0">
							@if (!empty($categoryParentOfParent))
								<a href="{{ urlGen()->category($categoryParentOfParent, null, $city ?? null) }}"
								   class="{{ $linkClass }}"
								>
									<i class="fa-solid fa-reply"></i> {{ data_get($cat, 'parent.parent.name') }}
								</a>
							@elseif (!empty($categoryParent))
								<a href="{{ urlGen()->category($categoryParent, null, $city ?? null) }}"
								   class="{{ $linkClass }}"
								>
									<i class="fa-solid fa-reply"></i> {{ data_get($cat, 'name') }}
								</a>
							@else
								<a href="{{ $catParentUrl }}" class="{{ $linkClass }}">
									<i class="fa-solid fa-reply"></i> {{ trans('global.all_categories') }}
								</a>
							@endif
						</span> {!! $clearFilterBtn !!}
					</h5>
					<div class="list-group list-group-flush mb-0">
						@foreach ($categoryParentChildren as $iSubCat)
								@if (data_get($iSubCat, 'id') == data_get($cat, 'id'))
									<span class="list-group-item list-group-item-success fw-bold d-flex justify-content-between align-items-center">
										<span>
											@if (in_array(config('settings.listings_list.show_category_icon'), [4, 5, 6, 8]))
												<i class="{{ data_get($iSubCat, 'icon_class') ?? 'bi bi-folder-fill' }} me-2"></i>
											@endif
											{{ str(data_get($iSubCat, 'name'))->limit(100) }}
										</span>
										@if (config('settings.listings_list.count_categories_listings'))
											<span class="badge bg-success rounded-pill">{{ $countPostsPerCat[data_get($iSubCat, 'id')]['total'] ?? 0 }}</span>
										@endif
									</span>
								@else
									<a href="{{ urlGen()->category($iSubCat, null, $city ?? null) }}"
									   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
									   title="{{ data_get($iSubCat, 'name') }}"
									>
										<span>
											@if (in_array(config('settings.listings_list.show_category_icon'), [4, 5, 6, 8]))
												<i class="{{ data_get($iSubCat, 'icon_class') ?? 'bi bi-folder-fill' }} me-2 text-secondary"></i>
											@endif
											{{ str(data_get($iSubCat, 'name'))->limit(100) }}
										</span>
										@if (config('settings.listings_list.count_categories_listings'))
											<span class="badge bg-secondary rounded-pill">{{ $countPostsPerCat[data_get($iSubCat, 'id')]['total'] ?? 0 }}</span>
										@endif
									</a>
								@endif
						@endforeach
					</div>
				</div>
			@else
				
				@include('front.search.partials.sidebar.categories.root', ['countPostsPerCat' => $countPostsPerCat])
			
			@endif
			
		@endif
	</div>
	
@else
	
	@include('front.search.partials.sidebar.categories.root', ['countPostsPerCat' => $countPostsPerCat])
	
@endif
