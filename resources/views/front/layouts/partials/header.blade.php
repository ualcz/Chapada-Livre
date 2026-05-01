@php
	$countries ??= collect();
	
	// Search parameters
	$queryString = request()->getQueryString();
	$queryString = !empty($queryString) ? '?' . $queryString : '';
	
	$showCountryFlagNextLogo = (config('settings.localization.show_country_flag') == 'in_next_logo');
	
	// Check if the Multi-Countries selection is enabled
	$multiCountryIsEnabled = false;
	$multiCountryLabel = '';
	if ($showCountryFlagNextLogo) {
		if (!empty(config('country.code'))) {
			if ($countries->count() > 1) {
				$multiCountryIsEnabled = true;
				$multiCountryLabel = 'title="' . trans('global.select_country') . '"';
			}
		}
	}
	
	// Country
	$countryName = config('country.name');
	$countryFlag24Url = config('country.flag24_url');
	$countryFlag32Url = config('country.flag32_url');
	
	// Logo
	$logoFactoryUrl = config('larapen.media.logo-factory');
	$logoDarkUrl = config('settings.app.logo_dark_url', $logoFactoryUrl);
	$logoLightUrl = config('settings.app.logo_light_url', $logoFactoryUrl);
	$logoAlt = strtolower(config('settings.app.name'));
	$logoWidth = (int)config('settings.upload.img_resize_logo_width', 454);
	$logoHeight = (int)config('settings.upload.img_resize_logo_height', 80);
	$logoStyle = "max-width:{$logoWidth}px !important; max-height:{$logoHeight}px !important; width:auto !important;";
	
	// Logo Label
	$logoLabel = '';
	if ($multiCountryIsEnabled) {
		$logoLabel = config('settings.app.name') . (!empty($countryName) ? ' ' . $countryName : '');
	}
	
	// User Menu
	$authUser = auth()->check() ? auth()->user() : null;
	$userMenu ??= collect();
	
	// Theme Preference (light/dark/system)
	$showIconOnly ??= false;
	
	// Fallback Navbar Parameters
	$fallbackHeight = 80;
	$fallbackNavbarClass = 'fixed-top navbar-sticky bg-body-tertiary border-bottom';
	$fallbackContainerClass = 'container';
	$textShadowClass = 'text-shadow';
	
	$defaultHeight = forceToInt(config('settings.header.default_height'), $fallbackHeight);
	$defaultStyle = "min-height: {$defaultHeight}px";
@endphp
@php
	// Navbar Parameters
	$isDefaultHeaderAnimationEnabled = (config('settings.header.default_animation') == '1');
	$isFixedHeaderEnabled = (config('settings.header.fixed_top') == '1');
	$navbarFixedHeightOffset = config('settings.header.fixed_height_offset');
	$navbarFixedHeightOffset = (!empty($navbarFixedHeightOffset) && is_numeric($navbarFixedHeightOffset)) ? $navbarFixedHeightOffset : 'null';
	
	$isDefaultHeaderDarkThemeEnabled = (config('settings.header.default_dark') == '1');
	$defaultCssClasses = config('settings.header.default_css_classes');
	$defaultCssClasses = !empty($defaultCssClasses) ? $defaultCssClasses : $fallbackNavbarClass;
	$defaultContainerCssClasses = config('settings.header.default_container_css_classes');
	$defaultContainerCssClasses = !empty($defaultContainerCssClasses) ? $defaultContainerCssClasses : $fallbackContainerClass;
	$defaultBgColor = config('settings.header.default_background_color');
	$defaultBorderColor = config('settings.header.default_border_color');
	$defaultLinkColorClass = config('settings.header.default_link_color_class');
	$defaultLinkColorClass = !empty($defaultLinkColorClass) ? " $defaultLinkColorClass" : '';
	$defaultLinkColor = config('settings.header.default_link_color');
	$defaultLinkHoverColor = config('settings.header.default_link_hover_color');
	$defaultTextColorClass = config('settings.header.default_text_color_class');
	$defaultTextColorClass = !empty($defaultTextColorClass) ? " $defaultTextColorClass" : '';
	$defaultTextColor = config('settings.header.default_text_color');
	$isDefaultHeaderItemShadowEnabled = (config('settings.header.default_item_shadow') == '1');
	
	$isFixedHeaderDarkThemeEnabled = (config('settings.header.fixed_dark') == '1');
	$fixedHeight = forceToInt(config('settings.header.fixed_height'), $defaultHeight);
	$fixedCssClasses = config('settings.header.fixed_css_classes');
	$fixedContainerCssClasses = config('settings.header.fixed_container_css_classes');
	$fixedContainerCssClasses = !empty($fixedContainerCssClasses) ? $fixedContainerCssClasses : $fallbackContainerClass;
	$fixedBgColor = config('settings.header.fixed_background_color');
	$fixedBorderColor = config('settings.header.fixed_border_color');
	$fixedLinkClass = config('settings.header.fixed_link_color_class');
	$fixedLinkColor = config('settings.header.fixed_link_color');
	$fixedLinkHoverColor = config('settings.header.fixed_link_hover_color');
	$fixedTextColorClass = config('settings.header.fixed_text_color_class');
	$fixedTextColor = config('settings.header.fixed_text_color');
	$isFixedHeaderItemShadowEnabled = (config('settings.header.static_item_shadow') == '1');
	
	$defaultExpandedBgColorClass = config('settings.header.default_expanded_background_color_class');
	$defaultExpandedLinkColorClass = config('settings.header.default_expanded_link_color_class');
	$defaultExpandedTextColorClass = config('settings.header.default_expanded_text_color_class');
	
	// Other Navbar Vars
	$defaultHeaderThemeAttr = $isDefaultHeaderDarkThemeEnabled ? ' data-bs-theme="dark"' : '';
	$defaultHeaderItemShadowClass = $isDefaultHeaderItemShadowEnabled ? " {$textShadowClass}" : '';
