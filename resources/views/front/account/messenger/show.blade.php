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
    $authUser = auth()->check() ? auth()->user() : null;
	$authUserId = !empty($authUser) ? $authUser->getAuthIdentifier() : 0;
	
	$thread ??= [];
	$threadId = data_get($thread, 'id', 0);
	
    $fiTheme = config('larapen.core.fileinput.theme', 'bs5');
	$allowedFileFormatsJson = collect(getAllowedFileFormats())->toJson();
@endphp

@section('content')
	@include('front.common.spacer')
    <div class="main-container px-0 px-md-3">
        <div class="container-fluid py-0 py-md-3">
            <div class="messenger-wrapper content-active">
                {{-- Sidebar: hidden on mobile (d-none), flex on desktop (d-md-flex) --}}
                <div class="messenger-sidebar d-none d-md-flex flex-column">
                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="m-0 fw-bold">{{ trans('global.Messages') }}</h5>
                    </div>
                    
                    @include('front.account.messenger.partials.sidebar')

                    <div class="messenger-thread-list" id="listThreads">
                        {{-- Threads will be loaded via AJAX --}}
                        <div class="p-4 text-center text-muted">
                            <i class="fa-solid fa-circle-notch fa-spin"></i>
                        </div>
                    </div>
                </div>

                {{-- Main Chat Area: always visible --}}
                <div class="messenger-content">
                    <div class="messenger-header">
                        <div class="d-flex align-items-center flex-grow-1">
                            <a href="{{ url(urlGen()->getAccountBasePath() . '/messages') }}" class="btn btn-link p-0 me-3 d-md-none text-dark">
                                <i class="fa-solid fa-chevron-left fs-4"></i>
                            </a>
                            @php
                                $otherUser = null;
                                $participants = data_get($thread, 'participants', []);
                                
                                // 1. Try to find from participants list (most reliable)
                                if (!empty($participants)) {
                                    foreach ($participants as $p) {
                                        $u = data_get($p, 'user') ?? $p;
                                        if (data_get($u, 'id') && data_get($u, 'id') != $authUserId) {
                                            $otherUser = $u;
                                            break;
                                        }
                                    }
                                }
                                
                                // 2. Fallback to creator/recipient logic
                                if (empty($otherUser)) {
                                    $pCreator = data_get($thread, 'p_creator');
                                    if (!empty($pCreator) && data_get($pCreator, 'id') != $authUserId) {
                                        $otherUser = $pCreator;
                                    } else {
                                        $pRecipient = data_get($thread, 'p_recipient');
                                        $otherUser = data_get($pRecipient, 'user') ?? data_get($thread, 'post.user');
                                    }
                                }
                                
                                $post = data_get($thread, 'post');
                                $postTitle = data_get($post, 'title');
                                $postUrl = !empty($post) ? urlGen()->post($post) : '#';
                                
                                $otherUserName = data_get($otherUser, 'name', 'User');
                                $otherUserPhoto = data_get($otherUser, 'photo_url');
                            @endphp

                            <div class="position-relative me-3">
                                @php
                                    $photoUrl = !empty($otherUserPhoto) ? url($otherUserPhoto) : url('images/user.jpg');
                                @endphp
                                <img src="{{ $photoUrl }}" class="thread-avatar" style="width: 40px; height: 40px;" onerror="this.src='{{ url('images/user.jpg') }}'">
                                @if (!empty($otherUser) && isUserOnline($otherUser))
                                    <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-light rounded-circle" style="width: 12px; height: 12px;"></span>
                                @endif
                            </div>
                            <div class="min-width-0">
                                <h6 class="m-0 fw-bold text-truncate">{{ $otherUserName }}</h6>
                                @if (!empty($postTitle))
                                    <div class="d-flex align-items-center gap-1 text-muted" style="font-size: 0.75rem;">
                                        <span class="d-none d-sm-inline">{{ trans('global.Regarding') }}:</span>
                                        <a href="{{ $postUrl }}" class="text-primary text-truncate fw-medium" target="_blank" style="text-decoration: none;">
                                            {{ $postTitle }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="messenger-actions d-flex align-items-center">
                            @if (data_get($thread, 'p_is_important'))
                                <a href="{{ url(urlGen()->getAccountBasePath() . '/messages/' . $threadId . '/actions?type=markAsNotImportant') }}" class="btn btn-link text-warning p-2" title="{{ trans('global.Mark as not important') }}">
                                    <i class="fa-solid fa-star"></i>
                                </a>
                            @else
                                <a href="{{ url(urlGen()->getAccountBasePath() . '/messages/' . $threadId . '/actions?type=markAsImportant') }}" class="btn btn-link text-secondary p-2" title="{{ trans('global.Mark as important') }}">
                                    <i class="fa-regular fa-star"></i>
                                </a>
                            @endif
                            <a href="{{ url(urlGen()->getAccountBasePath() . '/messages/' . $threadId . '/delete') }}" class="btn btn-link text-danger p-2" onclick="return confirm('{{ trans('global.Are you sure?') }}')" title="{{ trans('global.Delete') }}">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </div>
                    </div>

                    {{-- Status Messages for JS --}}
                    <div id="successMsg" class="alert alert-success d-none m-2" role="alert"></div>
                    <div id="errorMsg" class="alert alert-danger d-none m-2" role="alert"></div>

                    {{-- Chat History --}}
                    <div class="messenger-chat-history" id="messageChatHistory">
                        <div id="linksMessages" class="text-center mb-3">
                            {!! $linksRender ?? '' !!}
                        </div>
                        @include('front.account.messenger.messages.messages')
                    </div>

                    {{-- Footer: Message Input --}}
                    <div class="messenger-footer">
                        @php
                            $updateUrl = url(urlGen()->getAccountBasePath() . '/messages/' . $threadId);
                        @endphp
                        <form id="chatForm" role="form" method="POST" action="{{ $updateUrl }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            @honeypot
                            <div class="messenger-input-wrapper">
                                <div class="file-upload-btn button-wrap">
                                    <label for="addFile" class="btn btn-outline-secondary rounded-circle" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;">
                                        <i class="fa-solid fa-paperclip"></i>
                                    </label>
                                    <input id="addFile" name="file_path" type="file" class="d-none">
                                </div>
                                <textarea id="body" name="body" maxlength="500" class="messenger-input" placeholder="{{ trans('global.Type a message') }}" rows="1"></textarea>
                                <button id="sendChat" class="messenger-btn-send" type="submit">
                                    <i class="fa-solid fa-paper-plane"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('after_styles')
    @parent
    <link href="{{ url('assets/css/messenger-modern.css') }}" rel="stylesheet">
    <link href="{{ url('assets/plugins/bootstrap-fileinput/css/fileinput.min.css') }}" rel="stylesheet">
    @if (config('lang.direction') == 'rtl')
        <link href="{{ url('assets/plugins/bootstrap-fileinput/css/fileinput-rtl.min.css') }}" rel="stylesheet">
    @endif
    @if (str_starts_with($fiTheme, 'explorer'))
        <link href="{{ url('assets/plugins/bootstrap-fileinput/themes/' . $fiTheme . '/theme.min.css') }}" rel="stylesheet">
    @endif
    <style>
        .file-input { display: none !important; } /* hide extra fileinput widget */

        /* Hide site footer and sub-header on mobile inside messenger */
        @media (max-width: 768px) {
            html, body {
                overflow: hidden !important;
                height: 100dvh !important;
                overscroll-behavior-y: none;
            }
            body header,
            body .navbar,
            body footer,
            body .footer,
            body #footer,
            body .breadcrumb-wrapper,
            body .sub-header,
            body .spacer {
                display: none !important;
            }
            .main-container {
                padding-top: 0 !important;
                margin-top: 0 !important;
                height: 100dvh !important;
                overflow: hidden !important;
            }
            .messenger-wrapper {
                height: 100dvh !important;
            }
            .messenger-footer {
                padding-bottom: 25px !important;
                background: #fff;
            }
        }
    </style>
@endsection

@section('after_scripts')
    @parent

    <script>
        var loadingImage = '{{ url('images/spinners/fading-line.gif') }}';
        var loadingErrorMessage = '{{ trans('global.Threads could not be loaded') }}';
        var actionErrorMessage = '{{ trans('global.This action could not be done') }}';
        var title = {
            'seen': '{{ trans('global.Mark as read') }}',
            'notSeen': '{{ trans('global.Mark as unread') }}',
            'important': '{{ trans('global.Mark as important') }}',
            'notImportant': '{{ trans('global.Mark as not important') }}',
        };

        {{-- Garante atualização a cada 5s mesmo se o admin não configurou o timer --}}
        if (typeof timerNewMessagesChecking === 'undefined' || timerNewMessagesChecking <= 0) {
            timerNewMessagesChecking = 5000;
        }
    </script>
    <script src="{{ url('assets/js/app/messenger.js') }}?v={{ time() }}" type="text/javascript"></script>
    <script src="{{ url('assets/js/app/messenger-chat.js') }}?v={{ time() }}" type="text/javascript"></script>
    
    <script src="{{ url('assets/plugins/bootstrap-fileinput/js/plugins/sortable.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('assets/plugins/bootstrap-fileinput/js/fileinput.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('assets/plugins/bootstrap-fileinput/themes/' . $fiTheme . '/theme.js') }}" type="text/javascript"></script>
    @php
        $langCode = config('app.locale');
		$fileinputCachedLocalePath = "cache/plugins/bootstrap-fileinput/js/locales/{$langCode}.js";
		$fileinputLocalePath = "assets/plugins/bootstrap-fileinput/js/locales/{$langCode}.js";
		if (file_exists(public_path($fileinputCachedLocalePath))) {
			$fileinputLocalePath = $fileinputCachedLocalePath;
		}
    @endphp
    <script src="{{ mixStaticFile(url()->asset($fileinputLocalePath)) }}" type="text/javascript"></script>
    
    <script>
        let options = {};
        options.theme = '{{ $fiTheme }}';
        options.language = '{{ config('app.locale') }}';
        options.rtl = {{ (config('lang.direction') == 'rtl') ? 'true' : 'false' }};
        options.allowedFileExtensions = {!! $allowedFileFormatsJson !!};
        options.minFileSize = {{ (int)config('settings.upload.min_file_size', 0) }};
        options.maxFileSize = {{ (int)config('settings.upload.max_file_size', 1000) }};
        options.browseClass = 'btn btn-primary';
        options.browseIcon = '<i class="fa-solid fa-paperclip" aria-hidden="true"></i>';
        options.layoutTemplates = {
            main1: '{browse}',
            main2: '{browse}',
            btnBrowse: '<div tabindex="500" class="{css}"{status}>{icon}</div>',
        };
        
        onDocumentReady((event) => {
            {{-- Load threads in sidebar on desktop --}}
            if (window.innerWidth >= 768) {
                fetchThreads('{{ url(urlGen()->getAccountBasePath() . '/messages') }}');
            }
        });

        function fetchThreads(url) {
            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.threads) {
                    document.getElementById('listThreads').innerHTML = data.threads;
                }
            })
            .catch(error => console.error('Error loading threads:', error));
        }
    </script>
@endsection
