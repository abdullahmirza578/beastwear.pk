@extends('admin.layouts.layout')

@section('title', 'Category | BeastWear.pk Admin')
@section('page', 'category-form')

@section('content')
@php($editing = $category->exists)
<form method="POST" action="{{ $editing ? route('admin.categories.update', $category) : route('admin.categories.store') }}" enctype="multipart/form-data">
    @csrf
    @if ($editing) @method('PUT') @endif
    <div class="page-header">
        <div><div class="breadcrumb"><a href="{{ route('admin.dashboard') }}">Home</a><span class="sep">/</span><a href="{{ route('admin.categories.index') }}">Categories</a></div><h1>{{ $editing ? 'Edit Category' : 'Add Category' }}</h1><p class="subtitle">Categories organise the storefront collections.</p></div>
        <div class="page-actions"><a class="btn btn-outline" href="{{ route('admin.categories.index') }}">Cancel</a><button class="btn btn-primary" type="submit">SAVE CATEGORY</button></div>
    </div>
    @if ($errors->any())<div class="alert alert-warning" role="alert">{{ $errors->first() }}</div>@endif
    <div class="grid grid-2-1">
        <div class="card">
            <div class="card-header"><div class="card-title">Category Details</div></div>
            <div class="card-body">
                <div class="form-group"><label class="form-label" for="category-name">Name</label><input class="form-control" id="category-name" name="name" value="{{ old('name', $category->name) }}" required></div>
                <div class="form-group"><label class="form-label" for="category-slug">Slug</label><input class="form-control mono" id="category-slug" name="slug" value="{{ old('slug', $category->slug) }}" placeholder="generated from name"></div>
                <div class="form-group"><label class="form-label" for="category-description">Description</label><textarea class="form-control" id="category-description" name="description">{{ old('description', $category->description) }}</textarea></div>
                <div class="form-group"><label class="form-label" for="category-status">Status</label><select class="form-control" id="category-status" name="status"><option value="active" @selected(old('status', $category->status ?? 'active') === 'active')>Active</option><option value="inactive" @selected(old('status', $category->status) === 'inactive')>Inactive</option></select></div>
                <div class="form-group"><label class="form-label" for="category-image">Category Image</label><input class="form-control" type="file" id="category-image" name="image" accept="image/*">@if ($category->image)<img src="{{ asset($category->image) }}" alt="{{ $category->name }}" width="150" class="mt-16">@endif</div>
            </div>
        </div>
    </div>
    <div class="sticky-actions"><a class="btn btn-outline" href="{{ route('admin.categories.index') }}">Cancel</a><button class="btn btn-primary btn-lg" type="submit">SAVE CATEGORY</button></div>
</form>
@if ($editing)
    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="mt-16" onsubmit="return confirm('Delete this category? Products will become uncategorised.')">
        @csrf
        @method('DELETE')
        <button class="btn btn-danger" type="submit">Delete Category</button>
    </form>
@endif
@endsection
