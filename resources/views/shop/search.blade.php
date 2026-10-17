@extends('layouts.layout')

@section('title', 'Search — BeastWear.pk')

@section('meta')
    <meta name="description" content="Search active BeastWear.pk products by name, SKU or category.">
@endsection

@section('content')

<section class="page-hero">
    <div class="wrap">
      <div class="crumbs"><a href="index.html">Home</a><i>/</i><span>Search</span></div>
      <h1>Search Products</h1>
      <p>Search active products by name, SKU or category.</p>

      <form id="page-search-form" class="row" method="GET" action="{{ route('search') }}" style="margin-top:22px;max-width:640px;gap:10px">
        <input class="input" type="text" id="page-search-input" name="q" value="{{ $searchTerm }}" placeholder="What are you looking for?" style="flex:1;min-width:200px;padding:14px 16px">
        <button class="btn btn-dark" type="submit">Search</button>
      </form>
    </div>
  </section>

  <section class="section-sm">
    <div class="wrap">
      <div class="toolbar" style="border-bottom:0;padding-bottom:6px;margin-bottom:18px">
        <span class="count">Results for <b id="search-term">{{ $searchTerm !== '' ? $searchTerm : 'all products' }}</b></span>
        <span class="count" id="search-count">{{ $searchResultCount }} {{ \Illuminate\Support\Str::plural('product', $searchResultCount) }} found</span>
      </div>
      <div class="product-grid" id="search-grid"></div>

      <div class="mt-l">
        <span class="eyebrow">Popular searches</span>
        <div class="search-tags">
          <a class="chip" href="{{ route('search', ['q' => 'oversized']) }}">Oversized Tee</a>
          <a class="chip" href="{{ route('search', ['q' => 'hoodie']) }}">Hoodie</a>
          <a class="chip" href="{{ route('search', ['q' => 'sweatshirt']) }}">Sweatshirt</a>
          <a class="chip" href="{{ route('search', ['q' => 'cargo']) }}">Cargo Trousers</a>
          <a class="chip" href="{{ route('search', ['q' => 'kids']) }}">Kids Collection</a>
        </div>
      </div>
    </div>
  </section>

@endsection

@section('scripts')
<script>
    window.BW_SEARCH_PRODUCT_IDS = {{ \Illuminate\Support\Js::from($searchProductIds) }};
    window.BW_SEARCH_RESULT_COUNT = {{ $searchResultCount }};
</script>
<script src="{{ asset('js/search.js') }}"></script>
@endsection
