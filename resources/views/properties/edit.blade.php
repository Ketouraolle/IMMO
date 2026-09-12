@extends('layouts.app')
@section('title', 'Edit property')
@section('content')
    <h3 class="mb-4">Edit property</h3>
    <div class="card" style="max-width:600px;">
        <div class="card-body">
            <form method="POST" action="{{ route('properties.update', $property) }}">
                @csrf
                @method('PUT')
                @include('properties._form', ['property' => $property])
                <button class="btn btn-dark">Update property</button>
                <a href="{{ route('properties.show', $property) }}" class="btn btn-link">Cancel</a>
            </form>
        </div>
    </div>
@endsection