@endphp
@pushonce('before_scripts_stack')
	<script>
		if (typeof window.headerOptions === 'undefined') {
			window.headerOptions = {};
		}
		window.headerOptions = {
			animationEnabled: {{ $isDefaultHeaderAnimationEnabled ? 'true' : 'false' }},
			navbarHeightOffset: {{ $navbarFixedHeightOffset }},
			default: {
				darkThemeEnabled: {{ $isDefaultHeaderDarkThemeEnabled ? 'true' : 'false' }},
				height: {{ $defaultHeight }},
				cssClasses: '{{ $defaultCssClasses }}',
				containerCssClasses: '{{ $defaultContainerCssClasses }}',
				bgColor: '{{ $defaultBgColor }}',
				borderColor: '{{ $defaultBorderColor }}',
				linkColorClass: '{{ $defaultLinkColorClass }}',
				linkColor: '{{ $defaultLinkColor }}',
				linkHoverColor: '{{ $defaultLinkHoverColor }}',
				textColorClass: '{{ $defaultTextColorClass }}',
				textColor: '{{ $defaultTextColor }}',
				itemShadowClass: '{{ $isDefaultHeaderItemShadowEnabled ? $textShadowClass : '' }}',
			},
			fixed: {
				enabled: {{ $isFixedHeaderEnabled ? 'true' : 'false' }},
				darkThemeEnabled: {{ $isFixedHeaderDarkThemeEnabled ? 'true' : 'false' }},
				height: {{ $fixedHeight }},
				cssClasses: '{{ $fixedCssClasses }}',
				containerCssClasses: '{{ $fixedContainerCssClasses }}',
				bgColor: '{{ $fixedBgColor }}',
				borderColor: '{{ $fixedBorderColor }}',
				linkColorClass: '{{ $fixedLinkClass }}',
				linkColor: '{{ $fixedLinkColor }}',
				linkHoverColor: '{{ $fixedLinkHoverColor }}',
				textColorClass: '{{ $fixedTextColorClass }}',
				textColor: '{{ $fixedTextColor }}',
				itemShadowClass: '{{ $isFixedHeaderItemShadowEnabled ? $textShadowClass : '' }}',
			},
			expandedBgColorClass: '{{ $defaultExpandedBgColorClass }}',
			expandedLinkColorClass: '{{ $defaultExpandedLinkColorClass }}',
			expandedTextColorClass: '{{ $defaultExpandedTextColorClass }}',
		};
	</script>
