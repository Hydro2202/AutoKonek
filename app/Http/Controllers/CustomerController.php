<?php

namespace App\Http\Controllers;

class CustomerController extends Controller
{
    public function reviews()
    {
        $reviews = auth()->user()->reviews()->with('establishment')->latest()->paginate(10);

        return view('customer.reviews', compact('reviews'));
    }

    public function reports()
    {
        $reports = auth()->user()->reports()->with(['establishment', 'review.establishment'])->latest()->paginate(10);

        return view('customer.reports', compact('reports'));
    }
}
