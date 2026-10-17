@extends('layouts.layout')

@section('title', ($category?->name ?? 'Categories') . ' — BeastWear.pk')

@section('meta')
    <meta name="description" content="{{ $category?->description ?: 'Browse active BeastWear.pk products by category.' }}">
@endsection

@section('content')

<!-- Category banner (updated by js/shop.js based on ?cat=) -->
  @php
    $categoryImage = \App\Models\ProductImage::urlFor($category?->image);
  @endphp
  <section style="position:relative;min-height:290px;display:flex;align-items:flex-end;background:var(--black);overflow:hidden">
    @if ($categoryImage)<img id="cat-banner-img" src="{{ $categoryImage }}" alt="{{ $category?->name }}" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.5">@endif
    <div class="wrap" style="position:relative;z-index:2;padding-top:60px;padding-bottom:34px">
      <div class="crumbs" style="color:#b9b8b4"><a href="{{ route('home') }}" style="color:#b9b8b4">Home</a><i>/</i><a href="{{ route('category') }}" style="color:#b9b8b4">Categories</a><i>/</i><span id="crumb-cat" style="color:#fff">{{ $category?->name ?? 'All' }}</span></div>
      <h1 id="cat-title" style="color:#fff;font-family:var(--font-display);font-weight:400;text-transform:uppercase;letter-spacing:.02em;font-size:clamp(30px,4.6vw,52px);margin:0 0 8px">{{ $category?->name ?? 'All Categories' }}</h1>
      <p id="cat-desc" style="color:#c9c8c4;max-width:620px;margin:0">{{ $category?->description ?? 'Choose a category below to explore the BeastWear.pk collection.' }}</p>
    </div>
  </section>

  <!-- Category chips -->
  <section class="section-sm" style="padding-bottom:0">
    <div class="wrap">
      <div class="chips" id="cat-chips"></div>
    </div>
  </section>

  <!-- Products in this category -->
  <section class="section-sm">
    <div class="wrap">
      <div class="shop-layout">
        <aside class="sidebar-filters">
          <div class="panel" style="padding:18px 20px">
            <h3 style="font-size:13px;letter-spacing:.18em;text-transform:uppercase;margin-bottom:6px">Refine</h3>
            <div data-filters></div>
          </div>
        </aside>

        <div>
          <div class="toolbar">
            <div class="row-center">
              <button class="btn btn-outline btn-sm filter-toggle" data-open-filters>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="15" height="15"><path d="M4 6h16M7 12h10M10 18h4"/></svg>
                Filters
              </button>
              <span class="count" id="shop-count">Loading products...</span>
            </div>
            <div class="row-center">
              <label class="hint" for="sort-select">Sort by</label>
              <select class="select" id="sort-select">
                <option value="featured">Featured</option>
                <option value="newest">Newest</option>
                <option value="popular">Most Popular</option>
                <option value="price-asc">Price: Low to High</option>
                <option value="price-desc">Price: High to Low</option>
              </select>
            </div>
          </div>

          <div class="product-grid" id="shop-grid"></div>
          <div class="pager" id="pager"></div>
        </div>
      </div>
    </div>
  </section>

  <!-- All categories grid -->
  <section class="section bg-off">
    <div class="wrap">
      <div class="sec-head"><div><span class="eyebrow">Browse</span><h2>All Categories</h2></div></div>
      <div class="cat-grid" id="all-cats"></div>
    </div>
  </section>

@endsection

@section('scripts')
<script>
window.BW_SHOP_PRODUCT_IDS = {{ \Illuminate\Support\Js::from($categoryProductIds) }};
</script>
<script src="{{ asset('js/shop.js') }}"></script>
<script>
/* Category page only: build the "all categories" grid at the bottom */
document.addEventListener("DOMContentLoaded", function () {
  var box = document.getElementById("all-cats");
  if (box) box.innerHTML = BW_CATEGORIES.map(function (c) {
    return '<a class="cat-card" href="/category?cat=' + encodeURIComponent(c.slug) + '">' +
      '<img src="' + IMG_CARD(c.image) + '" alt="' + c.name + '" loading="lazy">' +
      "<span>" + c.name + "<i>→</i></span></a>";
  }).join("");
});
</script>
@endsection
