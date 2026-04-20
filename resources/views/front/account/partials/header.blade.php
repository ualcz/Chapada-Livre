@php
	$headerTitle ??= trans('global.overview');
	$userName = $authUser->name ?? '--';
	$userPhotoUrl = $authUser->photo_url ?? config('larapen.media.avatar');
	$photoSize = '90px';
	$photoStyle = "max-width: $photoSize; max-height: $photoSize; width: $photoSize; height: $photoSize;";
@endphp

<style>
    .account-header {
        background: linear-gradient(135deg, var(--bs-primary) 0%, #0056b3 100%);
        border: none !important;
        border-radius: 1.25rem !important;
        position: relative;
        overflow: hidden;
        color: #fff !important;
        box-shadow: 0 10px 30px rgba(var(--bs-primary-rgb), 0.15);
    }
    
    .account-header::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -10%;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
        z-index: 0;
    }
    
    .account-header .row {
        position: relative;
        z-index: 1;
    }
    
    .account-header h3 {
        font-size: 1.5rem;
        letter-spacing: -0.02em;
        margin-bottom: 0.25rem !important;
    }
    
    .account-header .breadcrumb {
        margin-bottom: 0;
    }
    
    .account-header .breadcrumb-item, 
    .account-header .breadcrumb-item a,
    .account-header .breadcrumb-item.active {
        color: rgba(255, 255, 255, 0.8) !important;
        font-size: 0.85rem;
    }
    
    .account-header .breadcrumb-item + .breadcrumb-item::before {
        color: rgba(255, 255, 255, 0.5);
    }
    
    .user-info-header {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        padding: 0.5rem 0.75rem;
        border-radius: 50rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    #userImgHeader {
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        transition: transform 0.3s ease;
        object-fit: cover;
    }
    
    .user-info-header:hover #userImgHeader {
        transform: scale(1.05);
    }

    [data-bs-theme="dark"] .account-header {
        background: linear-gradient(135deg, #2c2c2c 0%, #1a1a1a 100%);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }
</style>

<div class="container account-header p-4 mb-4">
	<div class="row align-items-center gy-3">
		<div class="col-lg-7 col-md-12">
			<h3 class="p-0 fw-bold">
				{!! $headerTitle !!}
			</h3>
			<div class="d-none d-md-block">{!! Breadcrumb::render() !!}</div>
		</div>
		<div class="col-lg-5 col-md-12 d-flex justify-content-lg-end">
			<div class="user-info-header d-flex align-items-center gap-3">
				<div class="text-end">
					<h6 class="p-0 mb-0 fw-bold text-white fs-6">
						{{ $userName }}
					</h6>
					<small class="opacity-75" style="font-size: 0.75rem;">{{ trans('global.my_account') }}</small>
				</div>
				<img id="userImgHeader" class="rounded-circle border border-2 border-white" src="{{ $userPhotoUrl }}" alt="user" style="{!! $photoStyle !!}">
			</div>
		</div>
	</div>
</div>
