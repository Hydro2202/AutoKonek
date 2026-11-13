<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\FuelPrice;
use App\Models\Review;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $mine = $user->establishments();

        $stats = [
            'total' => (clone $mine)->count(),
            'approved' => (clone $mine)->where('status', Establishment::STATUS_APPROVED)->count(),
            'pending' => (clone $mine)->where('status', Establishment::STATUS_PENDING)->count(),
            'rejected' => (clone $mine)->where('status', Establishment::STATUS_REJECTED)->count(),
        ];

        $reviews = Review::whereIn('establishment_id', $user->establishments()->select('id'));
        $stats['reviews'] = (clone $reviews)->count();
        $stats['average'] = round((float) (clone $reviews)->avg('rating'), 1);

        $establishments = $user->establishments()->with(['category', 'images', 'city.province', 'city.region'])->withRatings()->latest()->get();
        $rejected = $establishments->where('status', Establishment::STATUS_REJECTED);
        $latestReviews = Review::whereIn('establishment_id', $user->establishments()->select('id'))
            ->with(['user', 'establishment'])->latest()->take(5)->get();

        $fuelSeries = FuelPrice::chartSeries();

        return view('owner.dashboard', compact('stats', 'establishments', 'rejected', 'latestReviews', 'fuelSeries'));
    }
}
