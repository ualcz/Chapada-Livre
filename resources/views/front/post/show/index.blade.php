{{--
 * LaraClassifier - Classified Ads Web Application
 * Copyright (c) BeDigit. All Rights Reserved
 *
 * Website: https://laraclassifier.com
 * Author: Mayeul Akpovi (BeDigit - https://bedigit.com)
 *
 * LICENSE
 * -------
 * This software is provided under a license agreement and may only be used or copied
 * in accordance with its terms, including the inclusion of the above copyright notice.
 * As this software is sold exclusively on CodeCanyon,
 * please review the full license details here: https://codecanyon.net/licenses/standard
--}}
@php use App\Enums\BootstrapColor; @endphp
@extends('front.layouts.master')

@section('after_styles')
	@parent
	<style>
		/* ---- Página de Detalhes Premium ---- */
		.post-detail-nav-bar {
			background: var(--bs-body-bg);
			border-bottom: 1px solid rgba(var(--bs-secondary-rgb), 0.12);
			padding: 0.6rem 0;
		}
		.post-detail-nav-bar .breadcrumb {
			margin-bottom: 0;
			font-size: 0.85rem;
		}
		.post-detail-nav-bar .back-link {
			font-size: 0.85rem;
			font-weight: 600;
			color: var(--bs-secondary);
			text-decoration: none;
			display: flex;
			align-items: center;
			gap: 0.35rem;
			transition: color 0.2s;
		}
		.post-detail-nav-bar .back-link:hover {
			color: var(--bs-primary);
		}
		.items-details-wrapper {
			background: var(--bs-body-bg) !important;
			border: 1px solid rgba(var(--bs-secondary-rgb), 0.12) !important;
			border-radius: 1rem !important;
			box-shadow: 0 4px 24px rgba(0,0,0,0.06) !important;
			padding: 1.75rem !important;
		}
		.post-title-section h1 a {
			color: var(--bs-body-color);
			text-decoration: none;
			transition: color 0.2s;
		}
		.post-title-section h1 a:hover {
			color: var(--bs-primary);
		}
		.post-meta-bar {
			background: rgba(var(--bs-secondary-rgb), 0.05);
			border-radius: 0.5rem;
			padding: 0.6rem 1rem;
			margin: 0.75rem 0 1rem;
			font-size: 0.85rem;
			color: var(--bs-secondary);
			display: flex;
			align-items: center;
			gap: 1.25rem;
			flex-wrap: wrap;
		}
		.post-meta-bar .meta-item {
			display: flex;
			align-items: center;
			gap: 0.35rem;
		}
		.post-meta-bar .meta-item i {
			color: var(--bs-primary);
			font-size: 0.9rem;
		}
		@media (max-width: 576px) {
			.items-details-wrapper {
				padding: 1rem !important;
			}
			.post-meta-bar {
				gap: 0.75rem;
			}
		}
	</style>
@endsection

@php
	$post ??= [];
	$catBreadcrumb ??= [];
	$topAdvertising ??= [];
	$bottomAdvertising ??= [];
	
	$similarListingsType = config('settings.listing_page.similar_listings');
	$similarListingsWidget = (config('settings.listing_page.similar_listings_in_carousel') ? 'carousel' : 'normal');
	$isSimilarListingsEnabled = ($similarListingsType == '1' || $similarListingsType == '2');
@endphp

@section('content')
	@php
		$paddingTopExists = true;
	@endphp
	
	@php
		$withMessage = !session()->has('flash_messages');
		$resendVerificationLink = getResendVerificationLink(withMessage: $withMessage);
	@endphp
	@if (!empty($resendVerificationLink))
		<div class="container mt-3">
			<div class="alert alert-info text-center mb-0">
				{!! $resendVerificationLink !!}
			</div>
		</div>
	@endif
	
	{{-- Archived listings message --}}
	@if (!empty(data_get($post, 'archived_at')))
		<div class="container mt-3">
			<div class="alert alert-warning mb-0" role="alert">
				{!! trans('global.This listing has been archived') !!}
			</div>
		</div>
	@endif

	<div class="main-container">
		@if (!empty($topAdvertising))
			@include('front.layouts.partials.advertising.top', ['paddingTopExists' => $paddingTopExists ?? false])
			@php
				$paddingTopExists = false;
			@endphp
		@endif
		
		{{-- Breadcrumb Bar --}}
		<div class="post-detail-nav-bar">
			<div class="container">
				<div class="d-flex justify-content-between align-items-center">
					<nav aria-label="breadcrumb" role="navigation">
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{ url('/') }}" class="{{ linkClass() }}">
									<i class="fa-solid fa-house"></i>
								</a>
							</li>
							<li class="breadcrumb-item">
								<a href="{{ url('/') }}" class="{{ linkClass() }}">{{ config('country.name') }}</a>
							</li>
							@if (is_array($catBreadcrumb) && count($catBreadcrumb) > 0)
								@foreach($catBreadcrumb as $key => $value)
									<li class="breadcrumb-item">
										<a href="{{ $value->get('url') }}" class="{{ linkClass() }}">{!! $value->get('name') !!}</a>
									</li>
								@endforeach
							@endif
							<li class="breadcrumb-item active" aria-current="page">
								{{ str(data_get($post, 'title'))->limit(55) }}
							</li>
						</ol>
					</nav>
					<a href="{{ rawurldecode(url()->previous()) }}" class="back-link">
						<i class="fa-solid fa-arrow-left"></i> {{ trans('global.back_to_results') }}
					</a>
				</div>
			</div>
		</div>

		<div class="container my-4">
			<div class="row g-4">
				{{-- Content --}}
				<div class="col-lg-9">
					@php
						$overflowStyle = (!auth()->check() && addon_exists('reviews')) ? 'overflow: visible;' : '';
					@endphp
					<div class="items-details-wrapper" style="{{ $overflowStyle }}">
						{{-- Title --}}
						<div class="post-title-section d-flex justify-content-between align-items-start flex-wrap gap-2">
							<h1 class="fs-3 fw-bold mb-0">
								<a href="{{ urlGen()->post($post) }}" title="{{ data_get($post, 'title') }}">
									{{ data_get($post, 'title') }}
								</a>
								@if (data_get($post, 'featured') == 1 && !empty(data_get($post, 'payment.package')))
									@php
										$ribbonColor = data_get($post, 'payment.package.ribbon');
										$ribbonColorClass = BootstrapColor::Text->getColorClass($ribbonColor);
										$packageShortName = data_get($post, 'payment.package.short_name');
									@endphp
									<i class="fa-solid fa-check-circle {{ $ribbonColorClass }} ms-1"
									   data-bs-placement="bottom"
									   data-bs-toggle="tooltip"
									   title="{{ $packageShortName }}"
									></i>
								@endif
							</h1>
							@if (config('settings.listing_form.show_listing_type') && !empty(data_get($post, 'postType')))
								<span class="badge rounded-pill text-bg-dark flex-shrink-0">
									{{ data_get($post, 'postType.label') }}
								</span>
							@endif
						</div>
						
						{{-- Meta bar --}}
						<div class="post-meta-bar">
							@if (!config('settings.listing_page.hide_date'))
								<span class="meta-item">
									<i class="fa-regular fa-clock"></i> {!! data_get($post, 'created_at_formatted') !!}
								</span>
							@endif
							<span class="meta-item">
								<i class="bi bi-folder"></i> {{ data_get($post, 'category.parent.name', data_get($post, 'category.name')) }}
							</span>
							<span class="meta-item">
								<i class="bi bi-geo-alt"></i> {{ data_get($post, 'city.name') }}
							</span>
							<span class="meta-item">
								<i class="bi bi-eye"></i> {{ data_get($post, 'visits_formatted') }}
							</span>
							<span class="meta-item ms-auto text-muted" style="font-size:0.8rem;">
								{{ trans('global.reference') }}: {{ data_get($post, 'reference') }}
							</span>
						</div>
						
						{{-- Pictures --}}
						@include('front.post.show.partials.pictures-slider')
						
						{{-- Reviews Stars --}}
						@if (config('addons.reviews.installed'))
							@if (view()->exists('reviews::ratings-single'))
								@include('reviews::ratings-single')
							@endif
						@endif
						
						{{-- Details --}}
						@include('front.post.show.partials.details')
					</div>
				</div>
				
				{{-- Sidebar --}}
				<div class="col-lg-3">
					@include('front.post.show.partials.sidebar')
				</div>
			</div>

		</div>
		
		@if ($isSimilarListingsEnabled)
			@php
				$widgetView = 'front.search.partials.posts.widget.' . $similarListingsWidget;
			@endphp
			@if (view()->exists($widgetView))
				@include($widgetView, [
					'widget'       => ($widgetSimilarPosts ?? null),
					'firstSection' => false
				])
			@endif
		@endif
		
		@include('front.layouts.partials.advertising.bottom', ['firstSection' => false])
		
		@if (isVerifiedPost($post))
			@include('front.layouts.partials.tools.facebook-comments', ['firstSection' => false])
		@endif
		
	</div>
	
	@includeWhen(!auth()->check(), 'auth.login.partials.modal')
