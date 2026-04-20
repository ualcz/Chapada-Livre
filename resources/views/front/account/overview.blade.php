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
@extends('front.layouts.master')

@php
	$authUser ??= auth()->user();
@endphp
@section('content')
	@include('front.common.spacer')
    
    <style>
        .welcome-card {
            background: #fff;
            border: none !important;
            border-radius: 1.25rem !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            padding: 2rem !important;
        }

        @media (max-width: 768px) {
            .welcome-card {
                padding: 1.25rem !important;
                border-radius: 1rem !important;
            }
        }
        
        .welcome-card h4 {
            color: #1a1a1a;
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        
        .last-login {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 0.5rem 1rem;
            background: rgba(var(--bs-primary-rgb), 0.05);
            border-radius: 50rem;
            color: var(--bs-primary);
            font-weight: 500;
            font-size: 0.85rem;
        }

        [data-bs-theme="dark"] .welcome-card {
            background: #1e1e1e !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        [data-bs-theme="dark"] .welcome-card h4 {
            color: #fff;
        }
        [data-bs-theme="dark"] .last-login {
            background: rgba(255, 255, 255, 0.05);
            color: rgba(255, 255, 255, 0.7);
        }
    </style>

	<div class="main-container">
		<div class="container">
			<div class="row">
				<div class="col-md-3">
					@include('front.account.partials.sidebar')
				</div>
				
				<div class="col-md-9">
					@if (isset($errors) && $errors->any())
						<div class="alert alert-danger alert-dismissible">
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ trans('global.Close') }}"></button>
							<h5><strong>{{ trans('global.validation_errors_title') }}</strong></h5>
							<ul>
								@foreach ($errors->all() as $error)
									<li>{!! $error !!}</li>
								@endforeach
							</ul>
						</div>
					@endif
					
					@include('front.account.partials.header', [
						'headerTitle' => '<i class="bi bi-person-lines-fill"></i> ' . trans('auth.overview')
					])
					
					<div class="welcome-card mb-4">
						<div class="row align-items-center">
							<div class="col-12">
								<h4 class="p-0 mb-3">
									{{ trans('global.Hello') }} {{ $authUser->name }}! 👋
								</h4>
								<div class="last-login">
                                    <i class="bi bi-clock-history"></i>
	                                {{ trans('global.You last logged in at') }}: {!! $authUser->last_login_at_formatted !!}
	                            </div>
							</div>
						</div>
						
                        <hr class="my-4 opacity-25">
                        
						@include('front.account.partials.overview-stats')
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
