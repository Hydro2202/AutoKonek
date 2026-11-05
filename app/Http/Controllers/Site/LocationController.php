<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Province;
use App\Models\Region;
use App\Services\LocationLookup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Browse every Philippine region and city, and supply the lists for the cascading selectors. */
class LocationController extends Controller
{
    public function __construct(private readonly LocationLookup $locations) {}

    /** "Browse by place": all regions, with their cities (or every city and municipality). */
    public function index(Request $request): View
    {
        $showAll = $request->query('show') === 'all';

        $places = City::query()
            ->when(! $showAll, fn ($q) => $q->where('is_city', true))
            ->withCount(['establishments' => fn ($q) => $q->approved()])
            ->orderBy('name')
            ->get();

        return view('site.places', [
            'regions' => Region::orderBy('id')->get(),
            'provinces' => Province::orderBy('name')->get()->keyBy('id'),
            'placesByRegion' => $places->groupBy('region_id'),
            'showAll' => $showAll,
            'cityCount' => City::where('is_city', true)->count(),
            'townCount' => City::count(),
        ]);
    }

    public function provinces(Request $request): JsonResponse
    {
        $data = $request->validate(['region' => ['required', 'integer']]);

        return response()->json(
            $this->locations->provinces($data['region'])->map(fn ($p) => ['id' => $p->id, 'name' => $p->name])->values()
        );
    }

    /** Cities of a province, or of a region that has no provinces (Metro Manila). */
    public function cities(Request $request): JsonResponse
    {
        $data = $request->validate([
            'region' => ['nullable', 'integer'],
            'province' => ['nullable', 'integer'],
        ]);

        return response()->json(
            $this->locations->cities($data['region'] ?? null, $data['province'] ?? null)
                ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'city' => $c->is_city])->values()
        );
    }
}
