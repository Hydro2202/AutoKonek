<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use App\Models\Establishment;
use App\Models\Region;
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

        // A few listings from the home province; the filters cover the rest of the country.
        $recent = Establishment::approved()
            ->whereHas('city.province', fn ($q) => $q->where('name', config('autokonek.home.province')))
            ->with(['category', 'images', 'city.province', 'city.region'])
            ->withRatings()
            ->latest()
            ->take(4)
            ->get();

        $totals = [
            'establishments' => Establishment::approved()->count(),
            'reviews' => Review::count(),
        ];

        // Towns of the home province (Quezon) that have approved listings, busiest first.
        $places = City::query()
            ->whereHas('province', fn ($q) => $q->where('name', config('autokonek.home.province')))
            ->whereHas('establishments', fn ($q) => $q->approved())
            ->withCount(['establishments' => fn ($q) => $q->approved()])
            ->with(['province', 'region'])
            ->orderByDesc('establishments_count')
            ->orderBy('name')
            ->get();

        $regions = Region::orderBy('id')->get();

        return view('site.home', compact('categories', 'recent', 'totals', 'places', 'regions'));
    }
}
