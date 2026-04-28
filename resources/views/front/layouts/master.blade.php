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
@php
	$htmlLang = getLangTag(config('app.locale'));
	$langDirection = config('lang.direction');
	$userThemePreference = currentUserThemePreference();
	
	$htmlDir = ($langDirection == 'rtl') ? ' dir="rtl"' : '';
	$htmlTheme = ($userThemePreference == 'dark') ? ' data-bs-theme="dark"' : '';
	$showIconOnly = true;
	
	$helpers = getViewHelpersNames(snakeCase: true);
	$addons = array_keys((array)config('addons'));
@endphp
<!DOCTYPE html>
<html lang="{{ $htmlLang }}"{!! $htmlDir . $htmlTheme !!}>
<head>
	<meta charset="{{ config('larapen.core.charset', 'utf-8') }}">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	@include('front.common.meta-robots')
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="shortcut icon" href="{{ config('settings.app.favicon_url') }}">
	<title>{!! MetaTag::get('title') !!}</title>
	{!! MetaTag::tag('description') !!}{!! MetaTag::tag('keywords') !!}
	<link rel="canonical" href="{{ request()->fullUrl() }}"/>
	{{-- Specify a default target for all hyperlinks and forms on the page --}}
	<base target="_top"/>
	@if (isset($post))
		@if (isVerifiedPost($post))
			@if (config('services.facebook.client_id'))
				<meta property="fb:app_id" content="{{ config('services.facebook.client_id') }}" />
			@endif
			{!! $og->renderTags() !!}
			{!! MetaTag::twitterCard() !!}
		@endif
	@else
		@if (config('services.facebook.client_id'))
			<meta property="fb:app_id" content="{{ config('services.facebook.client_id') }}" />
		@endif
		{!! $og->renderTags() !!}
		{!! MetaTag::twitterCard() !!}
	@endif
	@include('feed::links')
	{!! seoSiteVerification() !!}
	
	@if (file_exists(public_path('manifest.json')))
		<link rel="manifest" href="{{ url()->asset('manifest.json') }}">
	@endif
	
    @yield('before_styles')
	
	{{-- App CSS files (Handled by Mix) --}}
	@if ($langDirection == 'rtl')
		<link href="https://fonts.googleapis.com/css?family=Cairo|Changa" rel="stylesheet">
		<link href="{{ url(mix('dist/front/styles.rtl.css')) }}" rel="stylesheet">
	@else
		<link href="{{ url(mix('dist/front/styles.css')) }}" rel="stylesheet">
	@endif
	
	{{-- AdsBlocker Addon CSS --}}
	@if (config('addons.detectadsblocker.installed'))
		<link href="{{ mixStaticFile(url('cache/addons/detectadsblocker/assets/css/style.css')) }}" rel="stylesheet">
	@endif
	
	{{-- Generated Static CSS files --}}
	@php
		$skin = request()->query('skin') ?? config('settings.style.skin');
		$skinStylePath = "cache/css/skins/front/{$skin}.css";
		$stylePath = "cache/css/front-style.css";
		$homepageStylePath = "cache/css/front-homepage.css";
	@endphp
	@if (file_exists(public_path($skinStylePath)))
		<link href="{{ mixStaticFile(url()->asset($skinStylePath)) }}" rel="stylesheet">
	@endif
	@if (file_exists(public_path($stylePath)))
		<link href="{{ mixStaticFile(url()->asset($stylePath)) }}" rel="stylesheet">
	@endif
	@if (file_exists(public_path($homepageStylePath)))
		<link href="{{ mixStaticFile(url()->asset($homepageStylePath)) }}" rel="stylesheet">
	@endif
	
	{{-- Custom CSS --}}
	<link href="{{ mixStaticFile(url()->asset('dist/front/custom.css')) }}" rel="stylesheet">
	
    @yield('after_styles')
	@stack('after_styles_stack')
	
	@stack('before_helpers_styles_stack')
	
	@if (!empty($helpers))
		@foreach($helpers as $helper)
			@stack($helper . '_styles')
		@endforeach
	@endif
	
	@stack('after_helpers_styles_stack')
	
	@if (!empty($addons))
		@foreach($addons as $addon)
			@yield($addon . '_styles')
		@endforeach
	@endif
    
    @if (config('settings.style.custom_css'))
		{!! printCss(config('settings.style.custom_css')) . "\n" !!}
    @endif
	
	<style>
		html, body {
			overflow-x: hidden;
			width: 100%;
			position: relative;
		}

		/* Modern Login Modal */
		#quickLogin .modal-content {
			border-radius: 16px !important;
			border: none !important;
			box-shadow: 0 20px 40px rgba(0,0,0,0.15) !important;
		}
		#quickLogin .modal-header {
			border-bottom: none !important;
			padding: 1.5rem 1.5rem 0.5rem !important;
		}
		#quickLogin .modal-title {
			font-weight: 700 !important;
			color: #1a202c !important;
		}
		#quickLogin .modal-title i {
			color: #28a745 !important;
			margin-right: 8px;
		}
		#quickLogin .modal-body {
			padding: 1.5rem 2rem !important;
		}
		#quickLogin .auth-field-item, 
		#quickLogin .mb-3 {
			margin-bottom: 1.5rem !important; /* Spacing between fields */
		}
		#quickLogin label {
			margin-bottom: 0.5rem !important;
			font-weight: 600 !important;
			color: #475569 !important;
		}
		#quickLogin .form-control {
			border-radius: 10px !important;
			padding: 0.6rem 1rem !important;
			border: 1px solid #e2e8f0 !important;
			background-color: #f8fafc !important;
		}
		#quickLogin .form-control:focus {
			background-color: #ffffff !important;
			border-color: #28a745 !important;
			box-shadow: 0 0 0 4px rgba(40, 167, 69, 0.1) !important;
		}
		#quickLogin .btn-primary {
			background-color: #28a745 !important;
			border-color: #28a745 !important;
			border-radius: 10px !important;
			padding: 0.6rem 2rem !important;
			font-weight: 600 !important;
		}
		#quickLogin .btn-secondary {
			background-color: #f1f5f9 !important;
			border: none !important;
			color: #475569 !important;
			border-radius: 10px !important;
			padding: 0.6rem 1.5rem !important;
			font-weight: 600 !important;
		}
		#quickLogin .link-primary, #quickLogin .auth-field, #quickLogin a {
			color: #28a745 !important;
			font-weight: 600 !important;
			text-decoration: none !important;
		}
		#quickLogin .input-group-text {
			background-color: #f8fafc !important;
			border-color: #e2e8f0 !important;
			border-radius: 10px 0 0 10px !important;
			color: #64748b !important;
		}
		
		#quickLogin .hr-text {
			display: flex;
			align-items: center;
			text-align: center;
			margin: 1.5rem 0;
			color: #94a3b8;
			font-size: 0.85rem;
		}
		#quickLogin .hr-text::before, #quickLogin .hr-text::after {
			content: '';
			flex: 1;
			border-bottom: 1px solid #e2e8f0;
		}
		#quickLogin .hr-text:not(:empty)::before { margin-right: .5em; }
		#quickLogin .hr-text:not(:empty)::after { margin-left: .5em; }

		/* Social Buttons Global Style (Modal & Pages) */
		html body #main-wrapper .btn-social,
		html body .modal .btn-social,
		html body #quickLogin a.btn-social {
			border-radius: 8px !important;
			padding: 0.6rem 0.5rem !important;
			font-weight: 600 !important;
			display: flex !important;
			align-items: center;
			justify-content: center;
			gap: 4px !important;
			border: 1px solid #e2e8f0 !important;
			background: #ffffff !important;
			color: #1a202c !important; /* Force dark text to override modal green */
			transition: all 0.2s ease !important;
			font-size: 0.85rem !important;
			box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
			text-decoration: none !important;
			white-space: nowrap !important;
		}

		html body .modal .btn-facebook,
		html body #quickLogin a.btn-facebook { border-color: #1877F2 !important; }
		
		html body .modal .btn-google,
		html body .modal a[class*="btn-google"],
		html body #quickLogin a.btn-google { border-color: #ea4335 !important; }

		html body .modal .btn-apple,
		html body #quickLogin a.btn-apple { border-color: #000000 !important; }

		html body .modal .btn-social:hover,
		html body #quickLogin a.btn-social:hover {
			background: #f8fafc !important;
			transform: translateY(-1px);
			box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
		}

		html body .modal .btn-facebook:hover,
		html body #quickLogin a.btn-facebook:hover { 
			color: #1877F2 !important; 
			border-color: #1877F2 !important; 
		}
		
		html body .modal .btn-google:hover,
		html body #quickLogin a.btn-google:hover { 
			color: #ea4335 !important; 
			border-color: #ea4335 !important; 
		}

		/* Force row layout for social media in modal */
		#quickLogin .social-media {
			display: flex !important;
			flex-wrap: wrap !important;
			justify-content: center !important;
		}
		#quickLogin .social-media > div {
			flex: 0 0 50% !important;
			max-width: 50% !important;
			padding: 0 4px !important;
		}
	</style>
	
	@if (config('settings.other.js_code'))
		{!! printJs(config('settings.other.js_code')) . "\n" !!}
	@endif
	
	@include('front.common.js.document')
 
	<script>
		paceOptions = {
			elements: true
		};
	</script>
	<script src="{{ url()->asset('assets/plugins/pace-js/1.2.4/pace.min.js') }}"></script>
	<link href="{{ url()->asset('assets/plugins/pace-js/1.2.4/pace-theme-default.min.css') }}" rel="stylesheet">
	
	@if (!empty($helpers))
		@foreach($helpers as $helper)
			@stack($helper . '_head_scripts')
		@endforeach
	@endif
