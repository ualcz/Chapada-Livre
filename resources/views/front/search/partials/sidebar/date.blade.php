@php
	// Clear Filter Button
	$clearFilterBtn = urlGen()->getDateFilterClearLink($cat ?? null, $city ?? null);
@endphp
{{-- Date --}}
<div class="container p-0 vstack gap-2">
	<h5 class="border-bottom border-success border-opacity-50 pb-2 d-flex justify-content-between align-items-center">
		<span class="fw-bold text-success text-uppercase fs-6 mb-0">{{ trans('global.Date Posted') }}</span> {!! $clearFilterBtn !!}
	</h5>
	<div class="list-group list-group-flush mb-0">
		@if (!empty($periodList))
			@foreach($periodList as $key => $value)
				<label class="list-group-item list-group-item-action d-flex align-items-center border-0 cursor-pointer" for="postedDate_{{ $key }}">
					<input class="form-check-input me-2 mt-0"
					       type="radio"
					       name="postedDate"
					       value="{{ $key }}"
					       id="postedDate_{{ $key }}" {{ (request()->query('postedDate')==$key) ? 'checked="checked"' : '' }}
					>
					<span class="fw-normal">{{ $value }}</span>
				</label>
			@endforeach
		@endif
		<input type="hidden"
		       id="postedQueryString"
		       name="postedQueryString"
		       value="{{ \App\Helpers\Common\Arr::query(request()->except(['page', 'postedDate'])) }}"
		>
	</div>
</div>

@section('after_scripts')
	@parent
	{{-- Check out the JS code at: "../sidebar.blade.php" --}}
@endsection
