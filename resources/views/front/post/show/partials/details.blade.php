@php
	$authUser = auth()->check() ? auth()->user() : null;
	$authUserId = !empty($authUser) ? $authUser->getAuthIdentifier() : 0;

	$post ??= [];
@endphp
<div class="items-details">
	<div class="row">
		<div class="col-12">
			{{-- Tab navs --}}
			<ul class="nav nav-pills custom-nav-pills gap-2 px-1 mb-3" id="itemsDetailsTabs" role="tablist">
				<li class="nav-item" role="presentation">
					<button class="nav-link active rounded-pill px-4" id="item-details-tab" data-bs-toggle="tab"
						data-bs-target="#item-details" type="button" role="tab" aria-controls="item-details"
						aria-selected="true">
						<span class="fw-bold">{{ trans('global.listing_details') }}</span>
					</button>
				</li>
				@if (config('addons.reviews.installed'))
					@php
						$reviewLabel = config('addons.reviews.name');
					@endphp
					<li class="nav-item" role="presentation">
						<button class="nav-link rounded-pill px-4" id="item-{{ $reviewLabel }}-tab" data-bs-toggle="tab"
							data-bs-target="#item-{{ $reviewLabel }}" type="button" role="tab"
							aria-controls="item-{{ $reviewLabel }}" aria-selected="false">
							<span class="fw-bold">
								{{ trans('reviews::messages.Reviews') }} ({{ data_get($post, 'rating_count', 0) }})
							</span>
						</button>
					</li>
				@endif
			</ul>

			{{-- Tab panes --}}
			<div class="tab-content border-0 rounded-4 bg-body p-0 mb-4" id="itemsDetailsTabsContent">
				<div class="tab-pane show active" id="item-details" role="tabpanel" aria-labelledby="item-details-tab"
					tabindex="0">
					<div class="row pb-3">
						<div class="items-details-info col-12 text-wrap from-wysiwyg">

							<div class="px-4 py-3 border rounded-4 bg-white mb-4 shadow-sm border-light-subtle">
								<div class="row align-items-center">
									{{-- Location --}}
									<div class="col-md-7 mb-3 mb-md-0">
										<div class="d-flex align-items-center">
											<div class="flex-shrink-0 bg-primary-subtle rounded-circle p-2 me-3 text-primary d-flex align-items-center justify-content-center"
												style="width: 40px; height: 40px;">
												<i class="bi bi-geo-alt fs-5"></i>
											</div>
											<div>
												<small class="text-muted d-block text-uppercase fw-bold mb-0"
													style="font-size: 0.65rem; letter-spacing: 0.5px;">{{ trans('global.location') }}</small>
												<a href="{!! urlGen()->city(data_get($post, 'city')) !!}"
													class="fw-bold text-dark text-decoration-none fs-5 hover-primary">
													{{ data_get($post, 'city.name') }}
												</a>
											</div>
										</div>
									</div>

									{{-- Price / Salary --}}
									<div class="col-md-5 text-md-end">
										<div class="d-inline-block text-md-end">
											<small class="text-muted d-block text-uppercase fw-bold mb-0"
												style="font-size: 0.65rem; letter-spacing: 0.5px;">{{ data_get($post, 'price_label') }}</small>
											<div class="d-flex align-items-center justify-content-md-end">
												<span class="fw-bolder fs-2 text-primary">
													{!! data_get($post, 'price_formatted') !!}
												</span>
												@if (data_get($post, 'negotiable') == 1)
													<span class="badge rounded-pill text-bg-info ms-2 px-3 py-2"
														style="font-size: 0.65rem;">
														{{ trans('global.negotiable') }}</span>
												@endif
											</div>
										</div>
									</div>
								</div>
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
				</div>

				@if (config('addons.reviews.installed'))
					@if (view()->exists('reviews::comments'))
						@include('reviews::comments')
					@endif
				@endif
			</div>
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