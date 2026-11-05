<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('establishments')->orderBy('id')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Category::create($data + ['slug' => Category::uniqueSlug($data['name']), 'is_active' => true]);

        return back()->with('success', 'Category created.');
    }

    public function show(Category $category): View
    {
        $establishments = $category->establishments()->with('owner')->latest()->paginate(config('autokonek.pagination.admin'));

        return view('admin.categories.show', compact('category', 'establishments'));
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();

        $category->update($data + ['slug' => Category::uniqueSlug($data['name'], $category->id)]);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated.');
    }

    /** Activate or deactivate from the list page. */
    public function toggle(Category $category): RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);

        return back()->with('success', 'Category '.($category->is_active ? 'activated.' : 'deactivated.'));
    }

    public function destroy(Category $category): RedirectResponse
    {
        // No reassignment flow: a category that is still used cannot be deleted.
        if ($category->establishments()->exists()) {
            return back()->with('error', 'This category is still used by establishments. Deactivate it instead, or move those establishments first.');
        }

        $category->delete();

        return back()->with('success', 'Category deleted.');
    }
}
