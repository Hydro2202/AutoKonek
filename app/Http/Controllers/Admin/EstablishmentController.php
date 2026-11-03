<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Establishment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EstablishmentController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:pending,approved,rejected'],
            'category' => ['nullable', 'integer'],
        ]);

        return $this->listing($filters, 'admin.establishments.index', 'Establishments');
    }

    /** "Pending approvals" is the same list locked to the pending status. */
    public function approvals(Request $request)
    {
        $filters = ['status' => Establishment::STATUS_PENDING, 'q' => $request->query('q')];

        return $this->listing($filters, 'admin.approvals', 'Pending approvals');
    }

    private function listing(array $filters, string $route, string $title)
    {
        $establishments = Establishment::query()
            ->with(['owner', 'category'])
            ->search($filters['q'] ?? null)
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($filters['category'] ?? null, fn ($q, $c) => $q->where('category_id', $c))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.establishments.index', [
            'establishments' => $establishments,
            'filters' => $filters,
            'categories' => Category::orderBy('name')->get(),
            'route' => $route,
            'title' => $title,
        ]);
    }

    public function show(Establishment $establishment)
    {
        $establishment->load(['owner', 'category', 'services', 'images'])->loadCount('reviews');

        return view('admin.establishments.show', compact('establishment'));
    }

    public function approve(Establishment $establishment): RedirectResponse
    {
        $establishment->status = Establishment::STATUS_APPROVED;
        $establishment->rejection_reason = null;
        $establishment->save();

        return redirect()->route('admin.approvals')->with('success', '"'.$establishment->name.'" is now approved and visible to the public.');
    }

    public function reject(Request $request, Establishment $establishment): RedirectResponse
    {
        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'rejection_reason.required' => 'Please give the owner a reason for rejecting this listing.',
            'rejection_reason.min' => 'The reason should be at least 10 characters so the owner knows what to fix.',
        ]);

        $establishment->status = Establishment::STATUS_REJECTED;
        $establishment->rejection_reason = $data['rejection_reason'];
        $establishment->save();

        return redirect()->route('admin.approvals')->with('success', '"'.$establishment->name.'" was rejected. The owner can see your reason.');
    }

    public function destroy(Establishment $establishment): RedirectResponse
    {
        $establishment->load('images')->deleteImageFiles();
        $establishment->delete();

        return redirect()->route('admin.establishments.index')->with('success', 'Establishment deleted.');
    }
}
