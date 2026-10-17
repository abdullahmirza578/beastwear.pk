@extends('layouts.layout')

@section('title', 'About Us — BeastWear.pk')

@section('meta')
    <meta name="description" content="Learn about BeastWear.pk and browse the products currently available in our store.">
@endsection

@section('content')

<section class="hero" style="min-height:56vh">
    <div class="wrap"><div class="hero-inner" style="padding:70px 0">
      <h1 style="font-size:clamp(44px,7vw,88px)">About BeastWear.pk</h1>
      <p>Explore the clothing and collections currently available in our store.</p>
      <a class="btn btn-light" href="{{ route('shop') }}">Browse Products</a>
    </div></div>
</section>

<section class="section">
    <div class="wrap wrap-sm">
      <span class="eyebrow">Our store</span>
      <h2>Find Your Everyday Style</h2>
      <p>BeastWear.pk brings its available products together in one place. Browse the shop to see current prices, product details, sizes, colors, and stock availability.</p>
      <p>Product information and availability are shown on each product page. You can review the details before adding an item to your cart.</p>
    </div>
</section>

<section class="section bg-off">
    <div class="wrap wrap-sm">
      <span class="eyebrow">Shopping with BeastWear</span>
      <h2>Browse, Choose, Order</h2>
      <div class="grid g-3 mt-l">
        <div class="info-box"><h4>Browse</h4><p style="margin:0">Explore the products and categories currently listed in the shop.</p></div>
        <div class="info-box"><h4>Choose</h4><p style="margin:0">Check each product's description, price, available options, and stock.</p></div>
        <div class="info-box"><h4>Order</h4><p style="margin:0">Add your selection to the cart and continue to checkout.</p></div>
      </div>
      <div class="row mt-l" style="justify-content:center">
        <a class="btn btn-dark" href="{{ route('shop') }}">Shop Products</a>
        <a class="btn btn-outline" href="{{ route('contact') }}">Contact Us</a>
      </div>
    </div>
</section>

<section class="cta-strip">
    <div class="wrap text-center">
      <h2>Wear Your Beast</h2>
      <p>View the products currently available at BeastWear.pk.</p>
      <a class="btn btn-light" href="{{ route('shop') }}">Shop Now</a>
    </div>
</section>

@endsection
