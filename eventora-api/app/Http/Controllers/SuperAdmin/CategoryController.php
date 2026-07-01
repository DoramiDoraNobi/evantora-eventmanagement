<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('events')->ordered()->get();
        return view('super-admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('super-admin.categories.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        // Check for slug duplicate
        if (Category::where('slug', $validated['slug'])->exists()) {
            return back()->withErrors(['name' => 'A category with a similar name already exists (slug conflict).'])->withInput();
        }

        Category::create($validated);

        return redirect()->route('super-admin.categories.index')->with('status', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        return view('super-admin.categories.form', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,' . $category->id,
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        // Check for slug duplicate (exclude current)
        if (Category::where('slug', $validated['slug'])->where('id', '!=', $category->id)->exists()) {
            return back()->withErrors(['name' => 'A category with a similar name already exists (slug conflict).'])->withInput();
        }

        $category->update($validated);

        return redirect()->route('super-admin.categories.index')->with('status', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        // Prevent deletion if events are using this category
        if ($category->events()->count() > 0) {
            return back()->with('error', 'Cannot delete this category because ' . $category->events()->count() . ' event(s) are using it. Please reassign them first.');
        }

        $category->delete();

        return redirect()->route('super-admin.categories.index')->with('status', 'Category deleted successfully.');
    }
}
