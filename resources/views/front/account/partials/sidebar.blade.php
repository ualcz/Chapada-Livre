@php
	use App\Helpers\Common\Num;
	use Illuminate\Support\Collection;
	
	$accountMenu ??= collect();
	$accountMenu = ($accountMenu instanceof Collection) ? $accountMenu : collect();
	
	// Links CSS Class
	$linkClass = linkClass('body-emphasis');
@endphp

<style>
    .account-sidebar {
        border: none !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        border-radius: 1.25rem !important;
        overflow: hidden;
        background: #fff !important;
    }
    
    .account-sidebar h5 {
        color: #1a1a1a;
        font-size: 1.1rem;
        padding-bottom: 5px;
        margin-bottom: 1.25rem;
        letter-spacing: -0.01em;
    }
    
    .account-sidebar .list-group {
        border-radius: 0;
        gap: 6px;
    }
    
    .account-sidebar .list-group-item {
        border: none !important;
        border-radius: 0.75rem !important;
        background: transparent !important;
        padding: 0.75rem 1rem;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        margin-bottom: 2px;
    }
    
    .account-sidebar .list-group-item:hover {
        background: rgba(var(--bs-primary-rgb), 0.05) !important;
        transform: translateX(4px);
    }
    
    .account-sidebar .list-group-item.active {
        background: var(--bs-primary) !important;
        box-shadow: 0 4px 15px rgba(var(--bs-primary-rgb), 0.25);
    }
    
    .account-sidebar .list-group-item.active a {
        color: #fff !important;
        font-weight: 600;
    }
    
    .account-sidebar .list-group-item a {
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 0.95rem;
        color: #4a4a4a !important;
    }
    
    .account-sidebar .list-group-item.active .badge {
        background: rgba(255, 255, 255, 0.2) !important;
        color: #fff !important;
    }
    
    .account-sidebar .badge {
        font-weight: 600;
        padding: 0.4em 0.8em;
    }
    
    .account-sidebar .fa-angle-down {
        font-size: 0.8rem;
        transition: transform 0.3s ease;
    }
    
    [aria-expanded="true"] .fa-angle-down {
        transform: rotate(180deg);
    }

    /* Floating Button for Mobile (Chevron Tab) */
    .mobile-menu-trigger {
        position: fixed;
        top: 50%;
        left: 0;
        transform: translateY(-50%);
        z-index: 1050;
        width: 32px;
        height: 60px;
        border-radius: 0 12px 12px 0;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 4px 0 15px rgba(var(--bs-primary-rgb), 0.2);
        padding: 0;
        background: var(--bs-primary) !important;
        border: none !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        opacity: 0.8;
    }
    
    .mobile-menu-trigger:hover {
        width: 40px;
        opacity: 1;
    }
    
    .mobile-menu-trigger i {
        color: #fff;
        font-size: 1.2rem;
        transition: transform 0.3s ease;
    }

    .offcanvas-account {
        border-top-right-radius: 1.5rem;
        border-bottom-right-radius: 1.5rem;
        width: 280px !important;
    }

    [data-bs-theme="dark"] .account-sidebar {
        background: #1e1e1e !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }
    [data-bs-theme="dark"] .account-sidebar .list-group-item a {
        color: #e0e0e0 !important;
    }
    [data-bs-theme="dark"] .account-sidebar .list-group-item:hover {
        background: rgba(var(--bs-primary-rgb), 0.15) !important;
    }
    [data-bs-theme="dark"] .account-sidebar h5 {
        color: #fff;
    }
</style>

{{-- Desktop Sidebar --}}
<aside class="d-none d-md-block">
	<div class="container account-sidebar p-4 p-lg-4 p-md-3 mb-4 mb-md-0 vstack gap-4">
		@include('front.account.partials.sidebar-content')
	</div>
</aside>

{{-- Mobile Trigger & Offcanvas --}}
<div class="d-md-none">
    <button id="accountMenuTrigger" class="mobile-menu-trigger" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAccountMenu" aria-controls="offcanvasAccountMenu">
        <i class="fa-solid fa-chevron-right"></i>
    </button>

    <div class="offcanvas offcanvas-start offcanvas-account" tabindex="-1" id="offcanvasAccountMenu" aria-labelledby="offcanvasAccountMenuLabel">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title fw-bold" id="offcanvasAccountMenuLabel">
                <i class="bi bi-person-circle text-primary me-2"></i> {{ trans('global.Menu') }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-3">
            <div class="account-sidebar shadow-none bg-transparent">
                @include('front.account.partials.sidebar-content')
            </div>
        </div>
    </div>
</div>

<script>
    onDocumentReady((event) => {
        const offcanvasElement = document.getElementById('offcanvasAccountMenu');
        const triggerButton = document.getElementById('accountMenuTrigger');
        
        if (offcanvasElement && triggerButton) {
            offcanvasElement.addEventListener('show.bs.offcanvas', function () {
                triggerButton.classList.add('d-none');
            });
            
            offcanvasElement.addEventListener('hidden.bs.offcanvas', function () {
                triggerButton.classList.remove('d-none');
            });
        }
    });
</script>