</head>
<body class="bg-body text-body-emphasis skin">
@section('header')
	@include('front.layouts.partials.header')
@show

<main>
	@section('search')
	@show
	
	@section('wizard')
	@show
	
	@include('helpers.flash.default')
	
	@yield('content')
	
	@section('info')
	@show
	
	@include('front.layouts.partials.advertising.auto')
	
	@section('modal_location')
	@show
	@section('modal_languages')
	@show
	@section('modal_abuse')
	@show
	@section('modal_message')
	@show
	
	@include('front.layouts.partials.modal.countries')
	@include('front.layouts.partials.modal.error')
	@include('cookie-consent::index')
	
	@if (config('addons.detectadsblocker.installed'))
		@if (view()->exists('detectadsblocker::modal'))
			@include('detectadsblocker::modal')
		@endif
	@endif
</main>

@section('footer')
	@include('front.layouts.partials.footer')
@show

@include('front.common.js.init')

<script>
	var countryCode = '{{ config('country.code', 0)  }}';
	var timerNewMessagesChecking = {{ (int)config('settings.other.timer_new_messages_checking', 0)  }};
	
	{{-- Theme Preference (light/dark/system) --}}
	var isSettingsAppDarkModeEnabled = {{ isSettingsAppDarkModeEnabled() ? 'true' : 'false' }};
	var isSettingsAppSystemThemeEnabled = {{ isSettingsAppSystemThemeEnabled() ? 'true' : 'false' }};
	var userThemePreference = {!! !empty($userThemePreference) ? "'$userThemePreference'" : 'null' !!};
	var showIconOnly = {{ $showIconOnly ? 'true' : 'false' }};
	
	{{-- The app's default auth field --}}
	var defaultAuthField = '{{ old('auth_field', getAuthField()) }}';
	var phoneCountry = '{{ config('country.code') }}';
	
	{{-- Others global variables --}}
	var fakeLocationsResults = "{{ config('settings.listings_list.fake_locations_results', 0) }}";
</script>

@stack('before_scripts_stack')
@yield('before_scripts')

{{-- Toggle Password Visibility --}}
@if (view()->exists('auth.layouts.js.translations'))
	@include('auth.layouts.js.translations')
@endif

{{-- App JS files (Handled by Mix) --}}
<script src="{{ url(mix('dist/front/scripts.js')) }}"></script>

{{-- Lazy Loading JS --}}
@if (config('settings.optimization.lazy_loading_activation') == 1)
	<script src="{{ url('assets/plugins/lazysizes/lazysizes.min.js') }}" async=""></script>
@endif

{{-- AdsBlocker Addon JS --}}
@if (config('addons.detectadsblocker.installed'))
	<script src="{{ mixStaticFile(url('cache/addons/detectadsblocker/assets/js/script.js')) }}"></script>
@endif

<script>
	onDocumentReady((event) => {
		{{-- Social Media Share --}}
		SocialShare.init({width: 640, height: 480});
		
		{{-- Modal Login --}}
		@if (isset($errors) && $errors->any())
			@if ($errors->any() && old('quickLoginForm')=='1')
				{{-- Re-open the modal if error occured --}}
				openLoginModal();
			@endif
		@endif
	});
</script>

@yield('after_scripts')
@stack('after_scripts_stack')

@stack('before_helpers_scripts_stack')

@if (!empty($helpers))
	@foreach($helpers as $helper)
		@stack($helper . '_scripts')
	@endforeach
@endif

@stack('after_helpers_scripts_stack')

@if (!empty($addons))
	@foreach($addons as $addon)
		@yield($addon . '_scripts')
	@endforeach
@endif

@if (config('settings.footer.tracking_code'))
	{!! printJs(config('settings.footer.tracking_code')) . "\n" !!}
@endif
</body>
</html>
