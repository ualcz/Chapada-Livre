@php
	$userStats ??= [];
	
	$countPendingApprovalPosts = (int)data_get($userStats, 'posts.pendingApproval', 0);
	$countArchivedPosts = (int)data_get($userStats, 'posts.archived', 0);
	$countPosts = (int)data_get($userStats, 'posts.published', 0);
	$postsVisits = (int)data_get($userStats, 'posts.visits', 0);
	$countFavoritePosts = (int)data_get($userStats, 'posts.favourite', 0);
	$countThreads = (int)data_get($userStats, 'threads.all', 0);
	
	$statsData = [
		'activePosts' => [
			'icon'      => 'fa-solid fa-bullhorn',
			'countItem' => \App\Helpers\Common\Num::short($countPosts),
			'label'     => trans_choice('global.count_active_posts', getPlural($countPosts), [], config('app.locale')),
			'url'       => url(urlGen()->getAccountBasePath() . '/posts/list'),
            'color'     => '#4e73df'
		],
		'postsVisits' => [
			'icon'      => 'fa-regular fa-eye',
			'countItem' => \App\Helpers\Common\Num::short($postsVisits),
			'label'     => trans_choice('global.count_visits', getPlural($postsVisits), [], config('app.locale')),
			'url'       => url(urlGen()->getAccountBasePath() . '/posts/list'),
            'color'     => '#1cc88a'
		],
		'favoritePosts' => [
			'icon'      => 'fa-regular fa-heart',
			'countItem' => \App\Helpers\Common\Num::short($countFavoritePosts),
			'label'     => trans_choice('global.count_favorites', getPlural($countFavoritePosts), [], config('app.locale')),
			'url'       => url(urlGen()->getAccountBasePath() . '/saved-posts'),
            'color'     => '#e74a3b'
		],
		'messages' => [
			'icon'      => 'fa-solid fa-envelope',
			'countItem' => \App\Helpers\Common\Num::short($countThreads),
			'label'     => trans_choice('global.count_mails', getPlural($countThreads), [], config('app.locale')),
			'url'       => url(urlGen()->getAccountBasePath() . '/messages'),
            'color'     => '#f6c23e'
		],
		'pendingApprovalPosts' => [
			'icon'      => 'bi bi-hourglass-split',
			'countItem' => \App\Helpers\Common\Num::short($countPendingApprovalPosts),
			'label'     => trans_choice('global.count_pending_approval_posts', getPlural($countPendingApprovalPosts), [], config('app.locale')),
			'url'       => url(urlGen()->getAccountBasePath() . '/posts/pending-approval'),
            'color'     => '#858796'
		],
		'archivedPosts' => [
			'icon'      => 'fa-solid fa-calendar-xmark',
			'countItem' => \App\Helpers\Common\Num::short($countArchivedPosts),
			'label'     => trans_choice('global.count_archived_posts', getPlural($countArchivedPosts), [], config('app.locale')),
			'url'       => url(urlGen()->getAccountBasePath() . '/posts/archived'),
            'color'     => '#5a5c69'
		],
	];
@endphp

<style>
    .stats-card-modern {
        background: transparent;
        border: none !important;
    }
    
    .stat-box {
        background: #fff;
        border: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: 1rem;
        padding: 1.25rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .stat-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        border-color: var(--bs-primary);
    }
    
    .stat-icon-wrapper {
        width: 54px;
        height: 54px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        font-size: 1.5rem;
        transition: all 0.3s ease;
    }
    
    .stat-count {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 0.25rem;
        letter-spacing: -0.01em;
    }
    
    .stat-label {
        font-size: 0.8rem;
        color: #6c757d;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    @media (max-width: 576px) {
        .stat-box {
            padding: 1rem 0.5rem;
        }
        .stat-icon-wrapper {
            width: 42px;
            height: 42px;
            font-size: 1.2rem;
            margin-bottom: 0.75rem;
        }
        .stat-count {
            font-size: 1.1rem;
        }
        .stat-label {
            font-size: 0.7rem;
        }
    }

    [data-bs-theme="dark"] .stat-box {
        background: rgba(255, 255, 255, 0.03);
        border-color: rgba(255, 255, 255, 0.1);
    }
    [data-bs-theme="dark"] .stat-count {
        color: #fff;
    }
    [data-bs-theme="dark"] .stat-label {
        color: #a0a0a0;
    }
</style>

<div class="stats-card-modern">
	<div class="container px-0">
		<div class="row g-3">
			@foreach($statsData as $key => $item)
				<div class="col-6 col-lg-4">
					<a href="{{ $item['url'] }}" class="text-decoration-none">
						<div class="stat-box">
							<div class="stat-icon-wrapper" style="background: {{ $item['color'] }}15; color: {{ $item['color'] }};">
								<i class="{{ $item['icon'] }}"></i>
							</div>
							<div class="stat-count">
								{{ $item['countItem'] }}
							</div>
							<div class="stat-label">
								{{ $item['label'] }}
							</div>
						</div>
					</a>
				</div>
			@endforeach
		</div>
	</div>
</div>
