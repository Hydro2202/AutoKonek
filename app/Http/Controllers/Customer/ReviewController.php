<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\ReviewRequest;
use App\Models\Establishment;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

/** Customer reviews. Routes are already limited to the customer role. */
class ReviewController extends Controller
{
    public function store(ReviewRequest $request, Establishment $establishment): RedirectResponse
    {
        // Unapproved listings cannot be reviewed (and are not revealed either).
        abort_unless($establishment->isApproved(), 404);

        if ($establishment->reviews()->where('user_id', $request->user()->id)->exists()) {
            return back()->withFragment('review-form')->with('error', 'You have already reviewed this establishment. You can edit your existing review instead.');
        }

        $review = new Review($request->validated());
        $review->user_id = $request->user()->id;
        $review->establishment_id = $establishment->id;
        $review->save();

        // Land back on the reviews section, not the top of the page.
        return redirect()->route('establishments.show', $establishment)->withFragment('reviews')->with('success', 'Thanks! Your review has been posted.');
    }

    public function update(ReviewRequest $request, Review $review): RedirectResponse
    {
        Gate::authorize('modify', $review);

        $review->update($request->validated());

        return redirect()->route('establishments.show', $review->establishment)->withFragment('reviews')->with('success', 'Your review has been updated.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        Gate::authorize('modify', $review);

        $review->delete();

        return back()->withFragment('reviews')->with('success', 'Your review has been deleted.');
    }
}
