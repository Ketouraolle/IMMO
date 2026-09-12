<?php

namespace App\Http\Controllers;

use App\Models\Property;

class PublicPropertyController extends Controller
{
    public function index()
    {
        // Filtering/pagination is handled live by the PropertyCatalog Livewire component.
        $stats = [
            'vacant' => Property::where('status', 'vacant')->count(),
            'cities' => Property::where('status', 'vacant')->whereNotNull('city')->distinct('city')->count('city'),
        ];

        return view('public.catalog', compact('stats'));
    }

    public function show(Property $property)
    {
        $property->load('images');

        return view('public.show', compact('property'));
    }
}
