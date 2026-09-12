<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $properties = Property::with('owner', 'activeLease.tenant', 'mainImage')
            ->when($user->isOwner(), fn ($q) => $q->where('owner_id', $user->id))
            ->when($user->isTenant(), fn ($q) => $q->whereHas('leases', fn ($l) => $l->where('tenant_id', $user->id)))
            ->latest()
            ->paginate(15);

        return view('properties.index', compact('properties'));
    }

    public function create(Request $request)
    {
        $this->authorizeStaff($request);

        $owners = User::where('role', 'owner')->orderBy('name')->get();

        return view('properties.create', compact('owners'));
    }

    public function store(Request $request)
    {
        $this->authorizeStaff($request);

        $data = $request->validate([
            'owner_id' => ['required', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:50'],
            'monthly_rent' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:vacant,occupied,maintenance'],
            'notes' => ['nullable', 'string'],
        ]);

        $property = Property::create($data);

        return redirect()->route('properties.show', $property)->with('status', 'Property added.');
    }

    public function show(Request $request, Property $property)
    {
        $this->authorizeView($request, $property);

        $property->load('owner', 'leases.tenant', 'leases.payments', 'issues.reportedBy', 'issues.assignedTo', 'images');
        $property->load(['visitRequests' => fn ($q) => $q->latest()]);

        return view('properties.show', compact('property'));
    }

    public function edit(Request $request, Property $property)
    {
        $this->authorizeStaff($request);

        $owners = User::where('role', 'owner')->orderBy('name')->get();

        return view('properties.edit', compact('property', 'owners'));
    }

    public function update(Request $request, Property $property)
    {
        $this->authorizeStaff($request);

        $data = $request->validate([
            'owner_id' => ['required', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:50'],
            'monthly_rent' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:vacant,occupied,maintenance'],
            'notes' => ['nullable', 'string'],
        ]);

        $property->update($data);

        return redirect()->route('properties.show', $property)->with('status', 'Property updated.');
    }

    public function destroy(Request $request, Property $property)
    {
        $this->authorizeStaff($request);

        $property->delete();

        return redirect()->route('properties.index')->with('status', 'Property removed.');
    }

    private function authorizeStaff(Request $request): void
    {
        abort_unless($request->user()->isAdmin(), 403);
    }

    private function authorizeView(Request $request, Property $property): void
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return;
        }

        if ($user->isOwner() && $property->owner_id === $user->id) {
            return;
        }

        if ($user->isTenant() && $property->leases()->where('tenant_id', $user->id)->exists()) {
            return;
        }

        abort(403);
    }
}
