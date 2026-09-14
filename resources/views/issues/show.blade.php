@extends('layouts.app')
@section('title', $issue->title)
@section('content')
    @php $statuses = ['open' => __('Open'), 'in_progress' => __('In progress'), 'resolved' => __('Resolved'), 'closed' => __('Closed')]; @endphp

    <div class="page-head">
        <div>
            <a href="{{ route('issues.index') }}" class="back-to"><i class="bi bi-arrow-left"></i> {{ __('Issues') }}</a>
            <h3 class="mt-1">{{ $issue->title }}</h3>
            <p class="page-head__sub">{{ __(':property · reported by :name on :date', ['property' => $issue->property->name, 'name' => $issue->reportedBy->name, 'date' => $issue->created_at->translatedFormat('d M Y')]) }}</p>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-7">
            <div class="card">
                <div class="card-body p-4">
                    <p class="mb-0" style="white-space: pre-line;">{{ $issue->description }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card">
                <div class="card-body p-4">
                    <div class="mb-2"><span class="text-muted">{{ __('Priority') }}:</span> {{ __(ucfirst($issue->priority)) }}</div>
                    <div class="mb-3"><span class="text-muted">{{ __('Assigned to') }}:</span> {{ $issue->assignedTo->name ?? __('Unassigned') }}</div>

                    @if(auth()->user()->isAdmin())
                        <form method="POST" action="{{ route('issues.update', $issue) }}">
                            @csrf @method('PUT')
                            <div class="mb-3">
                                <label class="form-label">{{ __('Status') }}</label>
                                <select name="status" class="form-select">
                                    @foreach($statuses as $value => $label)
                                        <option value="{{ $value }}" {{ $issue->status == $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __('Assign to staff') }}</label>
                                <select name="assigned_to" class="form-select">
                                    <option value="">{{ __('Unassigned') }}</option>
                                    @foreach($staff as $s)
                                        <option value="{{ $s->id }}" {{ $issue->assigned_to == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button class="btn btn-dark btn-sm">{{ __('Update') }}</button>
                        </form>
                    @else
                        <span class="badge-soft badge-soft--neutral">{{ $statuses[$issue->status] ?? $issue->status }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
