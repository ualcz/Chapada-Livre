<style>
    .stats-container {
        padding: 40px 0;
    }
    .stat-card {
        background: #fff;
        border-radius: 20px;
        padding: 30px;
        transition: all 0.3s ease;
        border: 1px solid #f3f3f3;
        height: 100%;
        display: flex;
        align-items: center;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.06);
    }
    .stat-icon-box {
        width: 70px;
        height: 70px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 20px;
    }
    .stat-icon-box i {
        font-size: 2rem;
    }
    .stat-value {
        font-size: 2.2rem;
        font-weight: 800;
        line-height: 1.1;
        margin-bottom: 5px;
        color: #333;
    }
    .stat-label {
        font-size: 1rem;
        color: #777;
        font-weight: 500;
    }
    .stat-item-listings .stat-icon-box { background: rgba(var(--bs-primary-rgb), 0.12); color: var(--bs-primary); }
    .stat-item-users .stat-icon-box { background: rgba(25, 135, 84, 0.1); color: #198754; }
    .stat-item-locations .stat-icon-box { background: rgba(253, 126, 20, 0.1); color: #fd7e14; }

    @media (max-width: 767.98px) {
        .stat-card {
            padding: 15px 5px;
            flex-direction: column;
            text-align: center;
            justify-content: center;
        }
        .stat-icon-box {
            width: 45px;
            height: 45px;
            margin-right: 0;
            margin-bottom: 8px;
        }
        .stat-icon-box i {
            font-size: 1.3rem;
        }
        .stat-value {
            font-size: 1.1rem;
        }
        .stat-label {
            font-size: 0.65rem;
            line-height: 1.2;
        }
    }
</style>

@php
	$sectionOptions = $statsOptions ?? [];
	
	$iconPosts = $sectionOptions['icon_count_listings'] ?? 'bi bi-megaphone';
	$iconUsers = $sectionOptions['icon_count_users'] ?? 'bi bi-people';
	$iconLocations = $sectionOptions['icon_count_locations'] ?? 'bi bi-geo-alt';
	$prefixPosts = $sectionOptions['prefix_count_listings'] ?? '';
	$suffixPosts = $sectionOptions['suffix_count_listings'] ?? '';
	$prefixUsers = $sectionOptions['prefix_count_users'] ?? '';
	$suffixUsers = $sectionOptions['suffix_count_users'] ?? '';
	$prefixLocations = $sectionOptions['prefix_count_locations'] ?? '';
	$suffixLocations = $sectionOptions['suffix_count_locations'] ?? '';
	$disableCounterUp = $sectionOptions['disable_counter_up'] ?? false;
	$counterUpDelay = $sectionOptions['counter_up_delay'] ?? 10;
	$counterUpTime = $sectionOptions['counter_up_time'] ?? 2000;
	
	$fullHeight = $sectionOptions['full_height'] ?? '0';
	$isFullHeightEnabled = ($fullHeight == '1');
	$style = $isFullHeightEnabled ? 'height: 100vh; min-height: 100dvh;' : '';
	
	$htmlAttr = $sectionOptions['html_attributes'] ?? '';
	$htmlAttr = !empty($htmlAttr) ? " {$htmlAttr}" : '';
	
	$cssClasses = $sectionOptions['css_classes'] ?? '';
	$cssClasses = !empty($cssClasses) ? " {$cssClasses}" : '';
	
	$sectionData ??= [];
	$stats = (array)($sectionData['count'] ?? []);
	
	$statItems = [
		'listings' => [
			'icon'   => $iconPosts,
			'count'  => (int)data_get($stats, 'posts'),
			'prefix' => $prefixPosts,
			'suffix' => $suffixPosts,
			'label'  => trans('global.classified_ads'),
		],
		'users' => [
			'icon'   => $iconUsers,
			'count'  => (int)data_get($stats, 'users'),
			'prefix' => $prefixUsers,
			'suffix' => $suffixUsers,
			'label'  => trans('global.Trusted Sellers'),
		],
		'locations' => [
			'icon'   => $iconLocations,
			'count'  => (int)data_get($stats, 'locations'),
			'prefix' => $prefixLocations,
			'suffix' => $suffixLocations,
			'label'  => trans('global.locations'),
		],
	];
@endphp

<div class="container{{ $cssClasses }}" style="{!! $style !!}">
	<div class="card border-0 bg-transparent"{!! $htmlAttr !!}>
		<div class="card-body p-0">
			
			<div class="row g-4 stats-container">
				@foreach($statItems as $key => $item)
					@php
						$icon = $item['icon'];
						$count = $item['count'];
						$prefix = $item['prefix'];
						$suffix = $item['suffix'];
						$label = $item['label'];
					@endphp
					<div class="col-lg-4 col-md-4 col-4">
						<div class="stat-card stat-item-{{ $key }}">
							<div class="stat-icon-box">
								<i class="{{ $icon }}"></i>
							</div>
							<div class="stat-content">
								<div class="stat-value">
									@if (!empty($prefix))<span>{{ $prefix }}</span>@endif
									<span class="counter">{{ $count }}</span>
									@if (!empty($suffix))<span>{{ $suffix }}</span>@endif
								</div>
								<div class="stat-label">{{ $label }}</div>
							</div>
						</div>
					</div>
				@endforeach
			</div>
			
		</div>
	</div>
</div>

@section('after_scripts')
	@parent
	@if (!isset($disableCounterUp) || !$disableCounterUp)
		<script>
			onDocumentReady((event) => {
				const counterUp = window.counterUp.default;
				const counterEl = document.querySelector('.counter');
				if (counterEl) {
					counterUp(counterEl, {
						duration: {{ $counterUpTime }},
						delay: {{ $counterUpDelay }}
					});
				}
			});
		</script>
	@endif
@endsection
