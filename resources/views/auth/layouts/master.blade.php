@php
	$htmlLang = getLangTag(config('app.locale'));
	$langDirection = config('lang.direction');
	$userThemePreference = currentUserThemePreference();
	
	$htmlDir = ($langDirection == 'rtl') ? ' dir="rtl"' : '';
	$htmlTheme = ($userThemePreference == 'dark') ? ' theme="dark"' : '';
	$showIconOnly = false;
	
	$helpers = getViewHelpersNames(snakeCase: true);
	
	// Logo
	$logoFactoryUrl = config('larapen.media.logo-factory');
	$logoUrl = '';
	try {
        if (is_link(public_path('storage'))) {
			$logoDarkUrl = config('settings.app.logo_dark_url', $logoFactoryUrl);
			$logoLightUrl = config('settings.app.logo_light_url', $logoFactoryUrl);
			$logoUrl = $logoLightUrl;
		}
    } catch (\Throwable $e) {}
    $logoUrl = !empty($logoUrl) ? $logoUrl : $logoFactoryUrl;
	$logoWidth = (int)config('settings.upload.img_resize_logo_width', 200);
	$logoHeight = (int)config('settings.upload.img_resize_logo_height', 45);
	$logoWidth = \Illuminate\Support\Number::clamp($logoWidth, min: 150, max: 250);
	$logoHeight = \Illuminate\Support\Number::clamp($logoWidth, min: 40, max: 60);
	$logoCssSize = "max-width:{$logoWidth}px; max-height:{$logoHeight}px; width:auto; height:auto;";
    $appName = config('app.name', 'SiteName');
    $logoLabel = config('settings.app.name', $appName);
	$logoAlt = strtolower($logoLabel);
	
	// Hero Background Image
	$heroBgStyle = '';
    try {
        if (is_link(public_path('storage'))) {
            $bgImgUrl = config('settings.auth.hero_image_url');
            $heroBgStyle = 'background-image:url(' . $bgImgUrl . ');';
        }
    } catch (\Throwable $e) {}
