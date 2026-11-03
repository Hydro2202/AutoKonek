<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Establishment;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::active()->withCount(['establishments' => fn ($q) => $q->approved()])->orderBy('id')->get();

        $recent = Establishment::approved()
            ->with(['category', 'images'])
            ->withRatings()
            ->latest()
            ->take(6)
            ->get();

        return view('home', compact('categories', 'recent'));
    }
}
