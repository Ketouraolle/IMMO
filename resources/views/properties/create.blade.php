@extends('layouts.app')
@section('title', 'Add property')
@section('content')
    <h3 class="mb-4">Add property</h3>
    <div class="card" style="max-width:600px;">
        <div class="card-body">
            <form method="POST" action="{{ route('properties.store') }}">
                @csrf
                @include('properties._form', ['property' => null])
                <button class="btn btn-dark">Save property</button>
                <a href="{{ route('properties.index') }}" class="btn btn-link">Cancel</a>
            </form>
        </div>
    </div>
@endsection
