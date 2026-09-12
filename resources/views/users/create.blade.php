@extends('layouts.app')
@section('title', 'Create account')
@section('content')
    <h3 class="mb-4">Create account</h3>
    <div class="card" style="max-width:520px;">
        <div class="card-body">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Full name</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select" required>
                        <option value="">Select a role…</option>
                        <option value="admin" {{ old('role')=='admin'?'selected':'' }}>Admin (staff)</option>
                        <option value="owner" {{ old('role')=='owner'?'selected':'' }}>Owner</option>
                        <option value="tenant" {{ old('role')=='tenant'?'selected':'' }}>Tenant</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Temporary password</label>
                    <input type="text" name="password" class="form-control" required>
                    <div class="form-text">Share this with the user directly; they can change it later.</div>
                </div>
                <button class="btn btn-dark">Create account</button>
                <a href="{{ route('users.index') }}" class="btn btn-link">Cancel</a>
            </form>
        </div>
    </div>
@endsection
