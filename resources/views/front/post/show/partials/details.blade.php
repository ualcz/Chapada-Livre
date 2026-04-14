@php
	$authUser = auth()->check() ? auth()->user() : null;
	$authUserId = !empty($authUser) ? $authUser->getAuthIdentifier() : 0;

	$post ??= [];
@endphp
<div class="items-details">
	<div class="row">
		<div class="col-12">
			{{-- Conteúdo Direto (Abas removidas) --}}
			<div class="row pb-3">
				<div class="items-details-info col-12 text-wrap from-wysiwyg">

			
				<div class="border rounded-4 bg-white mb-4 shadow-sm border-light-subtle overflow-hidden">
					{{-- Localização + Preço em linha --}}
					<div class="d-flex align-items-center justify-content-between px-4 py-3 flex-wrap gap-2">
						{{-- Location --}}
						<div class="d-flex align-items-center gap-2">
							<div class="bg-primary-subtle rounded-circle text-primary d-flex align-items-center justify-content-center flex-shrink-0"
								style="width: 38px; height: 38px;">
								<i class="bi bi-geo-alt fs-5"></i>
							</div>
							<div>
								<small class="text-muted d-block text-uppercase fw-bold"
									style="font-size: 0.65rem; letter-spacing: 0.5px;">{{ trans('global.location') }}</small>
								<a href="{!! urlGen()->city(data_get($post, 'city')) !!}"
									class="fw-bold text-dark text-decoration-none fs-6 hover-primary">
									{{ data_get($post, 'city.name') }}
								</a>
							</div>
						</div>

						{{-- Price --}}
						<div class="text-end">
							<small class="text-muted d-block text-uppercase fw-bold"
								style="font-size: 0.65rem; letter-spacing: 0.5px;">{{ data_get($post, 'price_label') }}</small>
							<div class="d-flex align-items-center justify-content-end gap-2">
								<span class="fw-bolder fs-3 text-primary lh-1">
									@php
										$rawPrice = (float)data_get($post, 'price');
										echo ($rawPrice > 0) ? 'R$ ' . number_format($rawPrice, 2, ',', '.') : data_get($post, 'price_formatted');
									@endphp
								</span>
								@if (data_get($post, 'negotiable') == 1)
									<span class="badge rounded-pill text-bg-info px-2 py-1" style="font-size: 0.65rem;">
										{{ trans('global.negotiable') }}
									</span>
								@endif
							</div>
						</div>
					</div>

					{{-- Botões mobile — visíveis apenas em telas < lg --}}
					@php
						$mobilePhone = data_get($post, 'phone');
						$mobilePhoneHidden = (data_get($post, 'phone_hidden') == 1);
						$isMobileOwner = (!empty($authUserId) && $authUserId == data_get($post, 'user_id'));
					@endphp
					@if (!$isMobileOwner)
						<div class="d-flex d-lg-none gap-2 px-4 pb-3">
							{{-- WhatsApp --}}
							@if (!empty($mobilePhone) && !$mobilePhoneHidden)
								@php
									$mobilePhoneDigits = keepOnlyNumericChars($mobilePhone);
									$mobileSellerName  = data_get($post, 'contact_name');
									$mobileUserName    = auth()->check() ? auth()->user()->name : 'um interessado';
									$mobilePrice       = data_get($post, 'price_formatted');
									$mobileHasPrice    = (data_get($post, 'price') > 0);
									$mobileTranKey     = $mobileHasPrice ? 'global.whatsapp_pre_filled_message' : 'global.whatsapp_pre_filled_message_no_price';
									$mobileWaMessage   = trans($mobileTranKey, [
										'sellerName' => $mobileSellerName,
										'userName'   => $mobileUserName,
										'title'      => data_get($post, 'title'),
										'price'      => $mobilePrice,
										'appName'    => config('app.name'),
										'url'        => urlGen()->post($post),
									]);
									$mobileWaUrl = 'https://wa.me/' . $mobilePhoneDigits . '?text=' . urlencode($mobileWaMessage);
								@endphp
								<a href="{{ $mobileWaUrl }}" target="_blank"
									class="btn btn-success rounded-pill py-2 fw-bold text-white border-0 flex-fill d-flex align-items-center justify-content-center"
									style="background-color: #25D366; font-size: 0.88rem;">
									<i class="fa-brands fa-whatsapp me-1"></i> WhatsApp
								</a>
							@endif

							{{-- Contatar anunciante --}}
							@if (auth()->check())
								<button type="button"
									class="btn btn-outline-primary rounded-pill py-2 fw-bold flex-fill d-flex align-items-center justify-content-center"
									data-bs-toggle="modal" data-bs-target="#contactUser"
									style="font-size: 0.88rem;">
									<i class="bi bi-envelope me-1"></i> Contatar
								</button>
							@else
								<a href="{{ url('login') }}"
									class="btn btn-outline-primary rounded-pill py-2 fw-bold flex-fill d-flex align-items-center justify-content-center"
									style="font-size: 0.88rem;">
									<i class="bi bi-envelope me-1"></i> Contatar
								</a>
							@endif
						</div>
					@endif
				</div>

							{{-- Description --}}
							<div class="row mb-4">
								<div class="col-12 detail-line-content lh-base fs-6 px-3">
									<h5 class="fw-bold mb-3 border-start border-4 border-primary ps-3">
										{{ trans('global.Description') }}</h5>
									<div class="text-muted">
										{!! data_get($post, 'description') !!}
									</div>
								</div>
							</div>

							{{-- Custom Fields --}}
							<div class="mt-5 px-3">
								@include('front.post.show.partials.details.fields-values')
							</div>

							{{-- Tags --}}
							@if (!empty(data_get($post, 'tags')))
								<div class="row mt-5 px-3">
									<div class="col-12">
										<h6 class="fw-bold mb-3 text-muted text-uppercase small"
											style="letter-spacing: 1px;">
											<i class="bi bi-tags-fill me-2 text-primary"></i>{{ trans('global.Tags') }}
										</h6>
										<div class="d-flex flex-wrap gap-2">
											@foreach(data_get($post, 'tags') as $iTag)
												<a href="{{ urlGen()->tag($iTag) }}"
													class="btn btn-sm btn-light border-0 rounded-pill px-3 py-2 text-secondary text-decoration-none shadow-sm hover-elevate">
													#{{ $iTag }}
												</a>
											@endforeach
										</div>
									</div>
								</div>
							@endif

							{{-- Actions Section Bar --}}
							@if (empty($authUserId) || $authUserId != data_get($post, 'user_id'))
								<div class="mt-5 pt-5 border-top">
									<div class="d-grid d-sm-flex justify-content-center gap-3">

										@if (isVerifiedPost($post))
											@php
												$postId = data_get($post, 'id');
												$savedByLoggedUser = (bool) data_get($post, 'p_saved_by_logged_user');
											@endphp
											@if ($savedByLoggedUser)
												<a class="btn btn-success rounded-pill px-4 py-2 make-favorite d-flex align-items-center justify-content-center" id="{{ $postId }}">
													<i class="bi bi-heart-fill me-2"></i> <span>{{ trans('global.Saved') }}</span>
												</a>
											@else
												<a class="btn btn-outline-secondary rounded-pill px-4 py-2 make-favorite d-flex align-items-center justify-content-center" id="{{ $postId }}">
													<i class="bi bi-heart me-2"></i> <span>{{ trans('global.Save') }}</span>
												</a>
											@endif

											<a href="{{ urlGen()->reportPost($post) }}"
												class="btn btn-outline-danger rounded-pill px-4 py-2 d-flex align-items-center justify-content-center">
												<i class="bi bi-flag-fill me-2"></i> {{ trans('global.Report abuse') }}
											</a>
										@endif
									</div>
								</div>
							@endif
						</div>

					</div>

				@if (config('addons.reviews.installed'))
					@if (view()->exists('reviews::comments'))
						<div class="mt-4">
							@include('reviews::comments')
						</div>
					@endif
				@endif
		</div>
	</div>
</div>

@section('after_scripts')
	@parent
	<script>
		onDocumentReady((event) => {
			/*...*/
		});
	</script>
@endsection