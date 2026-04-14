@php
	use App\Enums\BootstrapColor;
	use Illuminate\Support\Number;
	use Illuminate\Support\Carbon;

	$posts ??= [];
	$totalPosts ??= 0;

	$city ??= null;
	$cat ??= null;

	$defaultCols = 4;
	$lgCols = (int) config('settings.listings_list.grid_view_cols', $defaultCols);
	$lgCols = Number::clamp($lgCols, min: 2, max: 4);
	$mdCols = ($lgCols >= 3) ? 3 : $lgCols;
	$smCols = ($lgCols >= 2) ? 2 : $lgCols;

	$isHomePage = (request()->path() == '/' || request()->routeIs('home') || !request()->segment(1));
	$carouselClass = $isHomePage ? 'grid-view-carousel' : '';
@endphp
@if (!empty($posts) && $totalPosts > 0)
	<div class="row row-cols-lg-{{ $lgCols }} row-cols-md-{{ $mdCols }} row-cols-{{ $smCols }} py-1 grid-view {{ $carouselClass }}">
		@foreach($posts as $key => $post)
			<div class="col item-list d-flex align-items-center px-2 my-3">
				<div class="card h-100 w-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative hover-shadow-lg transition-all" style="transition: all 0.3s ease;">
					@php
						$picturePath = data_get($post, 'picture.file_path');
						$pictureAttr = [
							'class' => 'lazyload img-fluid w-100 h-100 object-fit-cover',
							'style' => 'height: 220px;',
						];

						$postUrl = urlGen()->post($post);
						$parentCatUrl = null;
						if (!empty(data_get($post, 'category.parent'))) {
							$parentCatUrl = urlGen()->category(data_get($post, 'category.parent'), null, $city);
						}
						$catUrl = urlGen()->category(data_get($post, 'category'), null, $city);
						$locationUrl = urlGen()->city(data_get($post, 'city'), null, $cat);
					@endphp

					{{-- Image Area --}}
					<div class="position-relative overflow-hidden">
						@if (data_get($post, 'featured') == 1)
							@if (!empty(data_get($post, 'payment.package')))
								@if (data_get($post, 'payment.package.ribbon') != '')
									@php
										$ribbonColor = data_get($post, 'payment.package.ribbon');
										$ribbonColorClass = BootstrapColor::Badge->getColorClass($ribbonColor);
										$packageShortName = data_get($post, 'payment.package.short_name');
									@endphp
									<span class="badge rounded-pill {{ $ribbonColorClass }} position-absolute top-0 start-0 mt-2 ms-2 z-1">
										{{ $packageShortName }}
									</span>
								@endif
							@endif
						@endif

						<div class="position-absolute top-0 end-0 mt-2 me-2 bg-dark bg-opacity-50 text-white rounded-pill px-2 py-1 fs-xs z-1">
							<i class="fa-solid fa-camera"></i> {{ data_get($post, 'count_pictures') }}
						</div>

						<a href="{{ $postUrl }}" class="d-block card-img-container">
							@php
								$src = data_get($post, 'picture.url.medium');
								$webpSrc = data_get($post, 'picture.url.webp.medium');
								$alt = str(data_get($post, 'title'))->slug();
								echo generateImageHtml($src, $alt, $webpSrc, $pictureAttr);
							@endphp
						</a>
					</div>

					{{-- Content Area --}}
					<div class="card-body p-3 d-flex flex-column justify-content-between">
						<div>
							{{-- Title --}}
							<h5 class="card-title mb-1">
								<a href="{{ $postUrl }}" class="text-dark text-decoration-none hover-text-primary fs-6 fw-semibold lh-sm d-block">
									{{ str(data_get($post, 'title'))->limit(55) }}
								</a>
							</h5>

							{{-- Price & Action --}}
							<div class="d-flex justify-content-between align-items-center mb-1">
								<span class="fs-4 fw-bold text-primary lh-1">
									@php
										$rawPrice = (float)data_get($post, 'price');
										echo ($rawPrice > 0) ? 'R$ ' . number_format($rawPrice, 0, ',', '.') : data_get($post, 'price_formatted');
									@endphp
								</span>
								<div class="flex-shrink-0 ms-2">
									@php
										$postId = data_get($post, 'id');
										$savedByLoggedUser = (bool) data_get($post, 'p_saved_by_logged_user');
									@endphp
									@if ($savedByLoggedUser)
										<a class="btn btn-success btn-xs fw-normal make-favorite" id="{{ $postId }}">
											<i class="bi bi-heart-fill"></i> <span class="d-none d-md-inline">{{ trans('global.Saved') }}</span>
										</a>
									@else
										<a class="btn btn-outline-secondary btn-xs fw-normal make-favorite" id="{{ $postId }}">
											<i class="bi bi-heart"></i> <span class="d-none d-md-inline">{{ trans('global.Save') }}</span>
										</a>
									@endif
								</div>
							</div>
						</div>

						{{-- Footer Info: Location & Time --}}
						<div class="mt-2 pt-2 border-top text-muted d-flex justify-content-between align-items-center" style="font-size: 0.65rem;">
							<span class="" title="{{ data_get($post, 'city.name') }}">
								<i class="bi bi-geo-alt me-1 text-primary"></i>
								{{ data_get($post, 'city.name') }}
							</span>
							<span class="flex-shrink-0">
								<i class="bi bi-clock me-1"></i>
								{{ Carbon::parse(data_get($post, 'created_at'))->diffForHumans() }}
							</span>
						</div>
					</div>
				</div>
			</div>
		@endforeach
	</div>
@else
	<div class="py-5 text-center w-100">
		{{ trans('global.no_result_refine_your_search') }}
	</div>
@endif

@section('after_scripts')
	@parent
	<script>
		{{-- Favorites Translation --}}
		var lang = {
			labelSavePostSave: "{!! trans('global.Save listing') !!}",
			labelSavePostRemove: "{!! trans('global.Remove favorite') !!}",
			loginToSavePost: "{!! trans('global.Please log in to save the Listings') !!}",
			loginToSaveSearch: "{!! trans('global.Please log in to save your search') !!}"
		};
	</script>
@endsection

@section('after_styles')
	@parent
	<style>
		@media (max-width: 575.98px) {
			.grid-view-carousel {
				display: flex !important;
				flex-wrap: nowrap !important;
				overflow-x: auto !important;
				scroll-snap-type: x mandatory;
				-webkit-overflow-scrolling: touch;
				padding-bottom: 1.5rem;
				padding-left: 0.5rem;
				padding-right: 0.5rem;
				gap: 0 !important;
				scrollbar-width: none; /* Firefox */
			}
			.grid-view-carousel::-webkit-scrollbar {
				display: none; /* Chrome, Safari, Opera */
			}
			.grid-view-carousel .item-list {
				flex: 0 0 75% !important; /* Mostra 1 inteiro + 0.8 do próximo aproximadamente */
				max-width: 75% !important;
				scroll-snap-align: start;
				margin-top: 0.5rem !important;
				margin-bottom: 0.5rem !important;
			}
			.grid-view-carousel .item-list .card {
				height: 100% !important;
			}
		}
	</style>
@endsection