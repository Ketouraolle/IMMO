<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IssueController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $issues = Issue::with(['property', 'reportedBy', 'assignedTo'])
            ->when($user->isOwner(), fn ($q) => $q->whereHas('property', fn ($p) => $p->where('owner_id', $user->id)))
            ->when($user->isTenant(), fn ($q) => $q->where('reported_by', $user->id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('issues.index', compact('issues'));
    }

    public function create(Request $request)
    {
        $user = $request->user();

        $properties = $user->isAdmin()
            ? Property::orderBy('name')->get()
            : Property::whereHas('leases', fn ($q) => $q->where('tenant_id', $user->id)->where('status', 'active'))->get();

        return view('issues.create', compact('properties'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
        ]);

        $data['reported_by'] = $request->user()->id;
        $data['status'] = 'open';

        Issue::create($data);

        return redirect()->route('issues.index')->with('status', __('Issue reported.'));
    }

    public function show(Request $request, Issue $issue)
    {
        $this->authorizeView($request, $issue);

        $issue->load('property', 'reportedBy', 'assignedTo');
        $staff = $request->user()->isAdmin() ? User::where('role', 'admin')->get() : collect();

        return view('issues.show', compact('issue', 'staff'));
    }

    public function update(Request $request, Issue $issue)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'status' => ['required', Rule::in(['open', 'in_progress', 'resolved', 'closed'])],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $issue->update($data);

        return back()->with('status', __('Issue updated.'));
    }

    private function authorizeView(Request $request, Issue $issue): void
    {
        $user = $request->user();

        if ($user->isAdmin()) return;
        if ($user->isOwner() && $issue->property->owner_id === $user->id) return;
        if ($user->isTenant() && $issue->reported_by === $user->id) return;

        abort(403);
    }
}
