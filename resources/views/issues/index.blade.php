@extends('layouts.app')
@section('title', __('Issues'))
@section('content')
    @php $priorityTone = ['low' => 'neutral', 'medium' => 'info', 'high' => 'warning', 'urgent' => 'danger']; @endphp

    <div class="page-head">
        <div><h3>{{ __('Issues') }}</h3></div>
        @if(! auth()->user()->isAdmin())
            <a href="{{ route('issues.create') }}" class="btn btn-dark btn-sm"><i class="bi bi-plus-lg"></i> {{ __('Report issue') }}</a>
        @endif
    </div>

    <div class="pill-nav mb-3">
        <a href="{{ route('issues.index') }}" class="{{ request('status') ? '' : 'active' }}">{{ __('All') }}</a>
        @foreach(['open' => __('Open'), 'in_progress' => __('In progress'), 'resolved' => __('Resolved'), 'closed' => __('Closed')] as $value => $label)
            <a href="{{ route('issues.index', ['status' => $value]) }}" class="{{ request('status') === $value ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead><tr><th>{{ __('Title') }}</th><th>{{ __('Property') }}</th><th>{{ __('Reported by') }}</th><th>{{ __('Priority') }}</th><th>{{ __('Status') }}</th></tr></thead>
                <tbody>
                @forelse($issues as $i)
                    <tr>
                        <td><a href="{{ route('issues.show', $i) }}">{{ $i->title }}</a></td>
                        <td>{{ $i->property->name }}</td>
                        <td>{{ $i->reportedBy->name }}</td>
                        <td><span class="badge-soft badge-soft--{{ $priorityTone[$i->priority] ?? 'neutral' }}">{{ __(ucfirst($i->priority)) }}</span></td>
                        <td><span class="badge-soft badge-soft--neutral">{{ __(ucfirst(str_replace('_', ' ', $i->status))) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted text-center py-5">{{ __('No issues found') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $issues->links() }}</div>
@endsection
