@php
	$defaultMessage = trans('global.No message received');
	$messages = [
		'unread'    => trans('global.No new thread or with new messages'),
		'started'   => trans('global.No thread started by you'),
		'important' => trans('global.No message marked as important'),
	];
	$filter = request()->query('filter');
	$filter = (!empty($filter) && is_string($filter)) ? $filter : '-';
	$emptyMessage = $messages[$filter] ?? $defaultMessage;
@endphp
<div class="row my-5">
	<div class="col-12 text-center">
		{{ $emptyMessage }}
	</div>
</div>
