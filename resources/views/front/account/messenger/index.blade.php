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
    $apiResult ??= [];
	$threads = (array)data_get($apiResult, 'data');
	$totalThreads = (int)data_get($apiResult, 'meta.total', 0);
@endphp

@section('content')
	@include('front.common.spacer')
    <div class="main-container">
        <div class="container">
            <div class="messenger-wrapper">
                {{-- Sidebar: Filter & Thread List --}}
                <div class="messenger-sidebar">
                    <div class="messenger-header">
                        <h5 class="m-0 fw-bold">{{ trans('global.inbox') }}</h5>
                        <div class="btn-group">
                            <button type="button" id="btnRefresh" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="{{ trans('global.refresh') }}">
                                <i class="fa-solid fa-rotate"></i>
                            </button>
                            <div class="dropdown d-inline-block">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a href="" class="dropdown-item markAllAsRead">{{ trans('global.Mark all as read') }}</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    @include('front.account.messenger.partials.sidebar')
                    
                    <div id="successMsg" class="alert alert-success d-none m-2" role="alert"></div>
                    <div id="errorMsg" class="alert alert-danger d-none m-2" role="alert"></div>

                    <div class="messenger-thread-list" id="listThreads">
                        @include('front.account.messenger.threads.threads')
                    </div>
                    
                    <div class="p-2 border-top bg-light" id="linksThreads">
                        @include('front.account.messenger.threads.links')
                    </div>
                </div>

                {{-- Main Chat Area: Empty State --}}
                <div class="messenger-content d-none d-md-flex align-items-center justify-content-center text-center p-5">
                    <div>
                        <div class="mb-4">
                            <i class="bi bi-chat-dots text-primary" style="font-size: 5rem;"></i>
                        </div>
                        <h4 class="fw-bold">{{ trans('global.welcome_to_messenger') }}</h4>
                        <p class="text-muted">{{ trans('global.select_a_conversation_to_start') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('after_styles')
    @parent
    <link href="{{ url('assets/css/messenger-modern.css') }}" rel="stylesheet">
@endsection

@section('after_scripts')
	<script>
        var loadingImage = '{{ url('images/spinners/fading-line.gif') }}';
        var loadingErrorMessage = '{{ trans('global.Threads could not be loaded') }}';
        var actionText = '{{ trans('global.action') }}';
        var actionErrorMessage = '{{ trans('global.This action could not be done') }}';
        var title = {
            'seen': '{{ trans('global.Mark as read') }}',
            'notSeen': '{{ trans('global.Mark as unread') }}',
            'important': '{{ trans('global.Mark as important') }}',
            'notImportant': '{{ trans('global.Mark as not important') }}',
        };
	</script>
    <script src="{{ url('assets/js/app/messenger.js') }}" type="text/javascript"></script>
@endsection
