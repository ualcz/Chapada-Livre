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

@php
	$apiResult ??= [];
	$posts = (array)data_get($apiResult, 'data');
	$totalPosts = (int)data_get($apiResult, 'meta.total', 0);
	$pagePath ??= null;
	
	$countPromotionPackages ??= 0;
	$countPaymentMethods ??= 0;
	
	$pageData = [
		'list' => [
			'icon'     => 'fa-solid fa-bullhorn',
			'title'    => trans('global.my_listings'),
			'basePath' => urlGen()->getAccountBasePath() . '/posts/list',
		],
		'archived' => [
			'icon'     => 'bi bi-calendar-x',
			'title'    => trans('global.archived_listings'),
			'basePath' => urlGen()->getAccountBasePath() . '/posts/archived',
		],
		'pending-approval' => [
			'icon'     => 'bi bi-hourglass-split',
			'title'    => trans('global.pending_approval'),
			'basePath' => urlGen()->getAccountBasePath() . '/posts/pending-approval',
		],
		'saved-posts' => [
			'icon'     => 'bi bi-bookmarks',
			'title'    => trans('global.favourite_listings'),
			'basePath' => urlGen()->getAccountBasePath() . '/saved-posts',
		],
	];
	
	$pageIcon = $pageData[$pagePath]['icon'] ?? 'fa-solid fa-bullhorn';
	$pageTitle = $pageData[$pagePath]['title'] ?? trans('global.posts');
	$basePath = $pageData[$pagePath]['basePath'] ?? urlGen()->getAccountBasePath() . '/posts/undefined';
@endphp

