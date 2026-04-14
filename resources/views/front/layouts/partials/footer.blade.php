@php
	$footerLinksAreEnabled = (config('settings.footer.hide_links') != '1');
	
	$socialLinksAreEnabled = (
		config('settings.social_link.facebook_page_url')
		|| config('settings.social_link.twitter_url')
		|| config('settings.social_link.tiktok_url')
		|| config('settings.social_link.linkedin_url')
		|| config('settings.social_link.pinterest_url')
		|| config('settings.social_link.instagram_url')
		|| config('settings.social_link.youtube_url')
		|| config('settings.social_link.vimeo_url')
		|| config('settings.social_link.vk_url')
		|| config('settings.social_link.tumblr_url')
		|| config('settings.social_link.flickr_url')
	);
	$appsLinksAreEnabled = (
		config('settings.footer.ios_app_url')
		|| config('settings.footer.android_app_url')
	);
	$socialAndAppsLinksAreEnabled = ($socialLinksAreEnabled || $appsLinksAreEnabled);
	
	$paymentLogosAreEnabled = (config('settings.footer.hide_payment_addons_logos') != '1');
	
	// Footer's theme CSS Class
	$isFooterDarkThemeEnabled = (config('settings.footer.dark') == '1');
	$footerColor = $isFooterDarkThemeEnabled ? ' bg-black border-light-subtle text-light text-opacity-75' : ' bg-white';
	$borderColor = $isFooterDarkThemeEnabled ? ' border-dark border-opacity-75' : ' border-light-subtle border-opacity-75';
	$linkClass = $isFooterDarkThemeEnabled ? linkClass('light') . ' link-opacity-75' : 'text-secondary text-decoration-none';
	$imgBgColor = ' bg-light-subtle';
	
	// Footer's spacing
	$isFooterHighSpacingEnabled = (config('settings.footer.high_spacing') == '1');
	$bsSize = '5';
	
	// Footer's full width
	$isFullWidthFooter = (config('settings.footer.full_width') == '1');
	$containerClass = $isFullWidthFooter ? 'container-fluid' : 'container';
	$containerPxClass = $isFullWidthFooter ? " px-lg-5 px-0 py-0" : ' p-0';
@endphp

