<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

/** Sends each signed-in user to the right home page for their role. */
class DashboardController extends Controller
{
    public function index(): RedirectResponse
    {
        $user = auth()->user();

        return match (true) {
            $user->isAdmin() => redirect()->route('admin.dashboard'),
            $user->isOwner() => redirect()->route('owner.dashboard'),
            default => redirect()->route('establishments.index'),
        };
    }
}
