@php
	$sectionOptions = $categoriesOptions ?? [];
	
	$catDisplayType = $sectionOptions['cat_display_type'] ?? null;
	$maxSubCats = (int)($sectionOptions['max_sub_cats'] ?? 0);
	
	$fullHeight = $sectionOptions['full_height'] ?? '0';
	$isFullHeightEnabled = ($fullHeight == '1');
	$style = $isFullHeightEnabled ? 'height: 100vh; min-height: 100dvh;' : '';
	
	$htmlAttr = $sectionOptions['html_attributes'] ?? '';
	$htmlAttr = !empty($htmlAttr) ? " $htmlAttr" : '';
	
	$cssClasses = $sectionOptions['css_classes'] ?? '';
	$cssClasses = !empty($cssClasses) ? " {$cssClasses}" : '';
	
	$sectionData ??= [];
	$categories = (array)($sectionData['categories'] ?? []);
	$subCategories = (array)($sectionData['subCategories'] ?? []);
	$countPostsPerCat = (array)($sectionData['countPostsPerCat'] ?? []);
	$countPostsPerCat = collect($countPostsPerCat)->keyBy('id')->toArray();
@endphp

<div class="container{{ $cssClasses }} d-flex align-items-center carousel-categories-container" style="{!! $style !!}">
	<div class="card w-100 border-0 shadow-none bg-transparent"{!! $htmlAttr !!}>
		
		<div class="card-header border-bottom-0 bg-transparent px-0 d-flex justify-content-between align-items-center">
			<h4 class="mb-0 fw-bold text-dark">
				{{ trans('global.Browse by') }} <span class="text-primary">{{ trans('global.category') }}</span>
			</h4>
			<a href="{{ urlGen()->sitemap() }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
				{{ trans('global.View more') }} <i class="fa-solid fa-arrow-right ms-1"></i>
			</a>
		</div>
		<div class="card-body p-0 mt-3">
			@if ($catDisplayType == 'c_picture_list')
				
				@include('front.sections.home.categories.c-picture-list')
			
			@elseif ($catDisplayType == 'c_bigIcon_list')
				
				@include('front.sections.home.categories.c-big-icon-list')
			
			@elseif (in_array($catDisplayType, ['cc_normal_list', 'cc_normal_list_s']))
				
				@include('front.sections.home.categories.cc-normal-list')
			
			@elseif (in_array($catDisplayType, ['c_normal_list', 'c_border_list']))
				
				@include('front.sections.home.categories.c-normal-list')
			
			@else
				
				{{-- Called only when issue occurred --}}
				@include('front.sections.home.categories.c-big-icon-list')
			
			@endif
		</div>
	
	</div>
</div>

@section('after_styles')
	@parent
	<link href="{{ url('assets/plugins/swiper/7.4.1/swiper-bundle.min.css') }}" rel="stylesheet"/>
	<style>
		.category-card {
			transition: all 0.3s ease;
			border: 1px solid rgba(var(--bs-primary-rgb), 0.1);
			background: #fff;
			height: 110px;
			width: 100%;
		}
		.category-card:hover {
			transform: translateY(-5px);
			box-shadow: 0 10px 20px rgba(var(--bs-primary-rgb), 0.1);
			border-color: var(--bs-primary);
		}
		.category-icon-wrapper {
			width: 44px;
			height: 44px;
			display: flex;
			align-items: center;
			justify-content: center;
			background: rgba(var(--bs-primary-rgb), 0.1);
			border-radius: 10px;
			margin: 0 auto 8px;
			color: var(--bs-primary);
			transition: all 0.3s ease;
		}
		.category-card:hover .category-icon-wrapper {
			background: var(--bs-primary);
			color: #fff;
		}
		.category-name {
			font-size: 0.85rem;
			line-height: 1.2;
			margin-bottom: 0px !important;
			display: block;
			width: 100%;
		}
	</style>
@endsection

@section('before_scripts')
	@parent
	@if ($maxSubCats >= 0)
		<script>
			var maxSubCats = {{ $maxSubCats }};
		</script>
	@endif
@endsection
@section('after_scripts')
	@parent
	<script src="{{ url('assets/plugins/swiper/7.4.1/swiper-bundle.min.js') }}"></script>
	<script>
		onDocumentReady((event) => {
			const categorySwiper = new Swiper('.category-swiper', {
				slidesPerView: 2.5,
				spaceBetween: 10,
				loop: false,
				breakpoints: {
					480: {
						slidesPerView: 3.5,
						spaceBetween: 15
					},
					768: {
						slidesPerView: 5.2,
						spaceBetween: 20
					},
					992: {
						slidesPerView: 7.2,
						spaceBetween: 20
					},
					1200: {
						slidesPerView: 8.5,
						spaceBetween: 20
					}
				}
			});
		});
	</script>
@endsection