@endpushonce
<header{!! $defaultHeaderThemeAttr !!}>
	<nav class="navbar {{ $defaultCssClasses }} navbar-expand-xl" role="navigation" id="mainNavbar" style="{{ $defaultStyle }}">
		<div class="{{ $defaultContainerCssClasses }}" id="mainNavbarContainer">
			
			{{-- Logo --}}
			<a href="{{ url('/') }}" class="navbar-brand logo logo-title">
				<img src="{{ $logoDarkUrl }}"
				     alt="{{ $logoAlt }}"
				     class="main-logo dark-logo"
				     data-bs-placement="bottom"
				     data-bs-toggle="tooltip"
				     title="{!! $logoLabel !!}"
				     style="{!! $logoStyle !!}"
				/>
				<img src="{{ $logoLightUrl }}"
				     alt="{{ $logoAlt }}"
				     class="main-logo light-logo"
				     data-bs-placement="bottom"
				     data-bs-toggle="tooltip"
				     title="{!! $logoLabel !!}"
				     style="{!! $logoStyle !!}"
				/>
			</a>
			
			{{-- Toggle Nav (Desktop) - Bootstrap nativo, sem CSS customizado --}}
			<button class="navbar-toggler float-end border-0 d-xl-none"
			        type="button"
			        data-bs-toggle="offcanvas"
			        data-bs-target="#mobileNavDrawer"
			        aria-controls="mobileNavDrawer"
			        aria-label="Toggle navigation"
			>
				<span class="navbar-toggler-icon"></span>
			</button>
			
			{{-- Desktop Nav: Bootstrap 100% puro, sem nenhuma intervenção CSS --}}
			<div class="collapse navbar-collapse d-none d-xl-flex" id="navbarNav">
				<ul class="navbar-nav me-md-auto">
					{{-- Country Flag --}}
					@if ($showCountryFlagNextLogo)
						@if (!empty($countryFlag32Url))
							<li class="nav-item flag-menu country-flag"
							    data-bs-toggle="tooltip"
							    data-bs-placement="{{ (config('lang.direction') == 'rtl') ? 'bottom' : 'right' }}" {!! $multiCountryLabel !!}
							>
								@if ($multiCountryIsEnabled)
									<a class="nav-link p-0 {{ $defaultLinkColorClass }}" data-bs-toggle="modal" data-bs-target="#selectCountry" style="cursor: pointer;">
										<img class="flag-icon mt-1" src="{{ $countryFlag32Url }}" alt="{{ $countryName }}">
										<i class="bi bi-chevron-down float-end mt-1 mx-2"></i>
									</a>
								@else
									<a class="nav-link p-0" style="cursor: default;">
										<img class="flag-icon" src="{{ $countryFlag32Url }}" alt="{{ $countryName }}">
									</a>
								@endif
							</li>
						@endif
					@endif
				</ul>
				
				<ul class="navbar-nav ms-auto gap-xl-2">
					@include('front.layouts.partials.navs.menus.header')
					
					{{-- Currency Exchange Dropdown --}}
					@if (config('addons.currencyexchange.installed'))
						@include('currencyexchange::select-currency')
					@endif
					
					{{-- Dark/Light Mode Dropdown --}}
					@if (isSettingsAppDarkModeEnabled())
						@include('front.layouts.partials.navs.themes', [
							'dropdownTag'    => 'li',
							'dropdownClass'  => 'nav-item',
							'buttonClass'    => 'nav-link',
							'menuAlignment'  => 'dropdown-menu-end',
							'showIconOnly'   => $showIconOnly,
							'linkColorClass' => $defaultLinkColorClass,
						])
					@endif
					
					{{-- Languages Dropdown/Modal Link --}}
					@include('front.layouts.partials.navs.languages')
					
				</ul>
			</div>
		
		</div>
	</nav>
</header>

