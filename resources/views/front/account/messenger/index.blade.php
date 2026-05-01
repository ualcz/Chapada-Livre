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
    <div class="main-container px-0 px-md-3">
        <div class="container-fluid">
            <div class="messenger-wrapper">
                {{-- Sidebar: always visible (full width on mobile, fixed width on desktop) --}}
                <div class="messenger-sidebar d-flex flex-column">
                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="m-0 fw-bold">{{ trans('global.Messages') }}</h5>
                        <div class="btn-group">
                            <a href="{{ url(urlGen()->getAccountBasePath() . '/messages') }}" class="btn btn-outline-secondary btn-sm border-0">
                                <i class="fa-solid fa-rotate"></i>
                            </a>
                        </div>
                    </div>
                    
                    @include('front.account.messenger.partials.sidebar')
                    
                    <div id="successMsg" class="alert alert-success d-none m-2" role="alert"></div>
                    <div id="errorMsg" class="alert alert-danger d-none m-2" role="alert"></div>

                    <div class="messenger-thread-list" id="listThreads">
                        @include('front.account.messenger.threads.threads')
                    </div>
                </div>

                {{-- Empty State: hidden on mobile, visible on desktop --}}
                <div class="messenger-content d-none d-md-flex">
                    <div class="messenger-empty-state">
                        <i class="bi bi-chat-dots"></i>
                        <h4 class="fw-bold">{{ trans('global.welcome_to_messenger') }}</h4>
                        <p>{{ trans('global.select_a_conversation_to_start') }}</p>
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
