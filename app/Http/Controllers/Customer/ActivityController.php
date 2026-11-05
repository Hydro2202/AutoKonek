<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/** "My reviews" and "My reports" pages for customers. */
class ActivityController extends Controller
{
    public function reviews(): View
    {
        $reviews = auth()->user()->reviews()->with('establishment')->latest()->paginate(10);

        return view('customer.reviews', compact('reviews'));
    }

    public function reports(): View
    {
        $reports = auth()->user()->reports()->with(['establishment', 'review.establishment'])->latest()->paginate(10);

        return view('customer.reports', compact('reports'));
    }
}
