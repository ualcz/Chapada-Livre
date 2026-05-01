@php
	$stats ??= [];
	$countThreadsWithNewMessage = (int)data_get($stats, 'threads.withNewMessage');
	
	$navLinks = [
		'inbox' => [
			'label'    => trans('global.inbox'),
			'url'      => url(urlGen()->getAccountBasePath() . '/messages'),
			'isActive' => (!request()->has('filter') || request()->query('filter')==''),
            'icon'     => 'bi-inbox',
		],
		'unread' => [
			'label'    => trans('global.unread'),
			'url'      => url(urlGen()->getAccountBasePath() . '/messages?filter=unread'),
			'isActive' => (request()->query('filter')=='unread'),
            'icon'     => 'bi-envelope',
		],
		'started' => [
			'label'    => trans('global.started'),
			'url'      => url(urlGen()->getAccountBasePath() . '/messages?filter=started'),
			'isActive' => (request()->query('filter')=='started'),
            'icon'     => 'bi-star',
		],
		'important' => [
			'label'    => trans('global.important'),
			'url'      => url(urlGen()->getAccountBasePath() . '/messages?filter=important'),
			'isActive' => (request()->query('filter')=='important'),
            'icon'     => 'bi-exclamation-circle',
		],
	];
@endphp
<div class="px-2 py-3 border-bottom">
	<ul class="nav nav-pills flex-column messenger-filters">
		@foreach($navLinks as $key => $item)
			@php
				$activeClass = $item['isActive'] ? ' active' : '';
			@endphp
			<li class="nav-item mb-1">
				<a class="nav-link{{ $activeClass }} d-flex align-items-center" href="{{ $item['url'] }}">
					<i class="bi {{ $item['icon'] }} me-2"></i>
					<span class="flex-grow-1">{{ $item['label'] }}</span>
					@if ($key == 'inbox' && $countThreadsWithNewMessage > 0)
						<span class="badge rounded-pill bg-danger ms-2">
							{{ \App\Helpers\Common\Num::short($countThreadsWithNewMessage) }}
						</span>
					@endif
				</a>
			</li>
		@endforeach
	</ul>
</div>

<div class="px-3 py-3">
    <a href="{{ url(urlGen()->getAccountBasePath()) }}" class="btn btn-primary w-100 messenger-back-btn d-flex align-items-center justify-content-center">
        <i class="bi bi-arrow-left-circle me-2"></i>
        {{ trans('global.back_to_list') }}
    </a>
</div>

