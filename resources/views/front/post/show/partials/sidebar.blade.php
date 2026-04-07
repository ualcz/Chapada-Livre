@php
	$authUser = auth()->check() ? auth()->user() : null;
	$authUserId = !empty($authUser) ? $authUser->getAuthIdentifier() : 0;

	$post ??= [];
	$user ??= [];
	$countPackages ??= 0;
	$countPaymentMethods ??= 0;

	$isPostOwner = (!empty($authUserId) && $authUserId == data_get($post, 'user_id'));

	// Google Maps
	$isMapEnabled = (config('settings.listing_page.show_listing_on_googlemap') == '1');
	$useGeocodingApi = (config('settings.other.google_maps_integration_type') == 'geocoding');
	$mapsJavascriptApiKey = config('services.google_maps_platform.maps_javascript_api_key');
	$mapsEmbedApiKey = config('services.google_maps_platform.maps_embed_api_key');
	$geocodingApiKey = config('services.google_maps_platform.geocoding_api_key');
	$useAsyncGeocoding = (config('settings.other.use_async_geocoding') == '1');

	$mapsEmbedApiKey ??= $mapsJavascriptApiKey;
	$geocodingApiKey ??= $mapsJavascriptApiKey;
	$geocodingApiKey = $useAsyncGeocoding ? $geocodingApiKey : $mapsJavascriptApiKey;

	$mapHeight = 250;
	$city = data_get($post, 'city', []);
	$geoMapAddress = getItemAddressForMap($city);

	$mapsEmbedApiUrl = getGoogleMapsEmbedApiUrl($mapsEmbedApiKey, $geoMapAddress);
	$geocodingApiUrl = getGoogleMapsApiUrl($geocodingApiKey, $useAsyncGeocoding);

	$linkClass = linkClass();
