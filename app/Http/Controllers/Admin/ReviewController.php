<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'rating' => ['nullable', 'integer', 'between:1,5'],
        ]);

        $reviews = Review::with(['user', 'establishment'])
            ->when($filters['q'] ?? null, function ($q, $term) {
                $like = '%'.addcslashes($term, '%_\\').'%';
                $q->where(fn ($w) => $w->where('comment', 'ilike', $like)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'ilike', $like))
                    ->orWhereHas('establishment', fn ($e) => $e->where('name', 'ilike', $like)));
            })
            ->when($filters['rating'] ?? null, fn ($q, $r) => $q->where('rating', $r))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.reviews.index', compact('reviews', 'filters'));
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return back()->with('success', 'Review removed.');
    }
}
