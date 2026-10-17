@extends('layouts.layout')

@section('title', 'Shopping Cart — BeastWear.pk')

@section('meta')
    <meta name="description" content="Review your BeastWear.pk cart, update quantities and proceed to checkout with Cash on Delivery.">
@endsection

@section('main-class', 'section-sm')

@section('styles')
<style>
  .cart-row { grid-template-columns: 24px 100px minmax(0, 1fr) auto; }
  @media (max-width: 600px) {
    .cart-row { grid-template-columns: 24px 84px minmax(0, 1fr); }
  }
</style>
@endsection

@section('content')

<div class="wrap">
    <div class="crumbs"><a href="{{ route('home') }}">Home</a><i>/</i><span>Cart</span></div>
    @if (session('status'))
      <div class="alert alert-ok" role="status">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
      <div class="alert alert-err" role="alert">{{ $errors->first() }}</div>
    @endif
    <div class="sec-head"><div><span class="eyebrow">Your bag</span><h2>Shopping Cart</h2></div>
      <span class="hint"><b data-cart-label>{{ $cart->items->sum('quantity') }}</b> item(s) in cart</span></div>

    <div class="cart-layout">
      <!-- ============ CART ITEMS ============ -->
      <div class="panel" id="cart-items-panel">
        @if ($cart->items->isNotEmpty())
          <div class="row-between" style="padding-bottom:14px;border-bottom:1px solid var(--line)">
            <label class="row-center" style="gap:9px">
              <input type="checkbox" id="cart-select-all" checked aria-label="Select all cart products">
              <span>Select all ({{ $cart->items->count() }} product(s))</span>
            </label>
            <form id="delete-selected-form" method="POST" action="{{ route('cart.items.destroy-selected') }}" data-confirm="Remove selected products from your cart?">
              @csrf
              @method('DELETE')
              <button class="btn btn-outline btn-sm" type="submit" id="delete-selected-button" disabled>Delete selected</button>
            </form>
          </div>
        @endif
        @forelse ($cart->items as $item)
          <div class="cart-row">
            <label style="display:flex;align-items:center;justify-content:center">
              <input type="checkbox" class="cart-item-select" name="selected_items[]" value="{{ $item->id }}" form="delete-selected-form" checked aria-label="Select {{ $item->product->name }}">
            </label>
            <a class="cart-thumb" href="{{ route('products.show', $item->product->slug) }}">
              @php
                $cartImage = ($item->product->images->firstWhere('is_primary', true) ?? $item->product->images->first())?->url;
              @endphp
              @if ($cartImage)
                <img src="{{ $cartImage }}" alt="{{ $item->product->name }}">
              @else
                <span class="product-image-placeholder" aria-label="No product image available"></span>
              @endif
            </a>
            <div class="cart-meta">
              <div class="card-cat">{{ $item->product->category?->name }}</div>
              <h4><a href="{{ route('products.show', $item->product->slug) }}">{{ $item->product->name }}</a></h4>
              @if ($item->variant?->size || $item->variant?->color)
                <div class="vars">
                  @if ($item->variant?->size) Size: <strong>{{ $item->variant->size }}</strong>@endif
                  @if ($item->variant?->size && $item->variant?->color) · @endif
                  @if ($item->variant?->color) Color: <strong>{{ $item->variant->color }}</strong>@endif
                </div>
              @endif
              @if ($item->selectedDesign)
                <div class="vars">Design: <strong>{{ $item->selectedDesign->design_name }}</strong></div>
              @endif
              <div class="vars">{{ number_format((float) $item->price, 0) }} each</div>
            </div>
            <div class="cart-side">
              <form method="POST" action="{{ route('cart.items.update', $item) }}" class="qty-box">
                @csrf
                @method('PATCH')
                <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="20" aria-label="Quantity">
                <button type="submit" aria-label="Update quantity">Update</button>
              </form>
              <div class="price"><b>Rs {{ number_format((float) $item->price * $item->quantity, 0) }}</b></div>
              <form method="POST" action="{{ route('cart.items.destroy', $item) }}">
                @csrf
                @method('DELETE')
                <button class="remove-btn" type="submit">Remove</button>
              </form>
            </div>
          </div>
        @empty
          <div class="empty">
            <h3>Your cart is empty</h3>
            <p>Explore the latest BeastWear drops.</p>
            <a class="btn btn-dark" href="{{ route('shop') }}">Start Shopping</a>
          </div>
        @endforelse
        <div class="row-between mt-m">
          <a class="btn btn-outline btn-sm" href="{{ route('shop') }}">Continue Shopping</a>
          @if ($cart->items->isNotEmpty())
            <form method="POST" action="{{ route('cart.clear') }}">
              @csrf
              @method('DELETE')
              <button class="btn btn-outline btn-sm" type="submit">Clear Cart</button>
            </form>
          @endif
        </div>
      </div>

      <!-- ============ ORDER SUMMARY ============ -->
      <aside class="panel panel-dark" id="cart-summary" @if ($cart->items->isEmpty()) hidden @endif>
        <h3>Order Summary</h3>
        @php
          $delivery = $shippingSettings->deliveryFee($subtotal);
          $codFee = $shippingSettings->cod_enabled ? $shippingSettings->cod_fee : 0;
        @endphp
        <div class="sum-line"><span>Subtotal ({{ $cart->items->sum('quantity') }} items)</span><b style="color:#fff">Rs {{ number_format($subtotal, 0) }}</b></div>
        @if ($subtotal < $siteSettings->min_order)
          <p class="hint">Add Rs {{ number_format($siteSettings->min_order - $subtotal) }} more to meet the minimum order.</p>
        @endif
        <div class="sum-line"><span>Delivery</span><span>{{ $delivery ? 'Rs '.number_format($delivery, 0) : 'FREE' }}</span></div>
        @if ($codFee)
          <div class="sum-line"><span>COD handling</span><span>Rs {{ number_format($codFee, 0) }}</span></div>
        @endif
        <div class="sum-line total"><span>Total</span><span>Rs {{ number_format($subtotal + $delivery + $codFee, 0) }}</span></div>
        <p class="hint" style="color:#9a9995;margin:8px 0 0">Use a valid coupon during checkout.</p>

        <div class="mt-m" style="display:grid;gap:10px">
          @if ($subtotal >= $siteSettings->min_order && $shippingSettings->nationwide && $shippingSettings->cod_enabled)
            <button class="btn btn-light btn-block" type="button" id="checkout-selected" @disabled($cart->items->isEmpty())>Checkout selected ({{ $cart->items->count() }})</button>
          @else
            <button class="btn btn-light btn-block" type="button" disabled>
              {{ ! $shippingSettings->nationwide ? 'Delivery unavailable' : (! $shippingSettings->cod_enabled ? 'COD unavailable' : 'Minimum order not reached') }}
            </button>
          @endif
        </div>

        <p class="hint" style="color:#8a8a90;margin:16px 0 0">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" width="14" height="14" style="vertical-align:-2px"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
          Free delivery over Rs {{ number_format($shippingSettings->free_threshold) }} · 7-day exchange
        </p>
      </aside>
    </div>

    <!-- ============ RECOMMENDED ============ -->
    <div class="mt-l">
      <div class="sec-head"><div><span class="eyebrow">Complete the look</span><h2>You May Also Like</h2></div>
        <a class="link-arrow" href="{{ route('shop') }}">Shop All</a></div>
      <div class="product-grid" id="also-like"></div>
    </div>
  </div>

