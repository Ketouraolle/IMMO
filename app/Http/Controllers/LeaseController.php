<?php

namespace App\Http\Controllers;

use App\Models\Lease;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;

class LeaseController extends Controller
{
    // Assign a tenant to a property (admin only)
    public function create(Request $request, Property $property)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $tenants = User::where('role', 'tenant')->orderBy('name')->get();

        return view('leases.create', compact('property', 'tenants'));
    }

    public function store(Request $request, Property $property)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'tenant_id' => ['required', 'exists:users,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'rent_amount' => ['required', 'numeric', 'min:0'],
            'billing_cycle' => ['required', 'in:monthly,quarterly,yearly'],
            'document' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        if ($request->hasFile('document')) {
            $data['document_path'] = $request->file('document')->store('leases', 'public');
        }
        unset($data['document']);

        $data['property_id'] = $property->id;
        $data['status'] = 'active';

        Lease::create($data);

        $property->update(['status' => 'occupied']);

        return redirect()->route('properties.show', $property)->with('status', 'Tenant assigned to property.');
    }

    public function end(Request $request, Lease $lease)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $lease->update(['status' => 'ended', 'end_date' => now()]);
        $lease->property->update(['status' => 'vacant']);

        return redirect()->route('properties.show', $lease->property)->with('status', 'Lease ended, property marked vacant.');
    }
}
