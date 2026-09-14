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

        $property = Property::create($request->validate($this->rules()));

        return redirect()->route('properties.show', $property)->with('status', __('Property added.'));
    }

    public function show(Request $request, Property $property)
    {
        $this->authorizeView($request, $property);

        $property->load('owner', 'leases.tenant', 'leases.payments', 'leases.contract', 'issues.reportedBy', 'issues.assignedTo', 'images');
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

        $property->update($request->validate($this->rules()));

        return redirect()->route('properties.show', $property)->with('status', __('Property updated.'));
    }

    public function destroy(Request $request, Property $property)
    {
        $this->authorizeStaff($request);

        $property->delete();

        return redirect()->route('properties.index')->with('status', __('Property removed.'));
    }

    private function rules(): array
    {
        return [
            'owner_id' => ['required', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:50'],
            'monthly_rent' => ['required', 'numeric', 'min:0'],
            'visit_fee' => ['required', 'numeric', 'min:0'],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', 'in:vacant,occupied,maintenance'],
            'notes' => ['nullable', 'string'],
        ];
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
