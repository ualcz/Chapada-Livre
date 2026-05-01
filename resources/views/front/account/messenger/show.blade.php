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
    <div class="main-container">
        <div class="container">
            <div class="messenger-wrapper">
                {{-- Sidebar: Filter & Thread List --}}
                <div class="messenger-sidebar d-none d-md-flex">
                    <div class="messenger-header">
                        <h5 class="m-0 fw-bold">{{ trans('global.inbox') }}</h5>
                        <a href="{{ url(urlGen()->getAccountBasePath() . '/messages') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-rotate"></i>
                        </a>
                    </div>
                    @include('front.account.messenger.partials.sidebar')
                    <div class="messenger-thread-list" id="listThreads">
                        {{-- The threads are loaded via AJAX or in the index, but for show we can display a back button or a limited list --}}
                        <div class="p-3 text-center">
                            <a href="{{ url(urlGen()->getAccountBasePath() . '/messages') }}" class="btn btn-primary btn-sm w-100">
                                <i class="fa-solid fa-list"></i> {{ trans('global.back_to_list') }}
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Main Chat Area --}}
                <div class="messenger-content">
                    <div class="messenger-header">
                        <div class="d-flex align-items-center">
                            <a href="{{ url(urlGen()->getAccountBasePath() . '/messages') }}" class="btn btn-link d-md-none me-2">
                                <i class="fa-solid fa-arrow-left"></i>
                            </a>
                            @if ($authUserId != data_get($thread, 'p_creator.id'))
                                <img src="{{ url(data_get($thread, 'p_creator.photo_url')) }}" class="thread-avatar me-2" style="width: 40px; height: 40px;">
                                <div>
                                    <h6 class="m-0 fw-bold">{{ data_get($thread, 'p_creator.name') }}</h6>
                                    <small class="text-muted">
                                        @if (isUserOnline(data_get($thread, 'p_creator')))
                                            <i class="fa-solid fa-circle text-success" style="font-size: 8px;"></i> Online
                                        @else
                                            Offline
                                        @endif
                                    </small>
                                </div>
                            @else
                                {{-- It's the current user, show the other participant or the post title --}}
                                <div>
                                    <h6 class="m-0 fw-bold">{{ data_get($thread, 'post.title') }}</h6>
                                </div>
                            @endif
                        </div>
                        <div class="messenger-actions">
                            <div class="btn-group">
                                @if (data_get($thread, 'p_is_important'))
                                    <a href="{{ url(urlGen()->getAccountBasePath() . '/messages/' . $threadId . '/actions?type=markAsNotImportant') }}" class="btn btn-outline-secondary btn-sm markAsNotImportant border-0">
                                        <i class="fa-solid fa-star text-warning"></i>
                                    </a>
                                @else
                                    <a href="{{ url(urlGen()->getAccountBasePath() . '/messages/' . $threadId . '/actions?type=markAsImportant') }}" class="btn btn-outline-secondary btn-sm markAsImportant border-0">
                                        <i class="fa-regular fa-star"></i>
                                    </a>
                                @endif
                                <a href="{{ url(urlGen()->getAccountBasePath() . '/messages/' . $threadId . '/delete') }}" class="btn btn-outline-secondary btn-sm border-0" onclick="return confirm('{{ trans('global.Are you sure?') }}')">
                                    <i class="fa-solid fa-trash text-danger"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Status Messages for JS --}}
                    <div id="successMsg" class="alert alert-success d-none m-2" role="alert"></div>
                    <div id="errorMsg" class="alert alert-danger d-none m-2" role="alert"></div>

                    {{-- Chat History --}}
                    <div class="messenger-chat-history" id="messageChatHistory">
                        <div id="linksMessages" class="text-center mb-3">
                            {!! $linksRender !!}
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
                                    <input id="addFile" name="file_path" type="file" class="d-none">
                                    <label for="addFile" class="btn btn-outline-secondary rounded-circle" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                                        <i class="fa-solid fa-paperclip"></i>
                                    </label>
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
        .file-input {
            display: inline-block;
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
    </script>
    <script src="{{ url('assets/js/app/messenger.js') }}" type="text/javascript"></script>
    <script src="{{ url('assets/js/app/messenger-chat.js') }}" type="text/javascript"></script>
    
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
            {{-- fileinput (file_path) --}}
            $('#addFile').fileinput(options);
        });
    </script>
@endsection
