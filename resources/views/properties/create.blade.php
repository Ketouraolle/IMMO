@extends('layouts.app')
@section('title', __('Add property'))
@section('content')
    <div class="page-head">
        <div>
            <a href="{{ route('properties.index') }}" class="back-to"><i class="bi bi-arrow-left"></i> {{ __('Properties') }}</a>
            <h3 class="mt-1">{{ __('Add property') }}</h3>
        </div>
    </div>
    <div class="card" style="max-width:640px;">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('properties.store') }}">
                @csrf
                @include('properties._form', ['property' => null])
                <button class="btn btn-dark">{{ __('Save property') }}</button>
                <a href="{{ route('properties.index') }}" class="btn btn-link">{{ __('Cancel') }}</a>
            </form>
        </div>
    </div>
@endsection
