@extends('layouts.layout')

@section('title', 'Shop All Products — BeastWear.pk')

@section('meta')
    <meta name="description" content="Browse the full BeastWear.pk collection — t-shirts, oversized tees, hoodies, sweatshirts, trousers and kids clothing. Filter by size, price and availability.">
@endsection

@section('content')

<section class="page-hero">
    <div class="wrap">
      <div class="crumbs"><a href="index.html">Home</a><i>/</i><span>Shop</span></div>
      <h1>Shop All</h1>
      <p>The complete BeastWear.pk collection — heavy cotton basics, oversized fits and winter layers, all with Cash on Delivery.</p>
    </div>
  </section>

  <section class="section-sm">
    <div class="wrap">
      <div class="shop-layout">
        <!-- ===== SIDEBAR FILTERS (desktop) ===== -->
        <aside class="sidebar-filters">
          <div class="panel" style="padding:18px 20px">
            <h3 style="font-size:13px;letter-spacing:.18em;text-transform:uppercase;margin-bottom:6px">Filters</h3>
            <div data-filters></div>
          </div>
        </aside>

        <!-- ===== PRODUCT LIST ===== -->
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
                <option value="rating">Top Rated</option>
              </select>
            </div>
          </div>

          <div class="chips" id="active-chips" style="margin-bottom:22px"></div>
          <div class="product-grid" id="shop-grid"></div>
          <div class="pager" id="pager"></div>
        </div>
      </div>
    </div>
  </section>

  <section class="cta-strip">
    <div class="wrap text-center">
      <h2>Not Sure About Your Size?</h2>
      <p>Check our detailed size guide before ordering — and remember, exchanges are free within 7 days.</p>
      <a class="btn btn-light" href="size-guide.html">Open Size Guide</a>
    </div>
  </section>

@endsection

@section('scripts')
<script>
    window.BW_SHOP_PRODUCT_IDS = {{ \Illuminate\Support\Js::from($shopProductIds) }};
</script>
<script src="{{ asset('js/search.js') }}"></script>
<script src="{{ asset('js/shop.js') }}"></script>
@endsection
