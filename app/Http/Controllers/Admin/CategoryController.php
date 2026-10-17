<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::withCount('products')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.form', ['category' => new Category()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['slug'] ?? $data['name']);
        if ($request->hasFile('image')) {
            $data['image'] = 'storage/'.$request->file('image')->store('categories', 'public');
        }
        Category::create($data);
        Cache::forget('storefront.catalog.v2');

        return redirect()->route('admin.categories.index')->with('status', 'Category created.');
    }

    public function edit(Request $request): View
    {
        return view('admin.categories.form', [
            'category' => Category::find($request->query('id')) ?? new Category(),
        ]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $this->validated($request, $category);
        $data['slug'] = Str::slug($data['slug'] ?? $data['name']);
        if ($request->hasFile('image')) {
            $data['image'] = 'storage/'.$request->file('image')->store('categories', 'public');
        }
        $category->update($data);
        Cache::forget('storefront.catalog.v2');

        return redirect()->route('admin.categories.index')->with('status', 'Category updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();
        Cache::forget('storefront.catalog.v2');

        return redirect()->route('admin.categories.index')->with('status', 'Category deleted.');
    }

    private function validated(Request $request, ?Category $category = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($category?->id)],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);
    }
}
