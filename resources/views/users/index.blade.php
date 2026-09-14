@extends('layouts.app')
@section('title', __('Users'))
@section('content')
    <div class="page-head">
        <div><h3>{{ __('Users') }}</h3></div>
        <a href="{{ route('users.create') }}" class="btn btn-dark btn-sm"><i class="bi bi-person-plus"></i> {{ __('Create account') }}</a>
    </div>

    <div class="pill-nav mb-3">
        <a href="{{ route('users.index') }}" class="{{ request('role') ? '' : 'active' }}">{{ __('All') }}</a>
        @foreach(['admin' => __('Admins'), 'owner' => __('Owners'), 'tenant' => __('Tenants')] as $value => $label)
            <a href="{{ route('users.index', ['role' => $value]) }}" class="{{ request('role') === $value ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Email') }}</th><th>{{ __('Phone') }}</th><th>{{ __('Role') }}</th><th>{{ __('Status') }}</th><th></th></tr></thead>
                <tbody>
                @forelse($users as $u)
                    <tr>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->phone ?? '—' }}</td>
                        <td><span class="badge badge-role-{{ $u->role }}">{{ __(ucfirst($u->role)) }}</span></td>
                        <td>
                            @if($u->is_active)
                                <span class="badge-soft badge-soft--success">{{ __('Active') }}</span>
                            @else
                                <span class="badge-soft badge-soft--neutral">{{ __('Deactivated') }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($u->id !== auth()->id())
                                <form method="POST" action="{{ route('users.toggle-active', $u) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-secondary">{{ $u->is_active ? __('Deactivate') : __('Reactivate') }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted text-center py-5">{{ __('No users found') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $users->links() }}</div>
@endsection
