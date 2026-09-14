@extends('layouts.app')
@section('title', __('Create account'))
@section('content')
    <div class="page-head">
        <div>
            <a href="{{ route('users.index') }}" class="back-to"><i class="bi bi-arrow-left"></i> {{ __('Users') }}</a>
            <h3 class="mt-1">{{ __('Create account') }}</h3>
        </div>
    </div>
    <div class="card" style="max-width:520px;">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">{{ __('Full name') }}</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Email') }}</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Phone') }}</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Role') }}</label>
                    <select name="role" class="form-select" required>
                        <option value="">{{ __('Select a role…') }}</option>
                        <option value="admin" @selected(old('role') === 'admin')>{{ __('Admin (staff)') }}</option>
                        <option value="owner" @selected(old('role') === 'owner')>{{ __('Owner') }}</option>
                        <option value="tenant" @selected(old('role') === 'tenant')>{{ __('Tenant') }}</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Temporary password') }}</label>
                    <input type="text" name="password" class="form-control" required>
                    <div class="form-text">{{ __('Share this with the user directly; they can change it later.') }}</div>
                </div>
                <button class="btn btn-dark">{{ __('Create account') }}</button>
                <a href="{{ route('users.index') }}" class="btn btn-link">{{ __('Cancel') }}</a>
            </form>
        </div>
    </div>
@endsection