<style>
	/* ---- Extra Premium Footer Style (Clean Version) ---- */
	footer {
		background: linear-gradient(180deg, #ffffff 0%, #f9fafb 100%) !important;
		border-top: 1px solid rgba(0,0,0,0.05);
		padding: 5rem 0 0 0 !important;
		color: #212529 !important;
	}
	footer h4 {
		font-family: 'Outfit', sans-serif;
		font-size: 0.85rem !important;
		font-weight: 700 !important;
		text-transform: uppercase;
		letter-spacing: 0.1rem;
		color: #111827 !important;
		margin-bottom: 1.5rem !important;
	}
	/* Links do Menu e Textos das Colunas */
	footer .col ul li a, 
	footer .col ul li span,
	footer .col p,
	footer .col div:not(.social-links-premium) {
		color: #374151 !important; /* Cinza grafite bem escuro e legível */
		font-size: 0.92rem;
		transition: all 0.3s ease;
		text-decoration: none !important;
	}
	footer .col ul li a:hover {
		color: var(--bs-primary) !important;
		transform: translateX(5px);
	}
	.footer-logo-desc {
		font-size: 0.9rem;
		line-height: 1.6;
		color: #4b5563 !important;
	}
	/* Estilo Premium para Redes Sociais */
	.social-links-premium {
		display: flex;
		gap: 0.75rem;
		margin-top: 1.5rem;
	}
	.social-links-premium a {
		width: 40px;
		height: 40px;
		background: white;
		border: 1px solid #eee;
		display: flex !important;
		align-items: center;
		justify-content: center;
		border-radius: 50%;
		color: #374151 !important;
		font-size: 1.1rem;
		transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
		box-shadow: 0 2px 8px rgba(0,0,0,0.04);
	}
	.social-links-premium a i {
		color: inherit !important;
	}
	.social-links-premium a:hover {
		background: var(--bs-primary) !important;
		color: white !important;
		border-color: var(--bs-primary) !important;
		transform: translateY(-4px);
		box-shadow: 0 8px 15px rgba(var(--bs-primary-rgb), 0.2);
	}
	.app-badge-footer img {
		filter: grayscale(0.2);
		transition: all 0.3s;
	}
	.app-badge-footer img:hover {
		filter: grayscale(0);
		transform: translateY(-2px);
	}
	.copyright-text {
		font-size: 0.85rem;
		color: #6b7280;
	}
	.luxury-divider {
		height: 1px;
		background: linear-gradient(90deg, transparent 0%, rgba(0,0,0,0.05) 50%, transparent 100%);
		margin: 1rem 0;
	}
	@media (max-width: 991px) {
		footer {
			padding-top: 3rem !important;
		}
		footer .col {
			text-align: center;
			margin-bottom: 2.5rem;
		}
		.social-links-premium {
			justify-content: center;
		}
	}
</style>
<footer>
	@php
		$rowColsLg = $socialAndAppsLinksAreEnabled ? 'row-cols-lg-4' : 'row-cols-lg-3';
		$rowColsMd = 'row-cols-md-3';
		
		$borderTopCopy = " border-top{$borderColor} pt-{$bsSize}";
		$mbCopy = " mb-{$bsSize}";
		if (!$footerLinksAreEnabled) {
			$borderTopCopy = '';
			$mbCopy = ' mb-5';
		}
	@endphp
	<div class="container-fluid pt-4 pb-0">
		<div class="{{ $containerClass . $containerPxClass }} mt-0">
			<div class="row row-cols-lg-5 row-cols-md-3 row-cols-sm-2 row-cols-1 g-4">
				{{-- Coluna Logo e Slogan --}}
				<div class="col col-lg-3">
					<div class="mb-4">
						<a href="{{ url('/') }}" class="text-decoration-none">
							@php
								$logoUrl = config('settings.app.logo_url') ? config('settings.app.logo_url') : url('images/logo.png');
							@endphp
							<img src="{{ $logoUrl }}" style="height: 52px; width: auto;" alt="{{ config('settings.app.name') }}">
						</a>
						<div class="footer-logo-desc mt-4 pe-lg-5">
							{{ trans('global.footer_slogan') }}
						</div>
					</div>
					@if ($socialLinksAreEnabled)
						<div class="social-links-premium d-flex flex-wrap gap-3 mt-4">
							@if (config('settings.social_link.facebook_page_url'))
								<a href="{{ config('settings.social_link.facebook_page_url') }}" target="_blank" data-bs-toggle="tooltip" title="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
							@endif
							@if (config('settings.social_link.instagram_url'))
								<a href="{{ config('settings.social_link.instagram_url') }}" target="_blank" data-bs-toggle="tooltip" title="Instagram"><i class="fa-brands fa-instagram"></i></a>
							@endif
							@if (config('settings.social_link.twitter_url'))
								<a href="{{ config('settings.social_link.twitter_url') }}" target="_blank" data-bs-toggle="tooltip" title="X (Twitter)"><i class="fa-brands fa-x-twitter"></i></a>
							@endif
							@if (config('settings.social_link.linkedin_url'))
								<a href="{{ config('settings.social_link.linkedin_url') }}" target="_blank" data-bs-toggle="tooltip" title="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
							@endif
						</div>
					@endif
				</div>

				@if ($footerLinksAreEnabled)
					@include('front.layouts.partials.navs.menus.footer')
				@endif

				@if ($appsLinksAreEnabled)
					<div class="col col-lg-2">
						<h4>{{ trans('global.Mobile Apps') }}</h4>
						<div class="d-flex flex-column gap-3 app-badge-footer">
							@if (config('settings.footer.ios_app_url'))
								<a target="_blank" href="{{ config('settings.footer.ios_app_url') }}">
									<img src="{{ url('images/site/app-store-badge.svg') }}" style="height: 42px;" alt="App Store">
								</a>
							@endif
							@if (config('settings.footer.android_app_url'))
								<a target="_blank" href="{{ config('settings.footer.android_app_url') }}">
									<img src="{{ url('images/site/google-play-badge.svg') }}" style="height: 42px;" alt="Google Play">
								</a>
							@endif
						</div>
					</div>
				@endif
			</div>

			<div class="luxury-divider"></div>
		</div>
	</div>

	{{-- Copyright e Pagamentos --}}
	<div class="container-fluid py-3">
		<div class="{{ $containerClass }} px-lg-0">
			<div class="row g-3 justify-content-center">
				<div class="col-12 text-center">
					@php
						$siteName = config('settings.app.name');
					@endphp
					<div class="copyright-text">
						&copy; {{ date('Y') }} <span class="fw-bold text-dark">{!! $siteName !!}</span>. {{ trans('global.all_rights_reserved') }}.
					</div>
				</div>
				<div class="col-12 text-center">
					<div class="d-flex align-items-center justify-content-center gap-3 flex-wrap">
						@if ($paymentLogosAreEnabled && isset($paymentMethods) && $paymentMethods->count() > 0)
							@foreach($paymentMethods as $paymentMethod)
								@php
									$paymentMethodLogo = "cache/addons/{$paymentMethod->name}/images/payment.png";
								@endphp
								@if (file_exists(public_path($paymentMethodLogo)))
									<img src="{{ mixStaticFile(url($paymentMethodLogo)) }}"
										 alt="{{ $paymentMethod->display_name }}"
										 title="{{ $paymentMethod->display_name }}"
										 class="grayscale-hover opacity-50"
										 style="height: 18px; width: auto; filter: grayscale(1);"
									>
								@endif
							@endforeach
						@endif
					</div>
				</div>
			</div>
		</div>
	</div>
</footer>
