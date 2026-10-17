@extends('layouts.layout')

@section('title', 'Checkout — BeastWear.pk')

@section('meta')
    <meta name="description" content="Complete your BeastWear.pk order with your preferred available payment method.">
@endsection

@section('main-class', 'section-sm')

@section('content')

@php
  $selectedPaymentMethod = old('payment_method', $shippingSettings->cod_enabled ? 'COD' : array_key_first($prepaidMethods));
@endphp

<div class="wrap">
    <div class="crumbs"><a href="{{ route('home') }}">Home</a><i>/</i><a href="{{ route('cart') }}">Cart</a><i>/</i><span>Checkout</span></div>
    @if ($errors->any())
      <div class="alert alert-err" role="alert">{{ $errors->first() }}</div>
    @endif
    @if (session('status'))
      <div class="alert alert-ok" role="status">{{ session('status') }}</div>
    @endif
    <div class="sec-head"><div><span class="eyebrow">Almost there</span><h2>Checkout</h2></div>
      <span class="hint">{{ $shippingSettings->cod_enabled ? 'Cash on Delivery' : 'Prepaid orders only' }} · {{ $shippingSettings->nationwide ? 'Pakistan-wide' : 'Delivery unavailable' }}</span></div>
    <p class="hint">Minimum order: Rs {{ number_format($siteSettings->min_order) }} · Order confirmation: {{ $siteSettings->order_confirmation_label }}.</p>

    <!-- empty cart message -->
    <div class="empty" id="checkout-empty" @if ($cart->items->isNotEmpty()) hidden @endif>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 6h2.2l2.3 10h9.2l2.1-7.2H7.2"/><circle cx="10" cy="19.4" r="1.4"/><circle cx="17" cy="19.4" r="1.4"/></svg>
      <h3>Your cart is empty</h3>
      <p>Add something to your bag before checking out.</p>
      <a class="btn btn-dark" href="{{ route('shop') }}">Shop Now</a>
    </div>

    <div class="cart-layout" id="checkout-wrap" @if ($cart->items->isEmpty()) hidden @endif>
      <!-- ============ CUSTOMER FORM ============ -->
      <form id="checkout-form" method="POST" action="{{ route('checkout.store') }}">
        @csrf
        @foreach ($selectedCartItemIds as $cartItemId)
          <input type="hidden" name="cart_item_ids[]" value="{{ $cartItemId }}">
        @endforeach
        <div class="panel">
          <h3>Customer Details</h3>
          <div class="form-grid">
            <div class="field"><label for="co-name">Full Name</label>
              <input type="text" id="co-name" name="customer_name" value="{{ old('customer_name', auth()->user()->name) }}" placeholder="e.g. Ahmed Raza" required autocomplete="name">
              <span class="err">Please enter your full name.</span></div>
            <div class="field"><label for="co-phone">Mobile Number</label>
              <input type="tel" id="co-phone" name="customer_phone" value="{{ old('customer_phone', auth()->user()->phone) }}" placeholder="03XX XXXXXXX" required autocomplete="tel">
              <span class="err">Enter a valid mobile number.</span></div>
          </div>
          <div class="field"><label for="co-email">Email <small>(optional — for order updates)</small></label>
            <input type="email" id="co-email" name="customer_email" value="{{ old('customer_email', auth()->user()->email) }}" placeholder="you@email.com">
            <span class="err">Enter a valid email address.</span></div>

          <div class="field"><label for="co-city">City</label>
            <input type="text" id="co-city" name="city" value="{{ old('city', $addresses->first()?->city) }}" placeholder="e.g. Lahore" required autocomplete="address-level2">
            <span class="err">Please enter your city.</span></div>
          <div class="field"><label for="co-postal-code">Postal Code <small>(optional)</small></label>
            <input type="text" id="co-postal-code" name="postal_code" value="{{ old('postal_code', $addresses->first()?->postal_code) }}" autocomplete="postal-code"></div>
          @if ($addresses->isNotEmpty())
            <label class="field"><span>Saved address</span>
              <select onchange="document.getElementById('co-address').value=this.value">
                <option value="">Choose a saved address</option>
                @foreach ($addresses as $address)
                  <option value="{{ $address->address }}{{ $address->area ? ', '.$address->area : '' }}" @selected($loop->first)>
                    {{ $address->name }} — {{ $address->city }} ({{ $address->address }})
                  </option>
                @endforeach
              </select>
            </label>
          @endif
          <div class="field"><label for="co-area">Area <small>(locality / block)</small></label>
            <input type="text" id="co-area" name="area" value="{{ old('area', $addresses->first()?->area) }}" placeholder="e.g. Gulberg III">
            <span class="err">Please enter your area.</span></div>
          <div class="field"><label for="co-address">Complete Address</label>
            <textarea id="co-address" name="shipping_address" placeholder="House / flat no, street, landmark..." required>{{ old('shipping_address', $addresses->first()?->address) }}</textarea>
            <span class="err">Please enter your complete address.</span></div>
          <div class="field"><label for="co-notes">Order Notes <small>(optional)</small></label>
            <textarea id="co-notes" name="customer_note" placeholder="Any delivery instructions, gift note or size preference">{{ old('customer_note') }}</textarea></div>
          <div class="field"><label for="co-coupon">Coupon code <small>(optional)</small></label>
            <input type="text" id="co-coupon" name="coupon_code" value="{{ old('coupon_code') }}" placeholder="Enter coupon code"></div>
          <div class="info-box">
            <strong>Delivery: {{ $shippingSettings->courier }}</strong>
            <span class="hint" style="display:block">Estimated {{ $shippingSettings->karachi_days }} working days in Karachi and {{ $shippingSettings->other_days }} in other cities. Free delivery over Rs {{ number_format($shippingSettings->free_threshold) }}.</span>
          </div>
        </div>

        <!-- payment -->
        <div class="panel mt-m">
          <h3>Payment Method</h3>
          @if ($shippingSettings->cod_enabled)
            <label class="info-box" style="display:flex;gap:14px;align-items:flex-start;cursor:pointer">
              <input type="radio" name="payment_method" value="COD" @checked(old('payment_method', 'COD') === 'COD') style="margin-top:4px;accent-color:#0b0b0c">
              <span><strong>Cash on Delivery (COD)</strong>
              <span class="hint" style="display:block">Pay in cash when the courier hands over your parcel. COD handling fee: Rs {{ number_format($shippingSettings->cod_fee) }}.</span></span>
            </label>
          @endif
          @foreach ($prepaidMethods as $method => $account)
            <label class="info-box" style="display:flex;gap:14px;align-items:flex-start;cursor:pointer">
              <input type="radio" name="payment_method" value="{{ $method }}" @checked($selectedPaymentMethod === $method) style="margin-top:4px;accent-color:#0b0b0c">
              <span><strong>{{ $method }}</strong>
                <span class="hint" style="display:block">{{ $account['institution'] }} · Account title: {{ $account['account_title'] }} · Account number: {{ $account['account_number'] }}</span>
                <span class="hint" style="display:block">Place your order, transfer the total, then upload the receipt from your order details. Dispatch is after admin verification.</span>
              </span>
            </label>
          @endforeach
          @if (! $shippingSettings->cod_enabled && $prepaidMethods === [])
            <p class="hint">No payment methods are currently available. Please contact us before placing an order.</p>
          @endif
          @error('payment_method')<p class="hint" role="alert">{{ $message }}</p>@enderror
        </div>

        <div class="row mt-m" style="gap:12px">
          <button class="btn btn-dark" type="submit" style="flex:1 1 220px" @disabled(! $shippingSettings->nationwide || (! $shippingSettings->cod_enabled && $prepaidMethods === []))>Place Order</button>
          <a class="btn btn-outline" href="{{ route('contact') }}" style="flex:1 1 220px;text-align:center">Need help?</a>
        </div>
        <p class="hint" style="margin-top:14px">By placing an order you agree to our
          <a href="{{ route('terms') }}" style="text-decoration:underline">Terms &amp; Conditions</a> and
          <a href="{{ route('returns') }}" style="text-decoration:underline">Exchange Policy</a>.</p>
      </form>

      <!-- ============ SUMMARY ============ -->
      <aside class="panel panel-dark">
        <h3>Your Order</h3>
        @php
          $subtotal = $cart->items->sum(fn ($item) => (float) $item->price * $item->quantity);
          $deliveryFee = $shippingSettings->deliveryFee($subtotal, old('city', $addresses->first()?->city));
          $codFee = $shippingSettings->cod_enabled ? $shippingSettings->cod_fee : 0;
          $selectedCodFee = $selectedPaymentMethod === 'COD' ? $codFee : 0;
          $shippingFee = $deliveryFee + $selectedCodFee;
        @endphp
        @foreach ($cart->items as $item)
          <div class="row-center" style="gap:11px;padding:8px 0;border-bottom:1px solid #303035">
            @php
              $checkoutImage = ($item->product->images->firstWhere('is_primary', true) ?? $item->product->images->first())?->url;
            @endphp
            <span class="mini-thumb">@if ($checkoutImage)<img src="{{ $checkoutImage }}" alt="">@else<span class="product-image-placeholder" aria-label="No product image available"></span>@endif</span>
            <span style="flex:1;color:#d8d7d3;font-size:13px"><b style="color:#fff;display:block">{{ $item->product->name }}</b>
              @if ($item->selectedDesign) {{ $item->selectedDesign->design_name }} · @endif
              @if ($item->variant?->size) {{ $item->variant->size }} · @endif
              @if ($item->variant?->color) {{ $item->variant->color }} · @endif
              Qty {{ $item->quantity }}</span>
            <b style="color:#fff;font-size:13px">Rs {{ number_format((float) $item->price * $item->quantity, 0) }}</b>
          </div>
        @endforeach
        <div class="sum-line"><span>Subtotal</span><b style="color:#fff">Rs {{ number_format($subtotal, 0) }}</b></div>
        @if ($subtotal < $siteSettings->min_order)
          <p class="hint">Add Rs {{ number_format($siteSettings->min_order - $subtotal) }} more to meet the minimum order.</p>
        @endif
        <div class="sum-line"><span>Delivery</span><span>{{ $deliveryFee ? 'Rs '.number_format($deliveryFee, 0) : 'FREE' }}</span></div>
        @if ($codFee)
          <div class="sum-line" id="checkout-cod-fee" @if (! $selectedCodFee) hidden @endif><span>COD handling</span><span>Rs {{ number_format($codFee, 0) }}</span></div>
        @endif
        <div class="sum-line total"><span>Total before coupon</span><span id="checkout-total" data-subtotal="{{ $subtotal }}" data-delivery="{{ $deliveryFee }}" data-cod-fee="{{ $codFee }}">Rs {{ number_format($subtotal + $shippingFee, 0) }}</span></div>

        <div style="margin-top:18px;padding-top:16px;border-top:1px solid #3a3a3f">
          <div class="icon-line" style="margin-bottom:12px"><span class="ico">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M2 7h11v9H2zM13 10h4l3 3v3h-7z"/><circle cx="6" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/></svg></span>
            <div><h4 style="color:#fff">{{ $shippingSettings->courier }}</h4><p style="color:#9a9995">{{ $shippingSettings->karachi_days }} days Karachi · {{ $shippingSettings->other_days }} days other cities</p></div></div>
          <div class="icon-line" style="margin-bottom:12px"><span class="ico">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3l7 3v5c0 4.4-3 7.9-7 10-4-2.1-7-5.6-7-10V6z"/><path d="M9 12l2 2 4-4"/></svg></span>
            <div><h4 style="color:#fff">Safe Payment</h4><p style="color:#9a9995">Inspect before you pay</p></div></div>
          <div class="icon-line"><span class="ico">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M20 12a8 8 0 1 1-2.6-5.9"/><path d="M20 4v5h-5"/></svg></span>
            <div><h4 style="color:#fff">Easy Exchange</h4><p style="color:#9a9995">7 days, no questions asked</p></div></div>
        </div>
      </aside>
    </div>
  </div>

@endsection

@section('scripts')
<script>
  document.querySelectorAll('input[name="payment_method"]').forEach((input) => {
    input.addEventListener('change', () => {
      const total = document.getElementById('checkout-total');
      if (!total) return;
      const cod = input.value === 'COD';
      const amount = Number(total.dataset.subtotal) + Number(total.dataset.delivery) + (cod ? Number(total.dataset.codFee) : 0);
      total.textContent = 'Rs ' + new Intl.NumberFormat('en-PK', { maximumFractionDigits: 0 }).format(amount);
      const fee = document.getElementById('checkout-cod-fee');
      if (fee) fee.hidden = !cod;
    });
  });
</script>
@endsection
