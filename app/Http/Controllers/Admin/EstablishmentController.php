<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectEstablishmentRequest;
use App\Models\Category;
use App\Models\Establishment;
use App\Services\EstablishmentImageService;
use App\Services\ListingApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EstablishmentController extends Controller
{
    public function __construct(
        private readonly EstablishmentImageService $images,
        private readonly ListingApprovalService $approval,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:pending,approved,rejected'],
            'category' => ['nullable', 'integer'],
        ]);

        return $this->listing($filters, 'admin.establishments.index', 'Establishments');
    }

    /** "Pending approvals" is the same list locked to the pending status. */
    public function approvals(Request $request): View
    {
        $filters = ['status' => Establishment::STATUS_PENDING, 'q' => $request->query('q')];

        return $this->listing($filters, 'admin.approvals', 'Pending approvals');
    }

    private function listing(array $filters, string $route, string $title): View
    {
        $establishments = Establishment::query()
            ->with(['owner', 'category', 'city.province', 'city.region'])
            ->search($filters['q'] ?? null)
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($filters['category'] ?? null, fn ($q, $c) => $q->where('category_id', $c))
            ->latest()->paginate(config('autokonek.pagination.admin'))->withQueryString();

        $statusCounts = Establishment::query()
            ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.establishments.index', [
            'establishments' => $establishments,
            'filters' => $filters,
            'categories' => Category::orderBy('name')->get(),
            'statusCounts' => $statusCounts,
            'route' => $route,
            'title' => $title,
        ]);
    }

    public function show(Establishment $establishment): View
    {
        $establishment->load(['owner', 'category', 'services', 'images', 'city.province', 'city.region'])->loadCount('reviews');

        return view('admin.establishments.show', compact('establishment'));
    }

    public function approve(Establishment $establishment): RedirectResponse
    {
        $this->approval->approve($establishment);

        return redirect()->route('admin.approvals')->with('success', '"'.$establishment->name.'" is now approved and visible to the public.');
    }

    public function reject(RejectEstablishmentRequest $request, Establishment $establishment): RedirectResponse
    {
        $this->approval->reject($establishment, $request->validated('rejection_reason'));

        return redirect()->route('admin.approvals')->with('success', '"'.$establishment->name.'" was rejected. The owner can see your reason.');
    }

    public function destroy(Establishment $establishment): RedirectResponse
    {
        $this->images->deleteAllFiles($establishment->load('images'));
        $establishment->delete();

        return redirect()->route('admin.establishments.index')->with('success', 'Establishment deleted.');
    }
}
