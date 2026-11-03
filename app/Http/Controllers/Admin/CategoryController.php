<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    private function rules(?Category $category = null): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($category?->id)],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    private function slugFor(string $name, ?Category $category = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;
        while (Category::where('slug', $slug)->when($category, fn ($q) => $q->whereKeyNot($category->id))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    public function index()
    {
        $categories = Category::withCount('establishments')->orderBy('id')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());

        Category::create($data + ['slug' => $this->slugFor($data['name']), 'is_active' => true]);

        return back()->with('success', 'Category created.');
    }

    public function show(Category $category)
    {
        $establishments = $category->establishments()->with('owner')->latest()->paginate(15);

        return view('admin.categories.show', compact('category', 'establishments'));
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        // The list page sends only is_active for its activate/deactivate button.
        if ($request->has('toggle')) {
            $category->update(['is_active' => ! $category->is_active]);

            return back()->with('success', 'Category '.($category->is_active ? 'activated.' : 'deactivated.'));
        }

        $data = $request->validate($this->rules($category));
        $category->update($data + ['slug' => $this->slugFor($data['name'], $category)]);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated.');
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
