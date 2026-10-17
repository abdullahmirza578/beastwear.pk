@extends('layouts.layout')

@section('title', 'BeastWear.pk — Wear Your Beast | Premium Clothing in Pakistan')

@section('meta')
    <meta name="description" content="BeastWear.pk — premium quality, affordable t-shirts, oversized tees, hoodies, sweatshirts and trousers for men and kids. Cash on Delivery across Pakistan.">
@endsection

@section('content')

<!-- ================= HERO ================= -->
@php
  $heroImage = $heroBanner?->image_url;
  $heroMobileImage = $heroBanner?->mobile_image_url ?: $heroImage;
@endphp
  <section class="hero">
    @if ($heroImage)
      <div class="hero-media">
        <picture style="display:block;width:100%;height:100%">
          <source media="(max-width: 767px)" srcset="{{ $heroMobileImage }}">
          <img src="{{ $heroImage }}" alt="{{ $heroBanner?->title ?: 'BeastWear banner' }}" fetchpriority="high">
        </picture>
      </div>
    @endif
    <div class="wrap">
      <div class="hero-inner">
        @if (! $heroBanner)
        @endif
        <h1>{{ $heroBanner?->title ?: 'Wear Your Beast' }}</h1>
        <p>{{ $heroBanner?->subtitle ?: 'Modern essentials built for your everyday style. Heavyweight fabrics, clean silhouettes, honest prices — delivered anywhere in Pakistan.' }}</p>
        <div class="hero-btns">
          <a class="btn btn-light" href="{{ $heroBanner?->button_url ?: route('shop') }}">{{ $heroBanner?->button_text ?: 'Shop Now' }}</a>
          <a class="btn btn-ghost-light" href="category.html">Explore Collection</a>
        </div>
      </div>
    </div>
  </section>

  <div class="hero-strip"><div class="wrap">
    <span>{{ $shippingSettings->cod_enabled ? 'Cash on Delivery' : 'Prepaid orders only' }}</span>
    <span>Free shipping over Rs {{ number_format($shippingSettings->free_threshold) }}</span>
    <span>{{ $shippingSettings->nationwide ? 'Pakistan-wide delivery' : 'Delivery currently unavailable' }}</span>
  </div></div>

  @foreach ($promotionalBanners->where('position', 'Below Hero') as $promo)
    @include('home.partials.promo-banner', ['promo' => $promo])
  @endforeach

  <!-- ================= CATEGORIES ================= -->
  <section class="section">
    <div class="wrap">
      <div class="sec-head reveal">
        <div><span class="eyebrow">Shop by category</span><h2>Pick Your Style</h2></div>
        <a class="link-arrow" href="{{ route('shop') }}">All Products</a>
      </div>
      <div class="cat-grid reveal" id="cat-grid"></div>
    </div>
  </section>

  <!-- ================= NEW ARRIVALS ================= -->
  <section class="section bg-off">
    <div class="wrap">
      <div class="sec-head reveal">
        <div><span class="eyebrow">Just landed</span><h2>New Arrivals</h2></div>
        <a class="link-arrow" href="{{ route('shop') }}">View All Products</a>
      </div>
      <div class="product-grid reveal" id="new-arrivals"></div>
    </div>
  </section>

  <!-- ================= BEST SELLERS ================= -->
  <section class="section">
    <div class="wrap">
      <div class="sec-head reveal">
        <div><span class="eyebrow">Customer favourites</span><h2>Best Sellers</h2></div>
        <a class="link-arrow" href="{{ route('shop') }}">View All Products</a>
      </div>
      <div class="product-grid reveal" id="best-sellers"></div>
    </div>
  </section>

  @foreach ($promotionalBanners->where('position', 'Mid Page') as $promo)
    @include('home.partials.promo-banner', ['promo' => $promo])
  @endforeach

  <!-- ================= WHY BEASTWEAR ================= -->
  <section class="section bg-off">
    <div class="wrap">
      <div class="sec-head reveal">
      <div><span class="eyebrow">Shop BeastWear</span><h2>Product Details at a Glance</h2>
      <p>Review current product information and availability before placing an order.</p></div>
      </div>
      <div class="feature-grid reveal">
      <div class="feature"><div class="feature-ico" data-ico="tag"></div><h4>Current Prices</h4><p>Product pages show current prices and any sale prices.</p></div>
      <div class="feature"><div class="feature-ico" data-ico="shield"></div><h4>Product Details</h4><p>Review descriptions and product information before ordering.</p></div>
      <div class="feature"><div class="feature-ico" data-ico="check"></div><h4>Available Options</h4><p>See sizes, colors, and stock where product options are available.</p></div>
      <div class="feature"><div class="feature-ico" data-ico="cash"></div><h4>Checkout Options</h4><p>Available payment options are shown during checkout.</p></div>
      </div>
    </div>
  </section>

  <!-- ================= TWO-UP CTA ================= -->
  <section class="section">
    <div class="wrap two-col">
      <div class="reveal">
        <span class="eyebrow">Our promise</span>
        <h2>Find Your Next<br>BeastWear Pick</h2>
        <p>Browse the live catalog for products currently available and open any product to see its details.</p>
        <ul style="margin-top:18px">
          <li class="icon-line" style="margin-bottom:14px"><span class="ico" data-ico="check"></span><div><h4>Product descriptions</h4><p>Review the listed information before choosing an item.</p></div></li>
          <li class="icon-line" style="margin-bottom:14px"><span class="ico" data-ico="check"></span><div><h4>Size and color options</h4><p>Available product variants appear on the product page.</p></div></li>
          <li class="icon-line"><span class="ico" data-ico="check"></span><div><h4>Live catalog</h4><p>The shop displays active products from the catalog.</p></div></li>
        </ul>
        <a class="btn btn-dark mt-m" href="{{ route('about') }}">About BeastWear.pk</a>
      </div>
    </div>
  </section>

  @foreach ($promotionalBanners->where('position', 'Above Footer') as $promo)
    @include('home.partials.promo-banner', ['promo' => $promo])
  @endforeach

  @foreach ($promotionalBanners->where('position', 'Footer') as $promo)
    @include('home.partials.promo-banner', ['promo' => $promo])
  @endforeach

@endsection

@section('scripts')
<script src="{{ asset('js/home.js') }}"></script>
<script>
  document.querySelectorAll('.reveal').forEach(function (element) {
    element.classList.add('in');
  });
</script>
@endsection
