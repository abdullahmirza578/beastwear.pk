@extends('admin.layouts.layout')

@section('title', 'Products | BeastWear.pk Admin')
@section('page', 'products')

@section('content')
<div class="page-header">
    <div><div class="breadcrumb"><a href="{{ route('admin.dashboard') }}">Home</a><span class="sep">/</span><span>Products</span></div><h1>Products</h1><p class="subtitle">{{ $products->total() }} products in the database</p></div>
    <div class="page-actions"><a class="btn btn-outline" href="{{ route('admin.categories.index') }}">Categories</a><a class="btn btn-primary" href="{{ route('admin.products.create') }}">+ ADD PRODUCT</a></div>
</div>
@if (session('status'))
    <div class="alert alert-warning" role="status">{{ session('status') }}</div>
@endif
<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Image</th><th>Product</th><th>SKU</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse ($products as $product)
                @php($image = $product->images->firstWhere('is_primary', true) ?? $product->images->first())
                @php($imageUrl = $image?->url)
                <tr>
                    <td>@if ($imageUrl)<img class="product-thumb" src="{{ $imageUrl }}" alt="{{ $product->name }}">@else<span class="product-thumb" aria-label="No product image available"></span>@endif</td>
                    <td><strong>{{ $product->name }}</strong>@if ($product->featured)<div class="small">Featured</div>@endif</td>
                    <td class="mono">{{ $product->sku }}</td>
                    <td>{{ $product->category?->name ?? '—' }}</td>
                    <td>@if ($product->sale_price !== null && $product->sale_price < $product->price)<s>Rs {{ number_format((float) $product->price, 0) }}</s> @endif Rs {{ number_format((float) ($product->sale_price ?? $product->price), 0) }}</td>
                    <td>{{ $product->variants->isNotEmpty() ? $product->variants->sum('stock_quantity') : $product->stock_quantity }}</td>
                    <td>{{ ucfirst($product->status) }}</td>
                    <td>
                        <a class="btn btn-outline btn-sm" href="{{ route('admin.products.edit', ['id' => $product->id]) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" style="display:inline" onsubmit="return confirm('Delete this product?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8">No products found. Add the first product to start building the catalog.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $products->links() }}
</div>
@endsection
