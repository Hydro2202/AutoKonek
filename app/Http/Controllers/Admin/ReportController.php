<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:pending,resolved,dismissed'],
            'type' => ['nullable', 'in:establishment,review'],
        ]);

        $reports = Report::with(['reporter', 'establishment', 'review.user', 'review.establishment', 'resolver'])
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($filters['type'] ?? null, fn ($q, $t) => $t === 'establishment' ? $q->whereNotNull('establishment_id') : $q->whereNotNull('review_id'))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.reports.index', compact('reports', 'filters'));
    }

    public function update(Request $request, Report $report): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:resolved,dismissed']]);

        $report->status = $data['status'];
        $report->resolved_by = $request->user()->id;
        $report->resolved_at = now();
        $report->save();

        return back()->with('success', 'Report marked as '.$data['status'].'.');
    }
}
