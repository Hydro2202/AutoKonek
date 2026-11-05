<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreReportRequest;
use App\Models\Establishment;
use App\Models\Report;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    /** Resolve the reported thing, or null when the reference is invalid. */
    private function findTarget(?string $type, mixed $id): Establishment|Review|null
    {
        if (! is_numeric($id)) {
            return null;
        }

        return match ($type) {
            'establishment' => Establishment::approved()->find($id),
            'review' => Review::with('establishment')->find($id),
            default => null,
        };
    }

    public function create(Request $request): View
    {
        $target = $this->findTarget($request->query('type'), $request->query('id'));
        abort_if($target === null, 404);

        return view('customer.report-form', [
            'target' => $target,
            'type' => $request->query('type'),
        ]);
    }

    public function store(StoreReportRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $target = $this->findTarget($data['target_type'], $data['target_id']);
        if ($target === null) {
            return back()->withErrors(['target_id' => 'The item you are reporting does not exist.'])->withInput();
        }

        $user = $request->user();
        $isEstablishment = $target instanceof Establishment;

        // user_id is the owner for a listing and the author for a review.
        if ($target->user_id === $user->id) {
            return back()->with('error', 'You cannot report your own '.($isEstablishment ? 'listing.' : 'review.'));
        }

        $column = $isEstablishment ? 'establishment_id' : 'review_id';

        // One open report per user per target.
        $duplicate = Report::where('user_id', $user->id)->where($column, $target->id)
            ->where('status', Report::STATUS_PENDING)->exists();
        if ($duplicate) {
            return back()->with('error', 'You already have an open report for this item. An administrator will review it soon.');
        }

        $report = new Report(['reason' => $data['reason'], 'description' => $data['description'] ?? null]);
        $report->user_id = $user->id;
        $report->{$column} = $target->id;
        $report->status = Report::STATUS_PENDING;
        $report->save();

        $back = $isEstablishment ? $target : $target->establishment;

        return redirect()->route('establishments.show', $back)->with('success', 'Thank you. Your report was sent to the administrators.');
    }
}
