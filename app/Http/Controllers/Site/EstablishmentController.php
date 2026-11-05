<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Establishment;
use App\Services\LocationLookup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/** Public directory: only approved establishments are ever listed. */
class EstablishmentController extends Controller
{
    public function __construct(private readonly LocationLookup $locations) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:120'],
            'region' => ['nullable', 'integer'],
            'province' => ['nullable', 'integer'],
            'city' => ['nullable', 'integer'],
            'sort' => ['nullable', 'in:name,rating,newest,nearest'],
            // The visitor's GPS position, sent by the "Near me" button. Used for this request only.
            'lat' => ['nullable', 'numeric', 'between:-90,90', 'required_with:lng'],
            'lng' => ['nullable', 'numeric', 'between:-180,180', 'required_with:lat'],
        ]);

        $hasPosition = isset($filters['lat'], $filters['lng']);

        $query = Establishment::approved()
            ->with(['category', 'images', 'city.province', 'city.region'])
            ->withRatings()
            ->search($filters['q'] ?? null)
            ->inPlace($filters['region'] ?? null, $filters['province'] ?? null, $filters['city'] ?? null)
            ->when($filters['category'] ?? null, fn ($q, $slug) => $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->when($hasPosition, fn ($q) => $q->withDistanceFrom((float) $filters['lat'], (float) $filters['lng']));

        match (true) {
            ($filters['sort'] ?? null) === 'name' => $query->orderBy('name'),
            // Unrated listings sort last; ties broken by review count.
            ($filters['sort'] ?? null) === 'rating' => $query->orderByRaw('reviews_avg_rating IS NULL')->orderByDesc('reviews_avg_rating')->orderByDesc('reviews_count'),
            ($filters['sort'] ?? null) === 'nearest' && $hasPosition => $query->orderByRaw('distance_km IS NULL')->orderBy('distance_km')->orderBy('name'),
            default => $query->latest()->orderByDesc('id'),
        };

        $establishments = $query->paginate(config('autokonek.pagination.directory'))->withQueryString();

        $categories = Category::active()
            ->withCount(['establishments' => fn ($q) => $q->approved()])
            ->orderBy('id')
            ->get();

        $location = $this->locations->forSelection(
            $filters['region'] ?? null,
            $filters['province'] ?? null,
            $filters['city'] ?? null,
        );

        return view('site.establishments.index', compact('establishments', 'categories', 'location', 'filters', 'hasPosition'));
    }

    public function category(Category $category): RedirectResponse
    {
        return redirect()->route('establishments.index', ['category' => $category->slug]);
    }

    public function show(Establishment $establishment): View
    {
        // Pending/rejected listings are visible only to their owner and to administrators.
        Gate::authorize('view', $establishment);

        $establishment->load(['category', 'owner', 'services', 'images', 'city.province', 'city.region']);
        $establishment->loadCount('reviews')->loadAvg('reviews', 'rating');

        $reviews = $establishment->reviews()->with('user')->paginate(5);

        // How many reviews gave each star rating (5 down to 1), for the summary bars.
        $perRating = $establishment->reviews()->reorder()->selectRaw('rating, count(*) as total')->groupBy('rating')->pluck('total', 'rating');
        $distribution = collect(range(5, 1))->mapWithKeys(fn ($star) => [$star => (int) ($perRating[$star] ?? 0)]);

        $user = auth()->user();
        $myReview = $user ? $establishment->reviews()->where('user_id', $user->id)->first() : null;
        $canReview = $user && $user->isCustomer() && $establishment->isApproved();

        return view('site.establishments.show', compact('establishment', 'reviews', 'distribution', 'myReview', 'canReview'));
    }
}
