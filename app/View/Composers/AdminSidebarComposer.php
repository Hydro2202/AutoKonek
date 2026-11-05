<?php

namespace App\View\Composers;

use App\Models\Establishment;
use App\Models\Report;
use Illuminate\View\View;

/** Supplies the live counters shown next to admin sidebar links. */
class AdminSidebarComposer
{
    public function compose(View $view): void
    {
        $view->with('sidebarCounts', [
            'approvals' => Establishment::where('status', Establishment::STATUS_PENDING)->count(),
            'reports' => Report::where('status', Report::STATUS_PENDING)->count(),
        ]);
    }
}
