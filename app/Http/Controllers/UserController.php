<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $users = User::query()
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->input('role')))
            ->orderBy('role')->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        return view('users.create');
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'role' => ['required', Rule::in(['admin', 'owner', 'tenant'])],
            'password' => ['required', 'string', 'min:6'],
        ]);

        User::create($data);

        return redirect()->route('users.index')->with('status', 'Account created.');
    }

    public function toggleActive(Request $request, User $user)
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_if($user->id === $request->user()->id, 403, "You can't deactivate your own account.");

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('status', $user->is_active ? 'Account reactivated.' : 'Account deactivated.');
    }
}
