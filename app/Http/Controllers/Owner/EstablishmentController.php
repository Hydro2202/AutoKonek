<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\EstablishmentRequest;
use App\Models\Category;
use App\Models\Establishment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class EstablishmentController extends Controller
{
    public function index()
    {
        $establishments = auth()->user()->establishments()
            ->with(['category', 'images'])->withRatings()->latest()->paginate(10);

        return view('owner.establishments.index', compact('establishments'));
    }

    public function create()
    {
        return view('owner.establishments.create', [
            'categories' => Category::active()->orderBy('id')->get(),
        ]);
    }

    public function store(EstablishmentRequest $request): RedirectResponse
    {
        $establishment = DB::transaction(function () use ($request) {
            $establishment = new Establishment($request->safe()->only(Establishment::LISTING_FIELDS));
            $establishment->user_id = $request->user()->id;
            $establishment->slug = Establishment::uniqueSlug($establishment->name);
            $establishment->status = Establishment::STATUS_PENDING; // always starts as pending
            $establishment->save();

            ImageController::saveUploads($establishment, $request->file('images', []));

            return $establishment;
        });

        return redirect()->route('owner.services.index', $establishment)
            ->with('success', 'Your establishment was submitted for approval. Add your services below while you wait.');
    }

    public function edit(Establishment $establishment)
    {
        Gate::authorize('manage', $establishment);

        return view('owner.establishments.edit', [
            'establishment' => $establishment->load('category'),
            'categories' => Category::active()->orWhere('id', $establishment->category_id)->orderBy('id')->get(),
        ]);
    }

    /**
     * Approval policy:
     *  - Editing an APPROVED listing's details sends it back to PENDING, so changes are re-checked.
     *    (Services and images can be changed freely without re-approval.)
     *  - Editing a REJECTED listing resubmits it as PENDING and clears the rejection reason.
     *  - A listing that is already PENDING stays pending.
     */
    public function update(EstablishmentRequest $request, Establishment $establishment): RedirectResponse
    {
        Gate::authorize('manage', $establishment);

        $message = 'Establishment updated.';

        DB::transaction(function () use ($request, $establishment, &$message) {
            $establishment->fill($request->safe()->only(Establishment::LISTING_FIELDS));

            if ($establishment->status === Establishment::STATUS_REJECTED) {
                $establishment->status = Establishment::STATUS_PENDING;
                $establishment->rejection_reason = null;
                $message = 'Your corrections were saved and the listing has been resubmitted for approval.';
            } elseif ($establishment->status === Establishment::STATUS_APPROVED && $establishment->isDirty()) {
                $establishment->status = Establishment::STATUS_PENDING;
                $message = 'Changes saved. Because this listing was already approved, it is hidden from the public until an administrator approves the changes.';
            }

            $establishment->save();
            ImageController::saveUploads($establishment, $request->file('images', []));
        });

        return redirect()->route('owner.establishments.index')->with('success', $message);
    }

    public function destroy(Establishment $establishment): RedirectResponse
    {
        Gate::authorize('manage', $establishment);

        $establishment->load('images')->deleteImageFiles();
        $establishment->delete();

        return redirect()->route('owner.establishments.index')->with('success', 'Establishment deleted.');
    }
}