@endphp
<!DOCTYPE html>
<html lang="{{ $htmlLang }}"{!! $htmlDir . $htmlTheme !!} data-bs-theme="dark">
<head>
	<meta charset="{{ config('larapen.core.charset', 'utf-8') }}"/>
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1.0, shrink-to-fit=no">
	<link href="{{ config('settings.app.favicon_url') }}" rel="icon"/>
	<title>{!! MetaTag::get('title') !!}</title>
	{!! MetaTag::tag('description') !!}{!! MetaTag::tag('keywords') !!}
	<link rel="canonical" href="{{ request()->fullUrl() }}"/>
	
	{{-- Specify a default target for all hyperlinks and forms on the page --}}
	<base target="_top"/>
	
	@yield('before_styles')
	
	{{-- Auth Module's CSS files (Handled by Mix) --}}
	@if ($langDirection == 'rtl')
		<link href="https://fonts.googleapis.com/css?family=Cairo|Changa" rel="stylesheet">
		<link href="{{ url(mix('dist/auth/styles.rtl.css')) }}" rel="stylesheet">
	@else
		<link href="{{ url(mix('dist/auth/styles.css')) }}" rel="stylesheet">
	@endif
	
	{{-- Generated Static CSS files --}}
	@php
		$skin = request()->query('skin') ?? config('settings.style.skin');
		$skinStylePath = "cache/css/skins/auth/{$skin}.css";
	@endphp
	@if (file_exists(public_path($skinStylePath)))
		<link href="{{ mixStaticFile(url()->asset($skinStylePath)) }}" rel="stylesheet">
	@endif
	
	@yield('after_styles')
	@stack('after_styles_stack')
	
	<style>
		:root {
			--auth-primary: #28a745;
			--auth-bg: #ffffff;
			--auth-border: #edf2f7;
			--auth-text-main: #2d3748;
			--auth-text-muted: #718096;
		}

		.auth-login-register {
			background: #f7fafc;
			color: var(--auth-text-main);
		}
		
		.modern-auth-card {
			background: #ffffff;
			border: 1px solid var(--auth-border);
			border-radius: 12px;
			padding: 2rem 3rem;
			box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
		}
		
		.hero-wrap .hero-mask {
			background: #28a745 !important;
			opacity: 0.85 !important;
		}
		
		.form-control {
			background: #ffffff !important;
			border: 1px solid #e2e8f0 !important;
			border-radius: 8px !important;
			color: var(--auth-text-main) !important;
			padding: 0.65rem 1rem !important;
			transition: all 0.2s ease !important;
		}
		
		.form-control:focus {
			border-color: var(--auth-primary) !important;
			box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1) !important;
			outline: 0;
		}
		
		.btn-primary {
			background: var(--auth-primary) !important;
			border: none !important;
			border-radius: 8px !important;
			padding: 0.7rem 1.5rem !important;
			font-weight: 600 !important;
			transition: all 0.2s ease !important;
		}
		
		.btn-primary:hover {
			background: #218838 !important;
			box-shadow: 0 4px 12px rgba(40, 167, 69, 0.15) !important;
		}
		
		/* Social Buttons */
		.btn-social {
			border-radius: 8px !important;
			padding: 0.6rem 1rem !important;
			font-weight: 600 !important;
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
			border: 1px solid #e2e8f0 !important;
			background: #ffffff !important;
			color: #1a202c !important;
			transition: all 0.2s ease !important;
			font-size: 0.95rem !important;
			box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
		}
		
		.btn-social:hover {
			background: #f8fafc !important;
			border-color: #cbd5e1 !important;
			color: #28a745 !important; /* Green on hover to match site */
			transform: translateY(-1px);
			box-shadow: 0 4px 6px rgba(0,0,0,0.05) !important;
		}
		
		.btn-social svg {
			transition: transform 0.2s ease;
		}
		
		.btn-social:hover svg {
			transform: scale(1.1);
		}

		/* Uniform White Style for All Social Buttons */
		.btn-facebook, 
		.btn-google, 
		.btn-google-plus,
		.btn-apple,
		a[class*="btn-google"],
		a[class*="btn-facebook"],
		a[class*="btn-apple"] {
			background: #ffffff !important;
			color: #1a202c !important;
			border: 1px solid #e2e8f0 !important;
			background-image: none !important;
			box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
		}

		.btn-facebook { border-color: #1877F2 !important; }
		.btn-google { border-color: #e2e8f0 !important; }
		.btn-apple { border-color: #000000 !important; }

		.btn-social:hover {
			background: #ffffff !important; /* Keep white on hover */
			color: #28a745 !important;    /* Just change text to green on hover */
			transform: translateY(-1px);
			box-shadow: 0 4px 12px rgba(0,0,0,0.05) !important;
		}
		
		.text-muted {
			color: var(--auth-text-muted) !important;
		}
		
		a {
			color: var(--auth-primary);
			text-decoration: none;
			font-weight: 600;
		}
		
		a:hover {
			color: #1e7e34;
			text-decoration: underline;
		}
		
		hr {
			opacity: 0.1 !important;
		}
		
		.fw-bold {
			color: #1a202c;
		}
		
		.modern-auth-card h2 {
			font-size: 1.75rem;
			color: #1a202c;
		}

		/* Fix for intl-tel-input overlap */
		.iti {
			width: 100% !important;
			display: block !important;
		}
		
		.iti input {
			padding-left: 95px !important;
		}
		
		@media (max-width: 767.98px) {
			.modern-auth-card {
				padding: 2rem 1.5rem;
				border-radius: 0;
				border: none;
				box-shadow: none;
				background: transparent;
			}
			.auth-login-register {
				background: #ffffff;
			}
		}
	</style>

	@if (!empty($helpers))
		@foreach($helpers as $helper)
			@stack($helper . '_styles')
		@endforeach
	@endif
	
	<style>
		/* HIGH SPECIFICITY OVERRIDE */
		html body #main-wrapper .btn-social,
		html body #main-wrapper .btn-facebook,
		html body #main-wrapper .btn-google,
		html body #main-wrapper .btn-google-plus,
		html body #main-wrapper .btn-apple,
		html body #main-wrapper a[class*="btn-google"],
		html body #main-wrapper a[class*="btn-facebook"],
		html body #main-wrapper a[class*="btn-apple"] {
			background: #ffffff !important;
			background-color: #ffffff !important;
			color: #1a202c !important;
			border-width: 1px !important;
			border-style: solid !important;
			background-image: none !important;
			box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important;
		}

		html body #main-wrapper .btn-facebook { border-color: #1877F2 !important; }
		html body #main-wrapper .btn-google,
		html body #main-wrapper a[class*="btn-google"] { 
			border-color: #ea4335 !important; /* Red border for Google */
		}
		html body #main-wrapper .btn-apple { border-color: #000000 !important; }

		html body #main-wrapper .btn-social:hover {
			background: #f8fafc !important;
			transform: translateY(-1px);
			box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
		}

		/* Specific Hover Text Colors */
		html body #main-wrapper .btn-facebook:hover,
		html body #main-wrapper a[class*="btn-facebook"]:hover {
			color: #1877F2 !important; /* Blue text on hover */
		}

		html body #main-wrapper .btn-google:hover,
		html body #main-wrapper .btn-google-plus:hover,
		html body #main-wrapper a[class*="btn-google"]:hover {
			color: #ea4335 !important; /* Red text on hover */
		}
	</style>

	@include('front.common.js.document')
	
	@if (!empty($helpers))
		@foreach($helpers as $helper)
			@stack($helper . '_head_scripts')
		@endforeach
	@endif
</head>
<body>

{{-- Preloader --}}
{{--
<div class="preloader">
	<div class="lds-ellipsis">
		<div></div>
		<div></div>
		<div></div>
		<div></div>
	</div>
</div>
--}}