{{-- Mobile Nav Drawer (Offcanvas) - Separado do desktop, sem impactar o layout --}}
{{-- Mobile Nav Drawer (Offcanvas) - Estilo unificado com o menu de conta --}}
<div class="offcanvas offcanvas-start d-xl-none" tabindex="-1" id="mobileNavDrawer" aria-labelledby="mobileNavDrawerLabel"
     style="width: 280px; border-top-right-radius: 1.5rem; border-bottom-right-radius: 1.5rem;">
    <div class="offcanvas-header border-bottom py-3 px-4">
        <h5 class="offcanvas-title fw-bold" id="mobileNavDrawerLabel">
            <i class="bi bi-person-circle text-primary me-2"></i> {{ auth()->check() ? auth()->user()->name : trans('global.Menu') }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-3">
		<ul class="navbar-nav flex-column gap-1 mb-2">
			{{-- Country Flag --}}
			@if ($showCountryFlagNextLogo && !empty($countryFlag32Url))
				<li class="nav-item">
					@if ($multiCountryIsEnabled)
						<a class="nav-link ps-0" data-bs-toggle="modal" data-bs-target="#selectCountry" style="cursor: pointer;">
							<img class="flag-icon me-2" src="{{ $countryFlag32Url }}" alt="{{ $countryName }}"> {{ $countryName }}
						</a>
					@else
						<span class="nav-link ps-0">
							<img class="flag-icon me-2" src="{{ $countryFlag32Url }}" alt="{{ $countryName }}"> {{ $countryName }}
						</span>
					@endif
				</li>
			@endif
		</ul>
		
		<div class="mobile-nav-content">
			<ul class="navbar-nav flex-column gap-1">
				@include('front.layouts.partials.navs.menus.header', ['isMobileMenu' => true])
				
				@if (config('addons.currencyexchange.installed'))
					@include('currencyexchange::select-currency')
				@endif
				
				@if (isSettingsAppDarkModeEnabled())
					@include('front.layouts.partials.navs.themes', [
						'dropdownTag'    => 'li',
						'dropdownClass'  => 'nav-item',
						'buttonClass'    => 'nav-link',
						'menuAlignment'  => 'dropdown-menu-end',
						'showIconOnly'   => false,
						'linkColorClass' => '',
					])
				@endif
				
				@include('front.layouts.partials.navs.languages')
			</ul>
		</div>
		
		<style>
			/* Estilo Premium para a Gaveta Mobile (Igual ao Menu de Conta) */
			#mobileNavDrawer {
				box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
			}
			
			#mobileNavDrawer .nav-item {
				margin-bottom: 2px;
				list-style: none;
			}
			
			#mobileNavDrawer .nav-link, 
			#mobileNavDrawer .nav-item > a {
				display: flex !important;
				align-items: center !important;
				gap: 12px !important;
				padding: 0.75rem 1rem !important;
				border-radius: 0.85rem !important;
				font-weight: 500 !important;
				color: #444 !important;
				transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
				text-decoration: none !important;
				border: none !important;
			}
			
			#mobileNavDrawer .nav-link:hover,
			#mobileNavDrawer .nav-item > a:hover {
				background: rgba(var(--bs-primary-rgb), 0.05) !important;
				transform: translateX(4px);
				color: var(--bs-primary) !important;
			}
			
			#mobileNavDrawer .nav-link.active,
			#mobileNavDrawer .nav-item > a.active {
				background: var(--bs-primary) !important;
				color: #fff !important;
				box-shadow: 0 4px 15px rgba(var(--bs-primary-rgb), 0.25);
			}
			
			#mobileNavDrawer .nav-link i,
			#mobileNavDrawer .nav-item > a i {
				font-size: 1.15rem;
				width: 24px;
				text-align: center;
				opacity: 0.8;
			}
			
			/* Títulos e Divisores */
			#mobileNavDrawer hr {
				margin: 1rem 0;
				opacity: 0.1;
			}
			
			#mobileNavDrawer .menu-type-title {
				font-size: 0.75rem !important;
				text-uppercase: uppercase !important;
				font-weight: 700 !important;
				color: #999 !important;
				letter-spacing: 0.5px !important;
				padding: 1.25rem 1rem 0.5rem !important;
				pointer-events: none;
			}
			
			/* Submenus (Dropdowns) */
			#mobileNavDrawer .dropdown-menu {
				display: block !important;
				position: static !important;
				box-shadow: none !important;
				border: none !important;
				background-color: transparent !important;
				padding-left: 1.5rem !important;
				padding-top: 0 !important;
				margin-top: -2px !important;
			}
			
			#mobileNavDrawer .dropdown-toggle::after {
				display: none !important;
			}
			
			#mobileNavDrawer .dropdown-item {
				padding: 0.6rem 1rem !important;
				font-size: 0.92rem !important;
				border-radius: 0.75rem !important;
				color: #555 !important;
			}
			
			#mobileNavDrawer .dropdown-item:hover {
				background: rgba(var(--bs-primary-rgb), 0.04) !important;
				color: var(--bs-primary) !important;
			}
			
			/* Botões de Destaque (ex: Criar Anúncio) */
			#mobileNavDrawer .btn-warning,
			#mobileNavDrawer .menu-type-button a {
				justify-content: center !important;
				margin: 0.75rem 0 !important;
				padding: 0.85rem !important;
				font-weight: 700 !important;
				border-radius: 1rem !important;
				box-shadow: 0 4px 12px rgba(255, 193, 7, 0.2) !important;
				transform: none !important;
			}
			
			/* Dark Mode Support */
			[data-bs-theme="dark"] #mobileNavDrawer {
				background: #1a1a1a !important;
			}
			[data-bs-theme="dark"] #mobileNavDrawer .nav-link,
			[data-bs-theme="dark"] #mobileNavDrawer .nav-item > a {
				color: #ccc !important;
			}
			[data-bs-theme="dark"] #mobileNavDrawer .nav-link:hover,
			[data-bs-theme="dark"] #mobileNavDrawer .nav-item > a:hover {
				background: rgba(255, 255, 255, 0.05) !important;
			}
			[data-bs-theme="dark"] #mobileNavDrawer .dropdown-item {
				color: #bbb !important;
			}
		</style>
    </div>
</div>

