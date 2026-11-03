<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ServiceController extends Controller
{
    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
        ];
    }

    public function index(Establishment $establishment)
    {
        Gate::authorize('manage', $establishment);

        return view('owner.services', ['establishment' => $establishment->load('services')]);
    }

    public function store(Request $request, Establishment $establishment): RedirectResponse
    {
        Gate::authorize('manage', $establishment);

        $establishment->services()->create($request->validate($this->rules()));

        return back()->with('success', 'Service added.');
    }

    public function update(Request $request, Establishment $establishment, Service $service): RedirectResponse
    {
        Gate::authorize('manage', $establishment);
        // Scoped check: the service must belong to this establishment (prevents cross-listing edits).
        abort_unless($service->establishment_id === $establishment->id, 404);

        $service->update($request->validate($this->rules()));

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
