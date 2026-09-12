@extends('layouts.app')
@section('title', 'Users')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Users</h3>
        <a href="{{ route('users.create') }}" class="btn btn-dark btn-sm">+ Create account</a>
    </div>

    <div class="mb-3 btn-group">
        <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary {{ request('role') ? '' : 'active' }}">All</a>
        <a href="{{ route('users.index', ['role'=>'admin']) }}" class="btn btn-sm btn-outline-secondary {{ request('role')=='admin' ? 'active' : '' }}">Admins</a>
        <a href="{{ route('users.index', ['role'=>'owner']) }}" class="btn btn-sm btn-outline-secondary {{ request('role')=='owner' ? 'active' : '' }}">Owners</a>
        <a href="{{ route('users.index', ['role'=>'tenant']) }}" class="btn btn-sm btn-outline-secondary {{ request('role')=='tenant' ? 'active' : '' }}">Tenants</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse($users as $u)
                    <tr>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->phone ?? '—' }}</td>
                        <td><span class="badge badge-role-{{ $u->role }}">{{ ucfirst($u->role) }}</span></td>
                        <td>
                            @if($u->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Deactivated</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($u->id !== auth()->id())
                            <form method="POST" action="{{ route('users.toggle-active', $u) }}">
                                @csrf
                                <button class="btn btn-sm btn-outline-secondary">
                                    {{ $u->is_active ? 'Deactivate' : 'Reactivate' }}
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted text-center py-4">No users found</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $users->links() }}</div>
@endsection
