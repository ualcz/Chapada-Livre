@php
	use App\Services\Auth\App\Helpers\SocialLogin\SocialLoginButton;
	
	$socialLoginProviders = socialLogin()->providersForConnection(strict: true);
	$labelType = getSocialLoginButtonType();
	
	$defaultPosition = 'top';
	$position ??= $defaultPosition;
	$position = in_array($position, ['top', 'topWithTitle', 'bottom']) ? $position : $defaultPosition;
	
	$defaultPage = 'login';
	$page ??= $defaultPage;
	$page = in_array($page, ['login', 'register', 'modal']) ? $page : $defaultPage;
	
	$topSeparator = ($page == 'register')
		? trans('auth.register_with_title')
		: trans('auth.login_with_title');
	
	$bottomSeparator = ($page == 'register')
		? trans('auth.or_register_with')
		: trans('auth.or_login_with');
	$bottomSeparator = ($labelType == SocialLoginButton::LoginWithDefault->value)
		? trans('auth.or')
		: $bottomSeparator;
	
	$boxedCol = (!empty($boxedCol) && is_numeric($boxedCol)) ? $boxedCol : 12;
@endphp
@if (!empty($socialLoginProviders))
	@php
		$sGutter = 'gx-2 gy-2';
		if (isset($socialCol) && !empty($socialCol) && is_numeric($socialCol)) {
			if ($socialCol >= 10) {
				$sGutter = 'gx-2 gy-1';
			}
			$sCol = 'col-xl-6 col-lg-6 col-md-6';
			$sCol = str_replace('-6', '-' . $socialCol, $sCol);
		} else {
			$sCol = 'col-xl-6 col-lg-6 col-md-6';
		}
	@endphp
	
	@if ($position == 'bottom')
		<div class="d-flex align-items-center my-2">
			<hr class="flex-grow-1">
			<span class="mx-2 text-2 text-muted fw-300">{{ $bottomSeparator }}</span>
			<hr class="flex-grow-1">
		</div>
	@endif
	@if ($position == 'topWithTitle')
		<div class="d-flex align-items-center my-2">
			<hr class="flex-grow-1">
			<span class="mx-2 text-2 text-muted fw-300">{{ $topSeparator }}</span>
			<hr class="flex-grow-1">
		</div>
	@endif
	@if ($page != 'modal' && !in_array($position, ['bottom', 'topWithTitle']))
		<div class="d-flex align-items-center my-3"></div>
	@endif
	
	<div class="row mb-1 social-media d-flex justify-content-center {{ $sGutter }}">
		@foreach($socialLoginProviders as $provider => $providerData)
			@php
				$iconClass = data_get($providerData, 'iconClass');
				$url = data_get($providerData, 'url');
				$label = data_get($providerData, 'label');
				$title = strip_tags($label);
				
				// Custom mapping for provider classes if needed
				$providerClass = 'btn-' . strtolower($provider);
			@endphp
			<div class="{{ $sCol }} col-sm-6 col-6 text-center mb-1">
				<a href="{{ $url }}" title="{!! $title !!}" class="btn btn-social {{ $providerClass }}">
					@if(strtolower($provider) == 'google')
						<svg width="18" height="18" viewBox="0 0 18 18" class="me-2" style="flex-shrink: 0;"><path d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.874 2.684-6.615z" fill="#4285F4"/><path d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z" fill="#34A853"/><path d="M3.964 10.705A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.705V4.963H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.037l3.007-2.332z" fill="#FBBC05"/><path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.963L3.964 7.295C4.672 5.168 6.656 3.58 9 3.58z" fill="#EA4335"/></svg>
					@elseif(strtolower($provider) == 'facebook')
						<svg width="18" height="18" viewBox="0 0 18 18" class="me-2" style="flex-shrink: 0;"><path d="M17.1,0H0.9C0.4,0,0,0.4,0,0.9v16.2C0,17.6,0.4,18,0.9,18h8.7v-7h-2.3V8.3h2.3V6.3c0-2.3,1.4-3.6,3.5-3.6 c1,0,1.9,0.1,2.1,0.1v2.5l-1.5,0c-1.1,0-1.3,0.5-1.3,1.3v1.7h2.8l-0.4,2.6h-2.4v7h4.7c0.5,0,0.9-0.4,0.9-0.9V0.9 C18,0.4,17.6,0,17.1,0z" fill="#1877F2"/></svg>
					@elseif(strtolower($provider) == 'apple')
						<svg width="18" height="18" viewBox="0 0 18 18" class="me-2" style="flex-shrink: 0;"><path d="M14.9,12.7c-0.8,1.2-1.7,2.4-3.1,2.4c-1.4,0-1.8-0.8-3.4-0.8c-1.6,0-2.1,0.8-3.4,0.8c-1.3,0-2.4-1.3-3.2-2.4 C0.2,10.2-0.5,6.5,0.7,4.3c0.6-1.1,1.7-1.8,2.9-1.8c1,0,1.9,0.7,2.5,0.7c0.6,0,1.7-0.8,2.8-0.7c0.5,0,1.8,0.2,2.7,1.5 c-0.1,0.1-1.6,0.9-1.6,2.8c0,2.2,1.9,3,1.9,3c0,0-0.3,1.1-1.1,2.2l0,0V12.7z M10.4,2c0-1.1-0.9-1.9-0.9-1.9S8.7,0.2,8.7,1.3 c0,1,0.9,1.8,0.9,1.8S10.4,3.1,10.4,2L10.4,2z" fill="#000000"/></svg>
					@else
						<i class="{{ $iconClass }} me-2"></i>
					@endif
					<span>{!! $label !!}</span>
				</a>
			</div>
		@endforeach
	</div>
	
	@if ($position == 'topWithTitle')
		<div class="d-flex align-items-center my-2">
			<hr class="flex-grow-1">
			<span class="mx-2 text-2 text-muted fw-300">{{ trans('auth.or') }}</span>
			<hr class="flex-grow-1">
		</div>
	@endif
	@if ($position == 'top')
		<div class="d-flex align-items-center my-2">
			<hr class="flex-grow-1">
			<span class="mx-2 text-2 text-muted fw-300">{{ trans('auth.or') }}</span>
			<hr class="flex-grow-1">
		</div>
	@endif
@endif
