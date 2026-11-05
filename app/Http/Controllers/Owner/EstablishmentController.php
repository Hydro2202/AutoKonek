<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\EstablishmentRequest;
use App\Models\Category;
use App\Models\Establishment;
use App\Services\EstablishmentImageService;
use App\Services\ListingApprovalService;
use App\Services\LocationLookup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class EstablishmentController extends Controller
{
    public function __construct(
        private readonly EstablishmentImageService $images,
        private readonly ListingApprovalService $approval,
        private readonly LocationLookup $locations,
    ) {}

    public function index(): View
    {
        $establishments = auth()->user()->establishments()
            ->with(['category', 'images', 'city.province', 'city.region'])->withRatings()->latest()
            ->paginate(config('autokonek.pagination.owner'));

        return view('owner.establishments.index', compact('establishments'));
    }

    public function create(): View
    {
        return view('owner.establishments.create', [
            'categories' => Category::active()->orderBy('id')->get(),
            // A new listing starts at the home city (Lucena City) unless the form was already filled in.
            'location' => $this->locations->forSelection(
                old('region_id') ?: null,
                old('province_id') ?: null,
                old('city_id') ?: $this->locations->defaultCityId(),
            ),
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

            $this->images->store($establishment, $request->file('images', []));

            return $establishment;
        });

        return redirect()->route('owner.services.index', $establishment)
            ->with('success', 'Your establishment was submitted for approval. Add your services below while you wait.');
    }

    public function edit(Establishment $establishment): View
    {
        Gate::authorize('manage', $establishment);

        return view('owner.establishments.edit', [
            'establishment' => $establishment->load('category'),
            'categories' => Category::active()->orWhere('id', $establishment->category_id)->orderBy('id')->get(),
            'location' => $this->locations->forSelection(
                old('region_id') ?: null,
                old('province_id') ?: null,
                old('city_id') ?: $establishment->city_id,
            ),
        ]);
    }

    public function update(EstablishmentRequest $request, Establishment $establishment): RedirectResponse
    {
        Gate::authorize('manage', $establishment);

        $message = DB::transaction(function () use ($request, $establishment) {
            $establishment->fill($request->safe()->only(Establishment::LISTING_FIELDS));
            $message = $this->approval->saveOwnerEdit($establishment);
            $this->images->store($establishment, $request->file('images', []));

            return $message;
        });

        return redirect()->route('owner.establishments.index')->with('success', $message);
    }

    public function destroy(Establishment $establishment): RedirectResponse
    {
        Gate::authorize('manage', $establishment);

        $this->images->deleteAllFiles($establishment->load('images'));
        $establishment->delete();

        return redirect()->route('owner.establishments.index')->with('success', 'Establishment deleted.');
    }
}
