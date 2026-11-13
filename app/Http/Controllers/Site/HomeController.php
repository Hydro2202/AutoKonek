<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use App\Models\Establishment;
use App\Models\FuelPrice;
use App\Models\Review;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Category::active()
            ->withCount(['establishments' => fn ($query) => $query->approved()])
            ->orderBy('id')
            ->get();

        $recent = Establishment::approved()
            ->with(['category', 'images', 'city.province', 'city.region'])
            ->withRatings()
            ->latest()
            ->take(4)
            ->get();

        $totals = [
            'establishments' => Establishment::approved()->count(),
            'reviews' => Review::count(),
        ];

        // Places that have at least one approved listing, busiest first.
        $places = City::query()
            ->whereHas('establishments', fn ($q) => $q->approved())
            ->withCount(['establishments' => fn ($q) => $q->approved()])
            ->with(['province', 'region'])
            ->orderByDesc('establishments_count')
            ->orderBy('name')
            ->get();

        $fuelSeries = FuelPrice::chartSeries();

        return view('site.home', compact('categories', 'recent', 'totals', 'places', 'fuelSeries'));
    }
}
