<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\VisitRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VisitRequestController extends Controller
{
    // Public — no auth required
    public function store(Request $request, Property $property)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['property_id'] = $property->id;
        $data['status'] = 'new';

        VisitRequest::create($data);

        return back()->with('status', 'Thanks! Our team will reach out to you shortly to schedule the visit.');
    }

    // Admin only — update lead status from the property page
    public function updateStatus(Request $request, VisitRequest $visitRequest)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'status' => ['required', Rule::in(['new', 'contacted', 'closed'])],
        ]);

        $data['handled_by'] = $request->user()->id;

        $visitRequest->update($data);

        return back()->with('status', 'Visit request updated.');
    }
}
