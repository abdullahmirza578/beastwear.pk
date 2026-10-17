@extends('layouts.layout')

@section('title', 'My Wishlist — BeastWear.pk')

@section('meta')
    <meta name="description" content="Products you saved at BeastWear.pk — add them to your cart whenever you are ready.">
@endsection

@section('main-class', 'section-sm')

@section('content')

<div class="wrap">
    <div class="crumbs"><a href="index.html">Home</a><i>/</i><span>Wishlist</span></div>
    <div class="sec-head"><div><span class="eyebrow">Saved items</span><h2>My Wishlist</h2></div>
      <span class="hint"><b data-wish-count>{{ $products->total() }}</b> product(s) saved</span></div>

    <div class="product-grid" id="wishlist-items">
      @forelse ($products as $product)
        @include('partials.product-card', ['product' => $product])
        <form method="POST" action="{{ route('wishlist.destroy', $product) }}">
          @csrf
          @method('DELETE')
          <button class="btn btn-outline btn-sm" type="submit">Remove from wishlist</button>
        </form>
      @empty
        <div class="empty" style="grid-column:1/-1">
          <h3>Your wishlist is empty</h3>
          <p>Tap the heart on any product to save it for later.</p>
          <a class="btn btn-dark" href="{{ route('shop') }}">Browse Products</a>
        </div>
      @endforelse
    </div>

    <div class="info-box mt-l">
      <h4>How the wishlist works</h4>
      <p style="margin:0">Saved products are connected to your BeastWear.pk account and stay available when you sign in again.</p>
    </div>
  </div>

@endsection
