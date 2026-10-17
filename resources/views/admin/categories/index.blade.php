@extends('admin.layouts.layout')

@section('title', 'Categories | BeastWear.pk Admin')
@section('page', 'categories')

@section('content')
<div class="page-header">
    <div><div class="breadcrumb"><a href="{{ route('admin.dashboard') }}">Home</a><span class="sep">/</span><span>Categories</span></div><h1>Categories</h1><p class="subtitle">{{ $categories->count() }} categories</p></div>
    <div class="page-actions"><a class="btn btn-primary" href="{{ route('admin.categories.form') }}">+ Add Category</a></div>
</div>
@if (session('status'))<div class="alert alert-warning" role="status">{{ session('status') }}</div>@endif
<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Image</th><th>Category</th><th>Products</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse ($categories as $category)
                <tr>
                    <td>@if ($category->image)<img class="product-thumb" src="{{ asset($category->image) }}" alt="{{ $category->name }}">@endif</td>
                    <td><strong>{{ $category->name }}</strong><div class="small mono">{{ $category->slug }}</div></td>
                    <td>{{ $category->products_count }}</td>
                    <td>{{ ucfirst($category->status) }}</td>
                    <td>
                        <a class="btn btn-outline btn-sm" href="{{ route('admin.categories.form', ['id' => $category->id]) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" style="display:inline" onsubmit="return confirm('Delete this category?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">No categories yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
