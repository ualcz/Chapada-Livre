@extends('errors.master')

@php
    $title = trans('global.Unauthorized action');
	
    $defaultErrorMessage = trans('global.Meanwhile, you may return to homepage', ['url' => url('/')]);
    $extractedMessage = null;
    
    if (isset($exception) && $exception instanceof \Throwable) {
        $extractedMessage = $exception->getMessage();
        $extractedMessage = str_replace(base_path(), '', $extractedMessage);
    }
    
    $message = !empty($extractedMessage) ? $extractedMessage : $defaultErrorMessage;
@endphp

@section('title', $title)
@section('status', 401)
@section('message')
    {!! $message !!}
@endsection