<div id="main-wrapper" class="auth-login-register">
	<div class="container-fluid px-0">
		<div class="row g-0 min-vh-100">
			
			{{-- Welcome Text --}}
			<div class="col-md-6 d-none d-md-block">
				<div class="hero-wrap d-flex align-items-start h-100">
					<div class="hero-mask opacity-8 bg-primary"></div>
					<div class="hero-bg hero-bg-scroll" style="{!! $heroBgStyle !!}"></div>
					<div class="hero-content w-100 min-vh-100 d-flex flex-column">
						<div class="row g-0">
							<div class="col-11 col-sm-10 col-md-10 col-lg-9 mx-auto">
								<div class="logo mt-5 mb-5 mb-md-0">
									<a class="d-flex" href="{{ url('/') }}" title="{!! $logoLabel !!}">
										<img src="{{ $logoUrl }}"
										     alt="{{ $logoAlt }}"
										     data-bs-placement="bottom"
										     data-bs-toggle="tooltip"
										     title="{!! $logoLabel !!}"
										     style="{!! $logoCssSize !!}"
										>
									</a>
								</div>
							</div>
						</div>
						<div class="row g-0 my-auto">
							<div class="col-11 col-sm-10 col-md-10 col-lg-9 mx-auto">
								@php
									$defaultCoverTitle = trans('auth.default_cover_title', ['appName' => config('app.name')]);
									$defaultCoverDescription = trans('auth.default_cover_description');
								@endphp
								<h1 class="text-11 text-white mb-4 fw-bold">
									{!! $coverTitle ?? $defaultCoverTitle !!}
								</h1>
								<p class="text-4 text-white lh-base mb-5 opacity-75">
									{!! $coverDescription ?? $defaultCoverDescription !!}
								</p>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			{{-- Login Form --}}
			<div class="col-md-6 d-flex align-items-center justify-content-center">
				<div class="container my-auto py-5">
					<div class="row g-0">
						<div class="col-11 col-sm-10 col-md-11 col-lg-10 col-xl-9 col-xxl-8 mx-auto modern-auth-card">
						
						@php
							$hasNotifications = (
								(isset($errors) && $errors->any())
								|| session()->has('flash_messages')
								|| session()->has('resendEmailVerificationData')
								|| session()->has('resendPhoneVerificationData')
								|| session()->has('status')
								|| session()->has('email')
								|| session()->has('phone')
								|| session()->has('login')
								|| session()->has('code')
							);
						@endphp
						
						@if (isset($errors) && $errors->any())
							<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-11 col-xxl-10 mx-auto">
								<div class="alert alert-danger">
									@if (request()->segment(2) == 'register')
										<h5 class="fw-bold text-danger-emphasis mb-3">
											{{ trans('auth.validation_errors_title') }}
										</h5>
									@endif
									<ul class="mb-0 list-unstyled">
										@foreach ($errors->all() as $error)
											<li class="lh-lg"><i class="bi bi-check-lg me-1"></i>{!! $error !!}</li>
										@endforeach
									</ul>
								</div>
							</div>
						@endif
						
						@include('helpers.flash.default')
						
						@yield('notifications')
						
						@if ($hasNotifications)
							<div class="col-12 mx-auto mb-4">&nbsp;</div>
						@endif
						
						@yield('content')
						
						@include('auth.layouts.partials.select-language')
						</div>
					</div>
				</div>
			</div>
		
		</div>
	</div>
</div>

@section('modal')
@show
@include('front.layouts.partials.modal.countries', ['modalSize' => 'modal-xl'])

@include('front.common.js.init')

<script>
	var countryCode = '{{ config('country.code', 0)  }}';
	
	{{-- Theme Preference (light/dark/system) --}}
	var isSettingsAppDarkModeEnabled = {{ isSettingsAppDarkModeEnabled() ? 'true' : 'false' }};
	var isSettingsAppSystemThemeEnabled = {{ isSettingsAppSystemThemeEnabled() ? 'true' : 'false' }};
	var userThemePreference = {!! !empty($userThemePreference) ? "'$userThemePreference'" : 'null' !!};
	var showIconOnly = {{ $showIconOnly ? 'true' : 'false' }};
	
	{{-- The app's default auth field --}}
	var defaultAuthField = '{{ old('auth_field', getAuthField()) }}';
	var phoneCountry = '{{ config('country.code') }}';
</script>

@yield('before_scripts')

{{-- Toggle Password Visibility --}}
@include('auth.layouts.js.translations')

{{-- App JS files (Handled by Mix) --}}
<script src="{{ url(mix('dist/auth/scripts.js')) }}"></script>

@yield('after_scripts')
@stack('after_scripts_stack')

@if (!empty($helpers))
	@foreach($helpers as $helper)
		@stack($helper . '_scripts')
	@endforeach
@endif
</body>
</html>