@endsection
@php
	if (!session()->has('emailVerificationSent') && !session()->has('phoneVerificationSent')) {
		if (session()->has('message')) {
			session()->forget('message');
		}
	}
@endphp

@section('modal_message')
	@if (config('settings.listing_page.show_security_tips') == '1')
		@include('front.post.show.partials.security-tips')
	@endif
	@if (auth()->check() || config('settings.listing_page.guest_can_contact_authors') == '1')
		@include('front.account.messenger.modal.create')
	@endif
@endsection

@section('before_scripts')
	<script>
		var showSecurityTips = '{{ config('settings.listing_page.show_security_tips', '0') }}';
	</script>
@endsection

@section('after_scripts')
	<script>
		{{-- Favorites Translation --}}
        var lang = {
            labelSavePostSave: "{!! trans('global.Save listing') !!}",
            labelSavePostRemove: "{!! trans('global.Remove favorite') !!}",
            loginToSavePost: "{!! trans('global.Please log in to save the Listings') !!}",
            loginToSaveSearch: "{!! trans('global.Please log in to save your search') !!}"
        };
		
		onDocumentReady((event) => {
			{{-- Tooltip --}}
			const tooltipEls = document.querySelectorAll('[rel="tooltip"]');
			if (tooltipEls) {
				let tooltipTriggerList = [].slice.call(tooltipEls);
				let tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
					return new bootstrap.Tooltip(tooltipTriggerEl)
				});
			}
			
			{{-- Keep the current tab active with Twitter Bootstrap after a page reload --}}
			const tabEls = document.querySelectorAll('button[data-bs-toggle="tab"]');
			if (tabEls.length > 0) {
				tabEls.forEach((tabButton) => {
					tabButton.addEventListener('shown.bs.tab', function (e) {
						/* Save the latest tab; use cookies if you like 'em better: */
						/* localStorage.setItem('lastTab', tabButton.getAttribute('href')); */
						localStorage.setItem('lastTab', tabButton.getAttribute('data-bs-target'));
					});
				});
			}
			
			{{-- Go to the latest tab, if it exists: --}}
            let lastTab = localStorage.getItem('lastTab');
            if (lastTab) {
				{{-- let triggerEl = document.querySelector('a[href="' + lastTab + '"]'); --}}
				let triggerEl = document.querySelector('button[data-bs-target="' + lastTab + '"]');
				if (typeof triggerEl !== 'undefined' && triggerEl !== null) {
					let tabObj = new bootstrap.Tab(triggerEl);
					if (tabObj !== null) {
						tabObj.show();
					}
				}
            }
		});
	</script>
@endsection
