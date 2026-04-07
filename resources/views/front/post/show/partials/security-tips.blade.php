<div class="modal fade" id="securityTips" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			
			<div class="modal-header border-0 pb-0">
				<h5 class="modal-title fs-4 fw-bold text-center w-100" id="securityTipsLabel">
					{{ trans('global.phone_number') }}
				</h5>
				<button type="button" class="btn-close position-absolute end-0 me-3 mt-1" data-bs-dismiss="modal" aria-label="{{ trans('global.Close') }}"></button>
			</div>
			
			@php
				$phoneModal = '';
				$phoneModalLink = '';
				if (config('settings.listing_page.hide_phone_number') == '') {
					$phoneModal = data_get($post, 'phone');
					if (!empty($phoneModal)) {
						$phoneModalLink = 'tel:' . $phoneModal;
					}
				}
			@endphp
			
			<div class="modal-body p-4">
				<div class="row">
					<div class="col-12 text-center mb-4">
						<div id="phoneModal" class="p-4 bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-4 h1 fw-bolder text-primary shadow-sm">
							<i class="bi bi-telephone-fill me-2 small"></i>{{ $phoneModal }}
						</div>
					</div>
					<div class="col-12 bg-danger bg-opacity-10 rounded-4 p-4 mt-2">
						<h5 class="text-danger fw-bold mb-3">
							<i class="bi bi-exclamation-triangle-fill me-2"></i> {!! trans('global.security_tips_title') !!}
						</h5>
						<div class="text-danger-emphasis small lh-lg">
							{!! trans('global.security_tips_text', ['appName' => config('app.name')]) !!}
						</div>
					</div>
				</div>
			</div>
			
			<div class="modal-footer border-0 pt-0 pb-4 justify-content-center">
				<a id="phoneModalLink" href="{{ $phoneModalLink }}" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow-sm">
					<i class="bi bi-telephone-outbound me-2"></i> {{ trans('global.call_now') }}
				</a>
				<button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">{{ trans('global.Close') }}</button>
			</div>
			
		</div>
	</div>
</div>
