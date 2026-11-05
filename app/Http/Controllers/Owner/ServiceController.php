<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\ServiceRequest;
use App\Models\Establishment;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Establishment $establishment): View
    {
        Gate::authorize('manage', $establishment);

        return view('owner.services', ['establishment' => $establishment->load('services')]);
    }

    public function store(ServiceRequest $request, Establishment $establishment): RedirectResponse
    {
        Gate::authorize('manage', $establishment);

        $establishment->services()->create($request->validated());

        return back()->with('success', 'Service added.');
    }

    public function update(ServiceRequest $request, Establishment $establishment, Service $service): RedirectResponse
    {
        Gate::authorize('manage', $establishment);
        // Scoped check: the service must belong to this establishment (prevents cross-listing edits).
        abort_unless($service->establishment_id === $establishment->id, 404);

        $service->update($request->validated());

        return back()->with('success', 'Service updated.');
    }

    public function destroy(Establishment $establishment, Service $service): RedirectResponse
    {
        Gate::authorize('manage', $establishment);
        abort_unless($service->establishment_id === $establishment->id, 404);

        $service->delete();

        return back()->with('success', 'Service removed.');
    }
}
