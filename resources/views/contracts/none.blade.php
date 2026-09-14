@extends('layouts.app')
@section('title', __('My contract'))
@section('content')
    <div class="page-head">
        <div><h3>{{ __('My contract') }}</h3></div>
    </div>
    <div class="card">
        <div class="card-body text-center py-5">
            <span class="stat-icon mb-3" style="width:3.4rem;height:3.4rem;font-size:1.5rem;"><i class="bi bi-file-earmark-text"></i></span>
            <h5>{{ __('No contract yet') }}</h5>
            <p class="text-muted mb-0">{{ __("The office hasn't sent your lease contract yet. You'll get a notification as soon as it's ready to sign.") }}</p>
        </div>
    </div>
@endsection