@endsection

@section('scripts')
<script>
/* ---------- CART PAGE ONLY ---------- */
document.addEventListener("DOMContentLoaded", function () {
  var selectAll = document.getElementById("cart-select-all");
  var itemSelections = Array.from(document.querySelectorAll(".cart-item-select"));
  var deleteButton = document.getElementById("delete-selected-button");
  var checkoutButton = document.getElementById("checkout-selected");
  var deleteForm = document.getElementById("delete-selected-form");
  function updateSelection() {
    var selected = itemSelections.filter(function (item) { return item.checked; });
    if (selectAll) {
      selectAll.checked = selected.length === itemSelections.length;
      selectAll.indeterminate = selected.length > 0 && selected.length < itemSelections.length;
    }
    if (deleteButton) deleteButton.disabled = selected.length === 0;
    if (checkoutButton) {
      checkoutButton.disabled = selected.length === 0;
      checkoutButton.textContent = "Checkout selected (" + selected.length + ")";
    }
  }
  if (selectAll) selectAll.addEventListener("change", function () {
    itemSelections.forEach(function (item) { item.checked = selectAll.checked; });
    updateSelection();
  });
  itemSelections.forEach(function (item) { item.addEventListener("change", updateSelection); });
  if (deleteForm) deleteForm.addEventListener("submit", function (event) {
    if (!itemSelections.some(function (item) { return item.checked; })) {
      event.preventDefault();
      return;
    }
    if (!window.confirm(deleteForm.dataset.confirm)) event.preventDefault();
  });
  if (checkoutButton) checkoutButton.addEventListener("click", function () {
    var selected = itemSelections.filter(function (item) { return item.checked; });
    if (!selected.length) return;
    var url = new URL(@json(route('checkout')), window.location.origin);
    selected.forEach(function (item) { url.searchParams.append("items[]", item.value); });
    window.location.assign(url.toString());
  });
  updateSelection();

  var inCart = @json($cart->items->pluck('product_id')->all());
  var picks = bwBestSellers(12).filter(function (p) { return inCart.indexOf(p.id) < 0; }).slice(0, 4);
  bwRenderCards("also-like", picks.length ? picks : bwBestSellers(4));
});
</script>
@endsection
