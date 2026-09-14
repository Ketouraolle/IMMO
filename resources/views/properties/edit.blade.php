@extends('layouts.app')
@section('title', __('Edit property'))
@section('content')
    <div class="page-head">
        <div>
            <a href="{{ route('properties.show', $property) }}" class="back-to"><i class="bi bi-arrow-left"></i> {{ $property->name }}</a>
            <h3 class="mt-1">{{ __('Edit property') }}</h3>
        </div>
    </div>
    <div class="card" style="max-width:640px;">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('properties.update', $property) }}">
                @csrf
                @method('PUT')
                @include('properties._form', ['property' => $property])
                <button class="btn btn-dark">{{ __('Update property') }}</button>
                <a href="{{ route('properties.show', $property) }}" class="btn btn-link">{{ __('Cancel') }}</a>
            </form>
        </div>
    </div>
@endsection
