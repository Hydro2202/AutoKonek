<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\Report;
use App\Models\Review;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        // Every number here is counted from the database.
        $stats = [
            'users' => User::count(),
            'owners' => User::where('role', User::ROLE_OWNER)->count(),
            'establishments' => Establishment::count(),
            'pending' => Establishment::where('status', Establishment::STATUS_PENDING)->count(),
            'reviews' => Review::count(),
            'reports' => Report::where('status', Report::STATUS_PENDING)->count(),
        ];

        $pending = Establishment::where('status', Establishment::STATUS_PENDING)
            ->with(['owner', 'category'])->latest()->take(5)->get();
        $recentReports = Report::with(['reporter', 'establishment', 'review'])->latest()->take(5)->get();
        $recentUsers = User::latest()->take(5)->get();
        $recentReviews = Review::with(['user', 'establishment'])->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'pending', 'recentReports', 'recentUsers', 'recentReviews'));
    }
}
