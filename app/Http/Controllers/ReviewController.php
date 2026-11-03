<?php

namespace App\Http\Controllers;

use App\Models\Establishment;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/** Customer reviews. Routes are already limited to the customer role. */
class ReviewController extends Controller
{
    private function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }

    public function store(Request $request, Establishment $establishment): RedirectResponse
    {
        // Unapproved listings cannot be reviewed (and are not revealed either).
        abort_unless($establishment->isApproved(), 404);

        $data = $request->validate($this->rules());

        if ($establishment->reviews()->where('user_id', $request->user()->id)->exists()) {
            return back()->with('error', 'You have already reviewed this establishment. You can edit your existing review instead.');
        }

        $review = new Review($data);
        $review->user_id = $request->user()->id;
        $review->establishment_id = $establishment->id;
        $review->save();

        return redirect()->route('establishments.show', $establishment)->with('success', 'Thanks! Your review has been posted.');
    }

    public function update(Request $request, Review $review): RedirectResponse
    {
        Gate::authorize('modify', $review);

        $review->update($request->validate($this->rules()));

        return redirect()->route('establishments.show', $review->establishment)->with('success', 'Your review has been updated.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        Gate::authorize('modify', $review);

        $review->delete();

        return back()->with('success', 'Your review has been deleted.');
    }
}
