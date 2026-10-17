@php
    $editing = $product->exists;
    $variantSizes = $product->variants->pluck('size')->filter()->unique()->implode(', ');
    $variantColors = $product->variants->pluck('color')->filter()->unique()->implode(', ');
    $variantStock = $product->variants->first()?->stock_quantity ?? 0;
    $variantAdditionalPrice = $product->variants->first()?->additional_price ?? 0;
    $designEnabled = (bool) old('design_selection_enabled', $product->design_selection_enabled);
    $sizeEnabled = (bool) old('size_selection_enabled', $product->size_selection_enabled);
    $colorEnabled = (bool) old('color_selection_enabled', $product->color_selection_enabled);
@endphp
<form method="POST" action="{{ $editing ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data">
    @csrf
    @if ($editing) @method('PUT') @endif
    @if ($errors->any())
        <div class="alert alert-warning" role="alert">{{ $errors->first() }}</div>
    @endif
    <div class="page-header">
        <div>
            <div class="breadcrumb"><a href="{{ route('admin.dashboard') }}">Home</a><span class="sep">/</span><a href="{{ route('admin.products.index') }}">Products</a><span class="sep">/</span><span>{{ $editing ? 'Edit' : 'Add' }}</span></div>
            <h1>{{ $editing ? 'Edit Product' : 'Add New Product' }}</h1>
            <p class="subtitle">Manage the product information saved to the store catalogue.</p>
        </div>
        <div class="page-actions">
            <a class="btn btn-outline" href="{{ route('admin.products.index') }}">Cancel</a>
            <button class="btn btn-primary" type="submit">{{ $editing ? 'UPDATE PRODUCT' : 'SAVE PRODUCT' }}</button>
        </div>
    </div>

    <div class="grid grid-2-1">
        <div>
            <div class="card">
                <div class="card-header"><div class="card-title">Basic Information</div></div>
                <div class="card-body">
                    <div class="form-group"><label class="form-label" for="product-name">Product Name</label><input class="form-control" id="product-name" name="name" value="{{ old('name', $product->name) }}" required></div>
                    <div class="form-group"><label class="form-label" for="product-sku">SKU</label><input class="form-control mono" id="product-sku" name="sku" value="{{ old('sku', $product->sku) }}" required></div>
                    <div class="form-group"><label class="form-label" for="product-category">Category</label><select class="form-control" id="product-category" name="category_id"><option value="">Uncategorised</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
                    <div class="form-group"><label class="form-label" for="product-short-description">Short Description</label><input class="form-control" id="product-short-description" name="short_description" value="{{ old('short_description', $product->short_description) }}" maxlength="500"></div>
                    <div class="form-group"><label class="form-label" for="product-description">Description</label><textarea class="form-control" id="product-description" name="description" rows="5">{{ old('description', $product->description) }}</textarea></div>
                </div>
            </div>
            <div class="card mt-24">
                <div class="card-header"><div class="card-title">Pricing &amp; Stock</div></div>
                <div class="card-body">
                    <div class="form-row cols-3">
                        <div class="form-group"><label class="form-label">Price (Rs.)</label><input type="number" class="form-control" name="price" min="1" step="0.01" value="{{ old('price', $product->price) }}" required></div>
                        <div class="form-group"><label class="form-label">Sale Price (Rs.)</label><input type="number" class="form-control" name="sale_price" min="0" step="0.01" value="{{ old('sale_price', $product->sale_price) }}"></div>
                        <div class="form-group"><label class="form-label">Cost Price (Rs.)</label><input type="number" class="form-control" name="cost_price" min="0" step="0.01" value="{{ old('cost_price', $product->cost_price) }}"></div>
                    </div>
                    <div class="form-group"><label class="form-label">Product stock quantity</label><input type="number" class="form-control" name="stock_quantity" min="0" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}" required></div>
                </div>
            </div>
            <div class="card mt-24">
                <div class="card-header"><div class="card-title">Clothing Variants<small>Comma-separated values are combined into size/color variants.</small></div></div>
                <div class="card-body">
                    <div class="form-group">
                        <input type="hidden" name="size_selection_enabled" value="0">
                        <label class="switch"><input type="checkbox" name="size_selection_enabled" value="1" @checked($sizeEnabled)><span class="track"></span><span class="switch-label">Enable Size Selection</span></label>
                    </div>
                    <div class="form-group"><label class="form-label">Sizes</label><input class="form-control" name="variant_sizes" value="{{ old('variant_sizes', $variantSizes) }}" placeholder="S, M, L, XL, XXL"></div>
                    <div class="form-group">
                        <input type="hidden" name="color_selection_enabled" value="0">
                        <label class="switch"><input type="checkbox" name="color_selection_enabled" value="1" @checked($colorEnabled)><span class="track"></span><span class="switch-label">Enable Color Selection</span></label>
                    </div>
                    <div class="form-group"><label class="form-label">Colors</label><input class="form-control" name="variant_colors" value="{{ old('variant_colors', $variantColors) }}" placeholder="Black, White"></div>
                    <div class="form-row">
                        <div class="form-group"><label class="form-label">Stock for each size/color</label><input type="number" class="form-control" name="variant_stock" min="0" value="{{ old('variant_stock', $variantStock) }}"></div>
                        <div class="form-group"><label class="form-label">Additional price per variant</label><input type="number" class="form-control" name="variant_additional_price" min="0" step="0.01" value="{{ old('variant_additional_price', $variantAdditionalPrice) }}"></div>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <div class="card">
                <div class="card-header"><div class="card-title">Product Images</div></div>
                <div class="card-body">
                    <div class="form-group">
                        <input type="hidden" name="design_selection_enabled" value="0">
                        <label class="switch"><input type="checkbox" name="design_selection_enabled" value="1" @checked($designEnabled)><span class="track"></span><span class="switch-label">Enable Design Selection</span></label>
                        <small>Design options use only images explicitly checked below. Ordinary gallery images stay in the gallery.</small>
                    </div>
                    @if ($product->images->isNotEmpty())
                        <div class="upload-preview mb-16">
                            @foreach ($product->images as $image)
                                <div style="display:inline-grid;gap:6px;max-width:125px;margin:4px">
                                    <label style="display:inline-flex;align-items:center;gap:6px">
                                        <input type="radio" name="primary_image_id" value="{{ $image->id }}" @checked(old('primary_image_id', $product->images->firstWhere('is_primary', true)?->id) == $image->id)>
                                        <span>{{ $image->is_primary ? 'Primary' : 'Set primary' }}</span>
                                    </label>
                                    <img src="{{ $image->url }}" alt="{{ $product->name }}" width="110">
                                    <label style="display:inline-flex;align-items:center;gap:6px">
                                        <input type="checkbox" name="design_image_ids[]" value="{{ $image->id }}" @checked(in_array($image->id, old('design_image_ids', $product->selectableDesigns->pluck('id')->all())))>
                                        <span>Selectable design</span>
                                    </label>
                                    <input class="form-control" name="design_names[{{ $image->id }}]" value="{{ old('design_names.'.$image->id, $image->design_name) }}" maxlength="100" placeholder="Design name">
                                </div>
                            @endforeach
                        </div>
                        <small>Upload a design in the gallery first, then mark it as a selectable design here.</small>
                    @endif
                    <div class="form-group"><label class="form-label" for="main-image">Main Image</label><input class="form-control" type="file" id="main-image" name="main_image" accept="image/*"><small>Optional · JPG, PNG or WebP up to 5 MB</small></div>
                    <div class="form-group"><label class="form-label" for="gallery-images">Gallery Images</label><input class="form-control" type="file" id="gallery-images" name="gallery_images[]" accept="image/*" multiple><small>Optional · up to 10 images</small></div>
                    <div class="form-group"><label class="form-label" for="design-images">Selectable Design Images</label><input class="form-control" type="file" id="design-images" name="design_images[]" accept="image/*" multiple><small>These uploads become customer-selectable designs. The file name is used as the design name; you can edit it after saving.</small></div>
                </div>
            </div>
            <div class="card mt-24">
                <div class="card-header"><div class="card-title">Visibility</div></div>
                <div class="card-body">
                    <div class="form-group"><label class="form-label" for="product-status">Status</label><select class="form-control" id="product-status" name="status"><option value="active" @selected(old('status', $product->status ?? 'draft') === 'active')>Active</option><option value="inactive" @selected(old('status', $product->status) === 'inactive')>Inactive</option><option value="draft" @selected(old('status', $product->status ?? 'draft') === 'draft')>Draft</option></select></div>
                    <label class="switch"><input type="checkbox" name="featured" value="1" @checked(old('featured', $product->featured))><span class="track"></span><span class="switch-label">Featured product</span></label>
                </div>
            </div>
            @if ($editing)
                <div class="card mt-24">
                    <div class="card-body">
                        <p class="form-hint">Product ID: {{ $product->id }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="sticky-actions"><a class="btn btn-outline" href="{{ route('admin.products.index') }}">Cancel</a><button class="btn btn-primary btn-lg" type="submit">{{ $editing ? 'UPDATE PRODUCT' : 'SAVE PRODUCT' }}</button></div>
</form>
@if ($editing)
    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="mt-16" onsubmit="return confirm('Delete this product?')">
        @csrf
        @method('DELETE')
        <button class="btn btn-danger" type="submit">Delete Product</button>
    </form>
@endif
