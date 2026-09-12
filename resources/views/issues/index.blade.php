@extends('layouts.app')
@section('title', 'Issues')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Issues</h3>
        @if(!auth()->user()->isAdmin())
        <a href="{{ route('issues.create') }}" class="btn btn-dark btn-sm">+ Report issue</a>
        @endif
    </div>

    <div class="mb-3 btn-group">
        <a href="{{ route('issues.index') }}" class="btn btn-sm btn-outline-secondary {{ request('status') ? '' : 'active' }}">All</a>
        @foreach(['open'=>'Open','in_progress'=>'In progress','resolved'=>'Resolved','closed'=>'Closed'] as $val=>$label)
            <a href="{{ route('issues.index', ['status'=>$val]) }}" class="btn btn-sm btn-outline-secondary {{ request('status')==$val ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Title</th><th>Property</th><th>Reported by</th><th>Priority</th><th>Status</th></tr></thead>
                <tbody>
                @forelse($issues as $i)
                    <tr>
                        <td><a href="{{ route('issues.show', $i) }}">{{ $i->title }}</a></td>
                        <td>{{ $i->property->name }}</td>
                        <td>{{ $i->reportedBy->name }}</td>
                        <td>{{ ucfirst($i->priority) }}</td>
                        <td><span class="badge bg-secondary">{{ ucfirst(str_replace('_',' ',$i->status)) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted text-center py-4">No issues found</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $issues->links() }}</div>
@endsection
