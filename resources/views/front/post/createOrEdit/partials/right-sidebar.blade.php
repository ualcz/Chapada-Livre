@php
	$post ??= [];
@endphp
<div class="vstack gap-4">
	
	@if (request()->segment(1) == 'create' || request()->segment(2) == 'create')
		{{-- Create Form --}}
		<div class="card border-0 shadow-sm rounded-4 text-center p-4" style="background: linear-gradient(135deg, rgba(var(--bs-primary-rgb),0.07) 0%, rgba(var(--bs-primary-rgb),0.02) 100%); border: 1px solid rgba(var(--bs-primary-rgb),0.12) !important;">
			<div style="width:64px;height:64px;background:rgba(var(--bs-primary-rgb),0.12);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
				<i class="fa-regular fa-image fa-2x text-primary"></i>
			</div>
			<h5 class="mb-2 fw-bold fs-6">
				{{ trans('global.create_new_listing') }}
			</h5>
			<p class="text-muted small mb-0">
				{{ trans('global.do_you_have_something_text', ['appName' => config('app.name')]) }}
			</p>
		</div>
	@else
		{{-- Edit Form --}}
		@if (isSingleStepFormEnabled())
			{{-- Single Step Form --}}
			@if (auth()->check())
				@if (auth()->user()->getAuthIdentifier() == data_get($post, 'user_id'))
					<div class="card border-0 shadow-sm rounded-4">
						<div class="card-header fw-bold text-center border-0 rounded-top-4" style="background: rgba(var(--bs-primary-rgb),0.08); color: var(--bs-primary);">
							<i class="fa-regular fa-star me-1"></i> {{ trans('global.author_actions') }}
						</div>
						<div class="card-body text-center">
							<div class="d-grid">
								<a href="{{ urlGen()->post($post) }}" class="btn btn-outline-primary rounded-pill">
									<i class="fa-regular fa-hand-point-right"></i> {{ trans('global.Return to the listing') }}
								</a>
							</div>
						</div>
					</div>
				@endif
			@endif
			
		@else
			{{-- Multi Steps Form --}}
			@if (auth()->check())
				@if (auth()->user()->getAuthIdentifier() == data_get($post, 'user_id'))
					<div class="card border-0 shadow-sm rounded-4">
						<div class="card-header fw-bold text-center border-0 rounded-top-4" style="background: rgba(var(--bs-primary-rgb),0.08); color: var(--bs-primary);">
							<i class="fa-regular fa-star me-1"></i> {{ trans('global.author_actions') }}
						</div>
						<div class="card-body text-center">
							<div class="d-grid vstack gap-2">
								<a href="{{ urlGen()->post($post) }}" class="btn btn-outline-primary rounded-pill">
									<i class="fa-regular fa-hand-point-right"></i> {{ trans('global.Return to the listing') }}
								</a>
								<a href="{{ url('posts/' . data_get($post, 'id') . '/photos') }}" class="btn btn-outline-secondary rounded-pill">
									<i class="fa-solid fa-camera"></i> {{ trans('global.Update Photos') }}
								</a>
								@if (isset($countPackages) && isset($countPaymentMethods) && $countPackages > 0 && $countPaymentMethods > 0)
									<a href="{{ url('posts/' . data_get($post, 'id') . '/payment') }}" class="btn btn-success rounded-pill">
										<i class="fa-regular fa-circle-check"></i> {{ trans('global.Make It Premium') }}
									</a>
								@endif
							</div>
						</div>
					</div>
				@endif
			@endif
			
		@endif
	@endif
	
	<div class="card border-0 shadow-sm rounded-4">
		<div class="card-header fw-bold border-0 rounded-top-4 text-white text-uppercase text-center" style="background: linear-gradient(135deg, var(--bs-primary), color-mix(in srgb, var(--bs-primary) 70%, #000));">
			<i class="fa-solid fa-bolt me-1"></i> {{ trans('global.how_to_sell_quickly') }}
		</div>
		<div class="card-body text-start px-3 py-3">
			<ul class="list-unstyled vstack gap-2 mb-0">
				<li class="d-flex align-items-start gap-2"><i class="bi bi-check-circle-fill text-primary mt-1 flex-shrink-0"></i> <span>{{ trans('global.sell_quickly_advice_1') }}</span></li>
				<li class="d-flex align-items-start gap-2"><i class="bi bi-check-circle-fill text-primary mt-1 flex-shrink-0"></i> <span>{{ trans('global.sell_quickly_advice_2') }}</span></li>
				<li class="d-flex align-items-start gap-2"><i class="bi bi-check-circle-fill text-primary mt-1 flex-shrink-0"></i> <span>{{ trans('global.sell_quickly_advice_3') }}</span></li>
				<li class="d-flex align-items-start gap-2"><i class="bi bi-check-circle-fill text-primary mt-1 flex-shrink-0"></i> <span>{{ trans('global.sell_quickly_advice_4') }}</span></li>
				<li class="d-flex align-items-start gap-2"><i class="bi bi-check-circle-fill text-primary mt-1 flex-shrink-0"></i> <span>{{ trans('global.sell_quickly_advice_5') }}</span></li>
			</ul>
		</div>
	</div>
	
</div>