@endphp
<aside class="vstack gap-4">
	{{-- Author Info Card --}}
	<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
		@if ($isPostOwner)
			<div class="card-header bg-primary bg-opacity-10 border-0 fw-bold text-primary py-3">
				<i class="bi bi-gear-fill me-2"></i>{{ trans('global.Manage Listing') }}
			</div>
		@endif
		<div class="card-body p-4">
			{{-- Author Info (for Guests & Non-Owner Users) --}}
			@if (!$isPostOwner)
				<div class="text-center mb-4">
					<div class="d-inline-block position-relative mb-3">
						<div class="bg-light rounded-circle d-flex align-items-center justify-content-center shadow-sm"
							style="width: 80px; height: 80px;">
							<i class="bi bi-person fs-1 text-secondary"></i>
						</div>
					</div>
					<h5 class="fw-bold mb-1">
						@if (!empty($user))
							<a href="{{ urlGen()->user($user) }}" class="text-dark text-decoration-none hover-primary">
								{{ data_get($post, 'contact_name') }}
							</a>
						@else
							{{ data_get($post, 'contact_name') }}
						@endif
					</h5>
					<small class="text-muted">{{ trans('global.Posted by') }}</small>

					@if (config('addons.reviews.installed'))
						@if (view()->exists('reviews::ratings-user'))
							<div class="mt-2">
								@include('reviews::ratings-user')
							</div>
						@endif
					@endif
				</div>

				<div class="bg-light rounded-3 p-3 mb-4 small text-secondary">
					<div class="d-flex justify-content-between mb-2 pb-2 border-bottom border-white">
						<span><i class="bi bi-geo-alt me-2 text-primary"></i>{{ trans('global.location') }}</span>
						<a href="{!! urlGen()->city(data_get($post, 'city')) !!}"
							class="text-dark fw-bold text-decoration-none">
							{{ data_get($post, 'city.name') }}
						</a>
					</div>
					@if (!config('settings.listing_page.hide_date'))
						@if (!empty($user) && !empty(data_get($user, 'created_at_formatted')))
							<div class="d-flex justify-content-between">
								<span><i class="bi bi-calendar3 me-2 text-primary"></i>{{ trans('global.Joined') }}</span>
								<span class="text-dark fw-bold">{!! data_get($user, 'created_at_formatted') !!}</span>
							</div>
						@endif
					@endif
				</div>
			@endif

			{{-- Actions Buttons --}}
			<div class="d-grid gap-2">
				{{-- Actions Buttons (for Logged-in Users) --}}
				@if (!empty($authUser))
					@if ($isPostOwner)
						{{-- Actions Buttons (for Owner Author) --}}
						<a href="{{ urlGen()->editPost($post) }}" class="btn btn-primary rounded-pill py-2 fw-bold shadow-sm">
							<i class="fa-regular fa-pen-to-square me-2"></i> {{ trans('global.Update the details') }}
						</a>
						@if (isMultipleStepsFormEnabled())
							<a href="{{ url('posts/' . data_get($post, 'id') . '/photos') }}"
								class="btn btn-outline-secondary rounded-pill py-2 fw-bold">
								<i class="fa-solid fa-camera me-2"></i> {{ trans('global.Update Photos') }}
							</a>
							@if ($countPackages > 0 && $countPaymentMethods > 0)
								<a href="{{ url('posts/' . data_get($post, 'id') . '/payment') }}"
									class="btn btn-success rounded-pill py-2 fw-bold shadow-sm text-white">
									<i class="fa-regular fa-circle-check me-2"></i> {{ trans('global.Make It Premium') }}
								</a>
							@endif
						@endif
						@if (empty(data_get($post, 'archived_at')) && isVerifiedPost($post))
							<a href="{{ url(urlGen()->getAccountBasePath() . '/posts/list/' . data_get($post, 'id') . '/offline') }}"
								class="btn btn-warning rounded-pill py-2 fw-bold confirm-simple-action">
								<i class="fa-solid fa-eye-slash me-2"></i> {{ trans('global.put_it_offline') }}
							</a>
						@endif
						@if (!empty(data_get($post, 'archived_at')))
							<a href="{{ url(urlGen()->getAccountBasePath() . '/posts/archived/' . data_get($post, 'id') . '/repost') }}"
								class="btn btn-info rounded-pill py-2 fw-bold text-white confirm-simple-action">
								<i class="fa-solid fa-recycle me-2"></i> {{ trans('global.re_post_it') }}
							</a>
						@endif
					@else
						{{-- WhatsApp Button (for Non-Owner Users) --}}
						@php
							$phone = data_get($post, 'phone');
							$isPhoneHidden = (data_get($post, 'phone_hidden') == 1);
						@endphp
						@if (!empty($phone) && !$isPhoneHidden)
							@php
								$phoneDigits = keepOnlyNumericChars($phone);
								$sellerName = data_get($post, 'contact_name');
								$userName = (!empty($authUser)) ? $authUser->name : 'um interessado';
								$price = data_get($post, 'price_formatted');
								$waMessage = trans('global.whatsapp_pre_filled_message', [
									'sellerName' => $sellerName,
									'userName' => $userName,
									'title' => data_get($post, 'title'),
									'price' => $price,
									'appName' => config('app.name'),
									'url' => urlGen()->post($post)
								]);
								$waUrl = "https://wa.me/" . $phoneDigits . "?text=" . urlencode($waMessage);
							@endphp
							<a href="{{ $waUrl }}" target="_blank"
								class="btn btn-success rounded-pill py-2 py-md-2 fw-bold shadow-sm text-white border-0 hover-lift mb-2"
								style="background-color: #25D366;">
								<i class="fa-brands fa-whatsapp fs-5 me-2"></i> Chamar no WhatsApp
							</a>
						@endif

						<button type="button" class="btn btn-outline-primary rounded-pill py-2 py-md-2 fw-bold shadow-sm mb-2"
							data-bs-toggle="modal" data-bs-target="#contactUser">
							<i class="bi bi-envelope me-2"></i> {{ trans('global.contact_advertiser') }}
						</button>
					@endif

					{{-- Admin Ban User --}}
					@php
						try {
							if (doesUserHavePermission($authUser, \App\Models\Permission::getStaffPermissions())) {
								$btnUrl = urlGen()->adminUrl('blacklists/add') . '?';
								$btnQs = (!empty(data_get($post, 'email'))) ? 'email=' . data_get($post, 'email') : '';
								$btnQs = (!empty($btnQs)) ? $btnQs . '&' : $btnQs;
								$btnQs = (!empty(data_get($post, 'phone'))) ? $btnQs . 'phone=' . data_get($post, 'phone') : $btnQs;
								$btnUrl = $btnUrl . $btnQs;

								if (!isDemoDomain($btnUrl)) {
									echo '<a href="' . $btnUrl . '" class="btn btn-link text-danger text-decoration-none fw-bold small mt-2 confirm-simple-action">
										<i class="bi bi-shield-slash me-1"></i>' . trans('admin.ban_the_user') . '</a>';
								}
							}
						} catch (\Throwable $e) {
						}
					@endphp
				@else
					{{-- Guest Buttons --}}
					@php
						$phone = data_get($post, 'phone');
						$isPhoneHidden = (data_get($post, 'phone_hidden') == 1);
					@endphp
					@if (!empty($phone) && !$isPhoneHidden)
						@php
							$phoneDigits = keepOnlyNumericChars($phone);
							$sellerName = data_get($post, 'contact_name');
							$price = data_get($post, 'price_formatted');
							$waMessage = trans('global.whatsapp_pre_filled_message', [
								'sellerName' => $sellerName,
								'userName' => 'um interessado',
								'title' => data_get($post, 'title'),
								'price' => $price,
								'appName' => config('app.name'),
								'url' => urlGen()->post($post)
							]);
							$waUrl = "https://wa.me/" . $phoneDigits . "?text=" . urlencode($waMessage);
						@endphp
						<a href="{{ $waUrl }}" target="_blank"
							class="btn btn-success rounded-pill py-2 py-md-2 fw-bold shadow-sm text-white border-0 hover-lift mb-2"
							style="background-color: #25D366;">
							<i class="fa-brands fa-whatsapp fs-5 me-2"></i> Chamar no WhatsApp
						</a>
					@endif

					<a href="{{ url('login') }}"
						class="btn btn-outline-primary rounded-pill py-2 py-md-2 fw-bold shadow-sm mb-2">
						<i class="bi bi-envelope me-2"></i> {{ trans('global.contact_advertiser') }}
					</a>
				@endif
			</div>
		</div>
	</div>

	{{-- Google Maps Card --}}
	@if ($isMapEnabled)
		<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
			<div class="card-header bg-white border-0 fw-bold py-3">
				<i class="bi bi-geo-alt-fill me-2 text-primary"></i>{{ trans('global.location_map') }}
			</div>
			<div class="card-body p-0">
				<div class="posts-googlemaps rounded-bottom">
					@if ($useGeocodingApi)
						<div id="googleMaps" style="width: 100%; height: {{ $mapHeight }}px;"></div>
					@else
						<iframe id="googleMaps" width="100%" height="{{ $mapHeight }}" src="{{ $mapsEmbedApiUrl }}"
							loading="lazy" style="border:0;" allowfullscreen></iframe>
					@endif
				</div>
			</div>
		</div>
	@endif

	{{-- Social Media Sharing
	@if (isVerifiedPost($post))
	<div class="card border-0 shadow-sm rounded-4 text-center p-3">
		<small class="text-muted text-uppercase fw-bold mb-2" style="font-size: 0.7rem; letter-spacing: 1px;">{{
			trans('global.Share') }}</small>
		@include('front.layouts.partials.social.horizontal')
	</div>
	@endif
	--}}

	{{-- Safety Tips Card --}}
	@php
		$tips = [
			trans('global.Meet seller at a public place'),
			trans('global.Check the item before you buy'),
			trans('global.Pay only after collecting the item'),
		];
	@endphp
	<div class="card border-0 shadow-sm rounded-4 bg-warning bg-opacity-10 border-start border-4 border-warning">
		<div class="card-body p-4">
			<h6 class="fw-bold text-warning-emphasis mb-3">
				<i class="bi bi-shield-check me-2"></i>{{ trans('global.Safety Tips for Buyers') }}
			</h6>
			<ul class="list-unstyled mb-0 small text-warning-emphasis fw-medium">
				@foreach($tips as $tip)
					<li class="mb-2 d-flex align-items-start">
						<i class="bi bi-check-circle-fill me-2 mt-1"></i>
						<span>{{ $tip }}</span>
					</li>
				@endforeach
			</ul>
			@php
				$tipsLinkAttributes = getUrlPageByType('tips');
			@endphp
			@if (!str_contains($tipsLinkAttributes, 'href="#"') && !str_contains($tipsLinkAttributes, 'href=""'))
				<div class="mt-3 pt-2 border-top border-warning border-opacity-25 text-end">
					<a class="text-warning-emphasis text-decoration-none small fw-bold" {!! $tipsLinkAttributes !!}>
						{{ trans('global.Know more') }} <i class="bi bi-arrow-right ms-1"></i>
					</a>
				</div>
			@endif
		</div>
	</div>
</aside>
</aside>

@section('after_scripts')
	@parent
	@if ($isMapEnabled)
		@if ($useGeocodingApi)
			{{-- Google Geocoding API script --}}
			@if (!empty($geocodingApiUrl))
				<script async defer src="{{ $geocodingApiUrl }}"></script>
			@endif

			{{-- JS code to append the map --}}
			<script>
				var geocodingApiKey = '{{ $geocodingApiKey }}';
				var locationAddress = '{{ $geoMapAddress }}';
				var locationMapElId = 'googleMaps';
				var locationMapId = '{{ generateUniqueCode(16) }}';
			</script>
			@if ($useAsyncGeocoding)
				<script src="{{ url('assets/js/app/google-maps-async.js') }}"></script>
			@else
				<script src="{{ url('assets/js/app/google-maps.js') }}"></script>
			@endif
		@endif
	@endif
@endsection