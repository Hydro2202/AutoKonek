<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Review;

class ReviewController extends Controller
{
    /** Reviews for the signed-in owner's own establishments only. */
    public function index()
    {
        $user = auth()->user();
        $ids = $user->establishments()->select('id');

        $reviews = Review::whereIn('establishment_id', $ids)
            ->with(['user', 'establishment'])
            ->when(request('establishment'), fn ($q, $id) => $q->where('establishment_id', $id))
            ->latest()->paginate(10)->withQueryString();

        $establishments = $user->establishments()->orderBy('name')->get(['id', 'name']);

        return view('owner.reviews', compact('reviews', 'establishments'));
    }
}