@section('content')
	@include('front.common.spacer')
    
    <style>
        .posts-container {
            background: #fff;
            border: none !important;
            border-radius: 1.25rem !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            padding: 2.5rem !important;
        }

        @media (max-width: 768px) {
            .posts-container {
                padding: 1rem !important;
                border-radius: 1rem !important;
            }
        }

        [data-bs-theme="dark"] .posts-container {
            background: #1e1e1e !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .posts-container h3 {
            font-size: 1.5rem;
            letter-spacing: -0.02em;
            color: #1a1a1a;
        }

        [data-bs-theme="dark"] .posts-container h3 {
            color: #fff;
        }
        
        .table-action {
            background: rgba(var(--bs-primary-rgb), 0.03) !important;
            border: 1px solid rgba(var(--bs-primary-rgb), 0.1);
            border-radius: 1rem !important;
        }

        @media (max-width: 768px) {
            .table-action {
                flex-direction: column !important;
                align-items: stretch !important;
                padding: 1rem !important;
                gap: 0.75rem !important;
            }
            .table-search {
                width: 100% !important;
            }
            .dropup-mobile .dropdown-menu {
                top: auto !important;
                bottom: 100% !important;
                transform: none !important;
                margin-bottom: 5px !important;
            }
        }
        
        #addManageTable {
            border-collapse: separate;
            border-spacing: 0 12px;
            width: 100%;
        }
        
        #addManageTable thead th {
            border: none;
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: 700;
            color: #6c757d;
            letter-spacing: 0.05em;
            padding: 1rem;
        }

        @media (max-width: 768px) {
            #addManageTable thead {
                display: none;
            }
            #addManageTable, #addManageTable tbody, #addManageTable tr, #addManageTable td {
                display: block;
                width: 100% !important;
            }
            #addManageTable tr {
                background: #fff;
                margin-bottom: 1.5rem;
                border-radius: 1.25rem;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
                padding: 1rem;
                border: 1px solid rgba(0,0,0,0.05);
                position: relative;
            }
            #addManageTable td {
                padding: 0 !important;
                margin-bottom: 0.75rem;
            }
            #addManageTable td:last-child {
                margin-bottom: 0;
            }
            
            .add-img-td {
                float: left;
                width: 80px !important;
                margin-right: 15px;
                margin-bottom: 0 !important;
            }
            .items-details-td {
                overflow: hidden;
                margin-bottom: 10px !important;
            }
            .price-td-mobile {
                display: block !important;
                font-weight: 700;
                color: var(--bs-primary);
                font-size: 1.1rem;
                margin-top: 5px;
            }
            .action-td {
                border-top: 1px solid rgba(0,0,0,0.05);
                padding-top: 10px !important;
                display: flex !important;
                justify-content: flex-end;
            }
        }
        
        #addManageTable tbody tr {
            background: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
            border-radius: 1rem;
            transition: all 0.2s ease;
            position: relative;
            z-index: 1;
        }
        
        #addManageTable tbody tr:hover,
        #addManageTable tbody tr:has(.dropdown-toggle.show) {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transform: scale(1.002);
            background: rgba(var(--bs-primary-rgb), 0.01);
            z-index: 10;
        }
        
        #addManageTable td {
            border: none;
            padding: 1.25rem 1rem;
            vertical-align: middle;
        }

        .img-thumbnail {
            border-radius: 0.75rem;
            border: none;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .btn-outline-primary {
            border-radius: 0.75rem;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .btn-outline-primary {
                width: 100%;
                text-align: center;
                justify-content: center;
            }
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-radius: 0.75rem;
            padding: 0.5rem;
        }
        
        .dropdown-item {
            border-radius: 0.5rem;
            padding: 0.6rem 1rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .dropdown-item i {
            opacity: 0.7;
        }
    </style>

	<div class="main-container">
		<div class="container">
			<div class="row">
				<div class="col-md-3">
					@include('front.account.partials.sidebar')
				</div>

				<div class="col-md-9">
					<div class="posts-container">
						<h3 class="fw-bold border-bottom pb-3 mb-4 d-flex align-items-center gap-3">
							<i class="{{ $pageIcon }} text-primary"></i> {{ $pageTitle }}
						</h3>
						
						<div class="table-responsive">
							<form name="listForm" action="{{ url($basePath . '/delete') }}" method="POST">
								@csrf
								
								<div class="d-flex justify-content-between rounded p-3 mb-4 table-action align-items-center gap-3">
									<div class="text-nowrap d-flex align-items-center gap-2">
										<div class="btn-group" role="group">
											<button type="button" class="btn btn-sm btn btn-outline-primary px-3">
												<input type="checkbox" id="checkAll" class="from-check-all">
											</button>
											<button type="button" class="btn btn-sm btn btn-primary from-check-all px-3">
												{{ trans('global.All') }}
											</button>
										</div>
										
										<button type="submit" class="btn btn-sm btn btn-danger confirm-simple-action px-3">
											<i class="fa-regular fa-trash-can"></i> {{ trans('global.Delete') }}
										</button>
									</div>
									
									<div class="w-100 table-search">
										<div class="row align-items-center">
											<div class="col-md-12">
												<div class="input-group">
                                                    <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-search"></i></span>
												    <input type="text" class="form-control border-start-0 ps-0" id="filter" placeholder="{{ trans('global.search') }}...">
                                                </div>
											</div>
										</div>
									</div>
								</div>
								
								<table id="addManageTable"
									   class="table mb-0"
									   data-filter="#filter"
									   data-filter-text-only="true"
								>
									<thead>
									<tr>
										<th scope="col" data-type="numeric" data-sort-initial="true" class="d-md-table-cell d-none" style="width: 5%"></th>
										<th scope="col" style="width: 15%">{{ trans('global.Photo') }}</th>
										<th scope="col" data-sort-ignore="true" style="width: 50%">{{ trans('global.listing_details') }}</th>
										<th scope="col" data-type="numeric" class="d-md-table-cell d-sm-none d-none" style="width: 15%">{{ trans('global.price') }}</th>
										<th scope="col" style="width: 15%">{{ trans('global.action') }}</th>
									</tr>
									</thead>
									<tbody>
									
									@if (!empty($posts) && $totalPosts > 0)
										@foreach($posts as $post)
											@php
												$postUrl = urlGen()->post($post);
												$deletingUrl = url($basePath . '/' . data_get($post, 'id') . '/delete');
												
												$isForOwnerEdition = (
													in_array($pagePath, ['list', 'pending-approval'])
													&& isset($authUser, $authUser->id)
													&& $authUser->id == data_get($post, 'user_id')
												);
												
												$isEditingAllowed = (
													$isForOwnerEdition
													&& empty(data_get($post, 'archived_at'))
												);
												$isPhotoEditingAllowed = (
													$isForOwnerEdition
													&& isMultipleStepsFormEnabled()
												);
												$isPlanPaymentAllowed = (
													$isForOwnerEdition
													&& isMultipleStepsFormEnabled()
													&& $countPromotionPackages > 0 && $countPaymentMethods > 0
												);
												$isArchivingAllowed = (
													$pagePath == 'list'
													&& isVerifiedPost($post)
													&& empty(data_get($post, 'archived_at'))
												);
												$isRepostingAllowed = (
													$pagePath == 'archived'
													&& isset($authUser, $authUser->id)
													&& $authUser->id == data_get($post, 'user_id')
													&& !empty(data_get($post, 'archived_at'))
												);
												
												$editingUrl = urlGen()->editPost($post);
												$photoEditingUrl = url('posts/' . data_get($post, 'id') . '/photos');
												$planPaymentUrl = url('posts/' . data_get($post, 'id') . '/payment');
												$archivingUrl = url($basePath . '/' . data_get($post, 'id') . '/offline');
												$repostingUrl = url($basePath . '/' . data_get($post, 'id') . '/repost');

												$price = data_get($post, 'price');
												$priceFormatted = data_get($post, 'price_formatted');
												if (is_numeric($price) && $price > 0) {
													$priceFormatted = 'R$ ' . number_format($price, 0, ',', '.');
												}
											@endphp
											<tr>
												<td class="add-img-selector d-md-table-cell d-none" style="width:2%">
													<div class="checkbox">
														<label><input type="checkbox" name="entries[]" value="{{ data_get($post, 'id') }}"></label>
													</div>
												</td>
												<td style="width:20%" class="add-img-td">
													<a href="{{ $postUrl }}">
														<img class="img-thumbnail img-fluid" src="{{ data_get($post, 'picture.url.medium') }}" alt="img">
													</a>
												</td>
												<td style="width:52%" class="items-details-td">
													<div>
														<div class="mb-2">
															<a href="{{ $postUrl }}"
															   class="{{ linkClass() }} fw-bold fs-6"
															   title="{{ data_get($post, 'title') }}"
															>
																{{ str(data_get($post, 'title'))->limit(50) }}
															</a>
															@if (in_array($pagePath, ['list', 'archived', 'pending-approval']))
																@if (
																	!empty(data_get($post, 'payment'))
																	&& !empty(data_get($post, 'payment.package'))
																)
																	@php
																		$ribbonColor = data_get($post, 'payment.package.ribbon');
																		$ribbonColorClass = BootstrapColor::Badge->getColorClass($ribbonColor);
																		$packageShortName = data_get($post, 'payment.package.short_name');
																		$packageInfo = '';
																		if (data_get($post, 'featured') != 1) {
																			$ribbonColorClass = 'text-bg-secondary';
																			$packageInfo = ' (' . trans('global.expired') . ')';
																		}
																	@endphp
																	<span class="badge rounded-pill {{ $ribbonColorClass }} ms-1"
																	      data-bs-toggle="tooltip"
																	      data-bs-placement="bottom"
																	      title="{{ $packageShortName . $packageInfo }}"
																	>
																		{{ $packageShortName }}
																	</span>
																@endif
															@endif
														</div>
														
														<div class="metadata-row d-flex flex-wrap gap-3 align-items-center mb-2 text-muted" style="font-size: 0.85rem;">
															<span>
																<i class="fa-regular fa-eye me-1"></i> {{ data_get($post, 'visits_formatted') ?? 0 }} vistas
															</span>
															<span>
																<i class="bi bi-geo-alt me-1"></i> {{ data_get($post, 'city.name') ?? '-' }}
																<img src="{{ data_get($post, 'country_flag_url') }}" alt="" class="ms-1" style="height: 12px; vertical-align: middle;">
															</span>
														</div>

														<div class="dates-row text-secondary mb-2" style="font-size: 0.75rem; opacity: 0.8;">
															@php
																$createdAt = data_get($post, 'created_at_formatted');
																$updatedAt = data_get($post, 'updated_at_formatted');
																// If the helper format is too long, we can use Carbon here
																try {
																	$carbonCreated = \Illuminate\Support\Carbon::parse(data_get($post, 'created_at'));
																	$carbonUpdated = \Illuminate\Support\Carbon::parse(data_get($post, 'updated_at'));
																	$createdAt = $carbonCreated->translatedFormat('d M, H:i');
																	$updatedAt = $carbonUpdated->translatedFormat('d M, H:i');
																} catch (\Exception $e) {}
															@endphp
															<div class="d-flex flex-wrap gap-x-3">
																<span><i class="fa-regular fa-clock me-1"></i> Criado: {{ $createdAt }}</span>
																<span><i class="bi bi-arrow-repeat me-1"></i> Atualizado: {{ $updatedAt }}</span>
															</div>
														</div>

														{{-- Mobile Price --}}
														<div class="price-td-mobile d-md-none">
															{!! $priceFormatted !!}
														</div>
													</div>
												</td>
												<td style="width:16%" class="price-td d-md-table-cell d-none text-end">
													<div class="fw-bold fs-5 text-primary">
														{!! $priceFormatted !!}
													</div>
												</td>
												<td style="width:10%" class="action-td">
													<div>
														<div class="btn-group dropup-mobile">
															<button type="button"
															        class="btn btn btn-outline-primary dropdown-toggle"
															        data-bs-toggle="dropdown"
															        aria-expanded="false"
															>
																{{ trans('global.action') }}
															</button>
															<ul class="dropdown-menu dropdown-menu-end">
																@if ($isEditingAllowed)
																	<li>
																		<a class="dropdown-item" href="{{ $editingUrl }}">
																			<i class="fa-regular fa-pen-to-square"></i> {{ trans('global.Edit') }}
																		</a>
																	</li>
																@endif
																@if ($isPhotoEditingAllowed)
																	<li>
																		<a class="dropdown-item" href="{{ $photoEditingUrl }}">
																			<i class="bi bi-camera"></i> {{ trans('global.Update Photos') }}
																		</a>
																	</li>
																@endif
																@if ($isPlanPaymentAllowed)
																	<li>
																		<a class="dropdown-item" href="{{ $planPaymentUrl }}">
																			<i class="fa-regular fa-circle-check"></i> {{ trans('global.Make It Premium') }}
																		</a>
																	</li>
																@endif
																@if ($isArchivingAllowed)
																	<li>
																		<a class="dropdown-item confirm-simple-action" href="{{ $archivingUrl }}">
																			<i class="fa-solid fa-eye-slash"></i> {{ trans('global.put_it_offline') }}
																		</a>
																	</li>
																@endif
																@if ($isRepostingAllowed)
																	<li>
																		<a class="dropdown-item confirm-simple-action" href="{{ $repostingUrl }}">
																			<i class="fa-solid fa-recycle"></i> {{ trans('global.re_post_it') }}
																		</a>
																	</li>
																@endif
																<li>
																	<a class="dropdown-item confirm-simple-action text-danger"
																	   href="{{ $deletingUrl }}"
																	>
																		<i class="fa-regular fa-trash-can"></i> {{ trans('global.Delete') }}
																	</a>
																</li>
															</ul>
														</div>
													</div>
												</td>
											</tr>
										@endforeach
									@else
										<tr>
											<td colspan="5">
												<div class="text-center my-5">
													{{ $apiMessage ?? trans('global.no_posts_found') }}
												</div>
											</td>
										</tr>
									@endif
									</tbody>
								</table>
							</form>
						</div>
						
						@include('vendor.pagination.api.bootstrap-5')
						
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('after_scripts')
	<script src="{{ url('assets/plugins/footable-jquery/2.0.1.4/footable.js?v=2-0-1') }}" type="text/javascript"></script>
	<script src="{{ url('assets/plugins/footable-jquery/2.0.1.4/footable.filter.js?v=2-0-1') }}" type="text/javascript"></script>
	<script type="text/javascript">
		onDocumentReady((event) => {
			$('#addManageTable').footable().bind('footable_filtering', function (e) {
				let selected = $('.filter-status').find(':selected').text();
				if (selected && selected.length > 0) {
					e.filter += (e.filter && e.filter.length > 0) ? ' ' + selected : selected;
					e.clear = !e.filter;
				}
			});
			
			/* Clear Filter OnClick */
			const clearFilterEl = document.querySelector(".clear-filter");
			if (clearFilterEl) {
				clearFilterEl.addEventListener("click", (event) => {
					event.preventDefault();
					
					const filterStatusEl = document.querySelector(".filter-status");
					if (filterStatusEl) {
						filterStatusEl.value = '';
					}
					
					$('table.demo').trigger('footable_clear_filter');
				});
			}
			
			/* Check All OnClick */
			const checkAllEls = document.querySelectorAll('.from-check-all');
			if (checkAllEls.length > 0) {
				checkAllEls.forEach(checkEl => {
					checkEl.addEventListener('click', (event) => checkAllBoxes(event.target));
				});
			}
		});
	</script>
@endsection
