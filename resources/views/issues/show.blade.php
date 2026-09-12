@extends('layouts.app')
@section('title', $issue->title)
@section('content')
    <h3 class="mb-1">{{ $issue->title }}</h3>
    <div class="text-muted mb-4">{{ $issue->property->name }} · reported by {{ $issue->reportedBy->name }} on {{ $issue->created_at->format('d M Y') }}</div>

    <div class="row g-3">
        <div class="col-md-7">
            <div class="card">
                <div class="card-body">
                    <p class="mb-0">{{ $issue->description }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card">
                <div class="card-body">
                    <div class="mb-2"><span class="text-muted">Priority:</span> {{ ucfirst($issue->priority) }}</div>
                    <div class="mb-3"><span class="text-muted">Assigned to:</span> {{ $issue->assignedTo->name ?? 'Unassigned' }}</div>

                    @if(auth()->user()->isAdmin())
                    <form method="POST" action="{{ route('issues.update', $issue) }}">
                        @csrf @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                @foreach(['open'=>'Open','in_progress'=>'In progress','resolved'=>'Resolved','closed'=>'Closed'] as $val=>$label)
                                    <option value="{{ $val }}" {{ $issue->status==$val?'selected':'' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Assign to staff</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">Unassigned</option>
                                @foreach($staff as $s)
                                    <option value="{{ $s->id }}" {{ $issue->assigned_to==$s->id?'selected':'' }}>{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button class="btn btn-dark btn-sm">Update</button>
                    </form>
                    @else
                        <span class="badge bg-secondary">{{ ucfirst(str_replace('_',' ',$issue->status)) }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
