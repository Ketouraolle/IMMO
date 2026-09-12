@extends('layouts.app')
@section('title', 'Report an issue')
@section('content')
    <h3 class="mb-4">Report an issue</h3>
    <div class="card" style="max-width:560px;">
        <div class="card-body">
            <form method="POST" action="{{ route('issues.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Property</label>
                    <select name="property_id" class="form-select" required>
                        <option value="">Select a property…</option>
                        @foreach($properties as $p)
                            <option value="{{ $p->id }}" {{ old('property_id')==$p->id?'selected':'' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" value="{{ old('title') }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4" required>{{ old('description') }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select" required>
                        <option value="low" {{ old('priority')=='low'?'selected':'' }}>Low</option>
                        <option value="medium" {{ old('priority','medium')=='medium'?'selected':'' }}>Medium</option>
                        <option value="high" {{ old('priority')=='high'?'selected':'' }}>High</option>
                        <option value="urgent" {{ old('priority')=='urgent'?'selected':'' }}>Urgent</option>
                    </select>
                </div>
                <button class="btn btn-dark">Submit issue</button>
                <a href="{{ route('issues.index') }}" class="btn btn-link">Cancel</a>
            </form>
        </div>
    </div>
@endsection
