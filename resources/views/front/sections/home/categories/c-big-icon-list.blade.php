@php
	$categories ??= [];
	$isCountPostsEnabled = (config('settings.listings_list.count_categories_listings') == '1');
	$isShowingCategoryIconEnabled = in_array(config('settings.listings_list.show_category_icon'), [2, 6, 7, 8]);
@endphp

@if (!empty($categories))
	<div class="swiper category-swiper px-1 py-3">
		<div class="swiper-wrapper">
			@foreach($categories as $cat)
				@php
					$catId = data_get($cat, 'id', 0);
					$catIconClass = $isShowingCategoryIconEnabled ? data_get($cat, 'icon_class', 'bi bi-folder-fill') : '';
					$catIcon = !empty($catIconClass) ? '<i class="' . $catIconClass . '" style="font-size: 1.8rem;"></i>' : '';
					$catName = data_get($cat, 'name', '--');
					
					$catCountPosts = $isCountPostsEnabled
						? ($countPostsPerCat[$catId]['total'] ?? 0)
						: null;
				@endphp
				<div class="swiper-slide h-auto">
					<a href="{{ urlGen()->category($cat) }}" class="text-decoration-none h-100 d-block">
						<div class="category-card p-3 rounded-4 text-center h-100 d-flex flex-column align-items-center justify-content-center">
							<div class="category-icon-wrapper">
								{!! $catIcon !!}
							</div>
							<h6 class="category-name mb-1 fw-bold text-dark" title="{{ $catName }}">
								{{ $catName }}
							</h6>
							@if($catCountPosts !== null)
								<span class="small text-muted">{{ $catCountPosts }} {{ trans_choice('global.count_listings', $catCountPosts) }}</span>
							@endif
						</div>
					</a>
				</div>
			@endforeach
		</div>
	</div>
@endif
