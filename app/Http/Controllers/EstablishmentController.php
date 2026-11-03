<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Establishment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/** Public directory: only approved establishments are ever listed. */
class EstablishmentController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'in:name,rating,newest'],
        ]);

        $query = Establishment::approved()
            ->with(['category', 'images'])
            ->withRatings()
            ->search($filters['q'] ?? null)
            ->when($filters['category'] ?? null, fn ($q, $slug) => $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->when($filters['city'] ?? null, fn ($q, $city) => $q->where(fn ($w) => $w->where('city', $city)->orWhere('municipality', $city)));

        match ($filters['sort'] ?? 'newest') {
            'name' => $query->orderBy('name'),
            // Unrated listings sort last; ties broken by review count.
            'rating' => $query->orderByRaw('reviews_avg_rating DESC NULLS LAST')->orderByDesc('reviews_count'),
            default => $query->latest(),
        };

        $establishments = $query->paginate(9)->withQueryString();

        $categories = Category::active()->orderBy('id')->get();
        $cities = Establishment::approved()->select('city')->distinct()->orderBy('city')->pluck('city');

        return view('establishments.index', compact('establishments', 'categories', 'cities', 'filters'));
    }

    public function category(Category $category): RedirectResponse
    {
        return redirect()->route('establishments.index', ['category' => $category->slug]);
    }

    public function show(Establishment $establishment)
    {
        // Pending/rejected listings are visible only to their owner and to administrators.
        Gate::authorize('view', $establishment);

        $establishment->load(['category', 'owner', 'services', 'images']);
        $establishment->loadCount('reviews')->loadAvg('reviews', 'rating');

        $reviews = $establishment->reviews()->with('user')->paginate(5);

        $user = auth()->user();
        $myReview = $user ? $establishment->reviews()->where('user_id', $user->id)->first() : null;
        $canReview = $user && $user->isCustomer() && $establishment->isApproved();

        return view('establishments.show', compact('establishment', 'reviews', 'myReview', 'canReview'));
    }
}
