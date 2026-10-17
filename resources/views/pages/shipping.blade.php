@extends('layouts.layout')

@section('title', 'Shipping Policy — BeastWear.pk')

@section('meta')
    <meta name="description" content="BeastWear.pk shipping information: delivery charges, estimated delivery times, Cash on Delivery and order processing.">
@endsection

@section('content')

<section class="page-hero">
    <div class="wrap">
      <div class="crumbs"><a href="index.html">Home</a><i>/</i><span>Shipping</span></div>
      <h1>Shipping Policy</h1>
      <p>{{ $shippingSettings->nationwide ? 'We deliver across Pakistan.' : 'Delivery is temporarily unavailable.' }} {{ $shippingSettings->cod_enabled ? 'Cash on Delivery is available.' : 'Cash on Delivery is currently unavailable.' }} Here is exactly how it works.</p>
    </div>
  </section>

  <section class="section-sm">
    <div class="wrap wrap-sm">
      <!-- quick facts -->
      <div class="stat-row">
        <div><b>{{ number_format($shippingSettings->default_charge) }}</b><span>Base delivery (Rs)</span></div>
        <div><b>{{ number_format($shippingSettings->free_threshold) }}</b><span>Free over (Rs)</span></div>
        <div><b>{{ $shippingSettings->karachi_days }}</b><span>Karachi working days</span></div>
        <div><b>24h</b><span>Dispatch time</span></div>
      </div>

      <div class="mt-l">
        <h3>Pakistan-wide delivery</h3>
        <p>We ship to all provinces and territories — Punjab, Sindh, Khyber Pakhtunkhwa, Balochistan, Islamabad Capital
          Territory, Azad Kashmir and Gilgit-Baltistan. Orders are handed to trusted courier partners (TCS / Leopard / M&amp;P)
          who deliver to your doorstep and call you before arrival. Our selected courier is {{ $shippingSettings->courier }}.</p>
      </div>

      <div class="mt-l">
        <h3>Delivery charges</h3>
        <div class="table-wrap"><table class="bw">
          <thead><tr><th>Order value</th><th>Delivery charge</th><th>Notes</th></tr></thead>
          <tbody>
            <tr><td class="bold">Rs {{ number_format($shippingSettings->free_threshold) }} and above</td><td><strong>FREE</strong></td><td>Automatically applied at checkout</td></tr>
            <tr><td class="bold">Below Rs {{ number_format($shippingSettings->free_threshold) }} — Karachi</td><td>Rs {{ number_format($shippingSettings->karachi_charge) }}</td><td>Karachi delivery rate</td></tr>
            <tr><td class="bold">Below Rs {{ number_format($shippingSettings->free_threshold) }} — other cities</td><td>Rs {{ number_format($shippingSettings->other_city_charge) }}</td><td>Remote areas: Rs {{ number_format($shippingSettings->remote_area_charge) }}</td></tr>
          </tbody>
        </table></div>
      </div>

      <div class="mt-l">
        <h3>Estimated delivery time</h3>
        <div class="table-wrap"><table class="bw">
          <thead><tr><th>Destination</th><th>Estimated time</th></tr></thead>
          <tbody>
            <tr><td class="bold">Karachi</td><td>{{ $shippingSettings->karachi_days }} working days</td></tr>
            <tr><td class="bold">Other major cities</td><td>{{ $shippingSettings->other_days }} working days</td></tr>
            <tr><td class="bold">Towns &amp; remote areas</td><td>4–6 working days</td></tr>
            <tr><td class="bold">Gilgit-Baltistan &amp; AJK</td><td>5–7 working days</td></tr>
          </tbody>
        </table></div>
        <p class="hint mt-s">Working days exclude Sundays and public holidays. Weather or courier delays in remote areas
          can add 1–2 days.</p>
      </div>

      <div class="mt-l">
        <h3>Cash on Delivery (COD)</h3>
        <div class="grid g-2">
          <div class="info-box"><h4>How it works</h4><p style="margin:0">{{ $shippingSettings->cod_enabled ? 'Pay the courier in cash when your parcel arrives. COD handling fee: Rs '.number_format($shippingSettings->cod_fee).'.' : 'Cash on Delivery is currently unavailable.' }}
            Please keep the exact amount ready — couriers often carry limited change.</p></div>
          <div class="info-box"><h4>Order limits</h4><p style="margin:0">COD is available on orders up to Rs 25,000.
            Larger orders can be paid via bank transfer or JazzCash / Easypaisa.</p></div>
          <div class="info-box"><h4>Verify before paying</h4><p style="margin:0">Open the parcel in front of the courier.
            If anything is wrong, refuse the parcel and message us immediately.</p></div>
          <div class="info-box"><h4>Repeated refusals</h4><p style="margin:0">Accounts with repeated refused COD parcels
            may be asked to pre-pay future orders.</p></div>
        </div>
      </div>

      <div class="mt-l">
        <h3>Order processing</h3>
        <div class="grid g-2">
          <div class="info-box"><h4>1. Order placed</h4><p style="margin:0">You receive an order number (BW-XXXXXX) instantly and a WhatsApp confirmation.</p></div>
          <div class="info-box"><h4>2. Confirmation call / message</h4><p style="margin:0">We verify stock and your address within a few hours.</p></div>
          <div class="info-box"><h4>3. Packed &amp; dispatched</h4><p style="margin:0">Orders before 4 PM are dispatched the same day with a tracking number.</p></div>
          <div class="info-box"><h4>4. Out for delivery</h4><p style="margin:0">The courier calls you and delivers to your doorstep.</p></div>
        </div>
        <p class="hint mt-s">Track any order on the <a href="track-order.html" style="text-decoration:underline">Track Order</a> page
          using your order number.</p>
      </div>

      <div class="info-box mt-l">
        <h4>Need help with a delivery?</h4>
        <p style="margin:0">WhatsApp +92 300 1234567 or email support@beastwear.pk with your order number — we reply
          within minutes, 7 days a week. See also our <a href="returns.html" style="text-decoration:underline">Returns &amp; Exchange</a> policy.</p>
      </div>
    </div>
  </section>

@endsection
