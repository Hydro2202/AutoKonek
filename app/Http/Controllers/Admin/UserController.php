<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'in:customer,business_owner,admin'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $users = User::query()
            ->when($filters['q'] ?? null, function ($q, $term) {
                $like = '%'.addcslashes($term, '%_\\').'%';
                $q->where(fn ($w) => $w->where('name', 'ilike', $like)->orWhere('email', 'ilike', $like));
            })
            ->when($filters['role'] ?? null, fn ($q, $role) => $q->where('role', $role))
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('is_active', $s === 'active'))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users', 'filters'));
    }

    public function toggle(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }
        if ($user->isAdmin()) {
            return back()->with('error', 'Administrator accounts cannot be deactivated here.');
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        return back()->with('success', $user->name.' has been '.($user->is_active ? 'activated.' : 'deactivated.'));
    }
}
