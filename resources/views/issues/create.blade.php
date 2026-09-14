@extends('layouts.app')
@section('title', __('Report an issue'))
@section('content')
    <div class="page-head">
        <div>
            <a href="{{ route('issues.index') }}" class="back-to"><i class="bi bi-arrow-left"></i> {{ __('Issues') }}</a>
            <h3 class="mt-1">{{ __('Report an issue') }}</h3>
        </div>
    </div>
    <div class="card" style="max-width:560px;">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('issues.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">{{ __('Property') }}</label>
                    <select name="property_id" class="form-select" required>
                        <option value="">{{ __('Select a property…') }}</option>
                        @foreach($properties as $p)
                            <option value="{{ $p->id }}" {{ old('property_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Title') }}</label>
                    <input type="text" name="title" value="{{ old('title') }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Description') }}</label>
                    <textarea name="description" class="form-control" rows="4" required>{{ old('description') }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Priority') }}</label>
                    <select name="priority" class="form-select" required>
                        @foreach(['low', 'medium', 'high', 'urgent'] as $priority)
                            <option value="{{ $priority }}" @selected(old('priority', 'medium') === $priority)>{{ __(ucfirst($priority)) }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-dark">{{ __('Submit issue') }}</button>
                <a href="{{ route('issues.index') }}" class="btn btn-link">{{ __('Cancel') }}</a>
            </form>
        </div>
    </div>
@endsection
