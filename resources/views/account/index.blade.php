@extends('layouts.layout')

@section('title', 'My Account — BeastWear.pk')

@section('meta')
    <meta name="description" content="Manage your BeastWear.pk profile, orders, addresses and wishlist.">
@endsection

@section('main-class', 'section-sm')

@section('content')

<div class="wrap">
    <div class="crumbs"><a href="{{ route('home') }}">Home</a><i>/</i><span>My Account</span></div>
    <div class="sec-head"><div><span class="eyebrow">Dashboard</span><h2>My Account</h2></div></div>
    @if (session('status'))
      <div class="alert alert-ok" role="status">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
      <div class="alert alert-err" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="account-layout" id="account-wrap">
      <aside class="side-nav">
        <a href="{{ route('account') }}" class="active">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8" r="3.6"/><path d="M4.5 20c1.4-3.6 4-5.2 7.5-5.2S18.1 16.4 19.5 20"/></svg>
          Dashboard</a>
        <a href="{{ route('orders') }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 8l9-5 9 5v8l-9 5-9-5z"/><path d="M3 8l9 5 9-5M12 13v8"/></svg>
          My Orders</a>
        <a href="{{ route('wishlist') }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 20s-7-4.4-7-9.3A4.1 4.1 0 0 1 12 8a4.1 4.1 0 0 1 7 2.7C19 15.6 12 20 12 20z"/></svg>
          Wishlist</a>
        <a href="{{ route('cart') }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 6h2.2l2.3 10h9.2l2.1-7.2H7.2"/><circle cx="10" cy="19.4" r="1.4"/><circle cx="17" cy="19.4" r="1.4"/></svg>
          My Cart</a>
        <a href="{{ route('track-order') }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/></svg>
          Track Order</a>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" id="ac-logout">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M15 4h4v16h-4M10 8l-4 4 4 4M6 12h9"/></svg>
          Logout</button>
        </form>
      </aside>

      <div>
        <!-- profile card -->
        <div class="panel">
          <div class="row-center" style="gap:16px;align-items:center">
            <div style="width:60px;height:60px;border-radius:50%;background:var(--black);color:#fff;display:grid;place-items:center;font-size:24px;font-family:var(--font-display)">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            <div>
              <h3 style="margin:0">{{ $user->name }}</h3>
              <span class="hint">Member since {{ $user->created_at->format('M Y') }}</span>
            </div>
            <a class="btn btn-outline btn-sm" href="#profile-edit" style="margin-left:auto">Edit Profile</a>
          </div>
          <div class="form-grid mt-m">
            <div><span class="eyebrow">Email</span><p style="margin:0">{{ $user->email }}</p></div>
            <div><span class="eyebrow">Mobile</span><p style="margin:0">{{ $user->phone ?: 'Not added yet' }}</p></div>
          </div>
        </div>

        <form id="profile-edit" class="panel mt-m" method="POST" action="{{ route('account.update') }}">
          @csrf
          @method('PUT')
          <h3>Update Profile</h3>
          <div class="form-grid">
            <div class="field"><label for="profile-name">Full Name</label><input id="profile-name" name="name" value="{{ old('name', $user->name) }}" required></div>
            <div class="field"><label for="profile-email">Email</label><input id="profile-email" name="email" type="email" value="{{ old('email', $user->email) }}" required></div>
            <div class="field"><label for="profile-phone">Phone</label><input id="profile-phone" name="phone" value="{{ old('phone', $user->phone) }}"></div>
            <div class="field"><label for="profile-password">New password <small>(optional)</small></label><input id="profile-password" name="password" type="password" minlength="8" autocomplete="new-password"></div>
            <div class="field"><label for="profile-password-confirmation">Confirm new password</label><input id="profile-password-confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password"></div>
          </div>
          <button class="btn btn-dark" type="submit">Save Profile</button>
        </form>

        <!-- stats -->
        <div class="mt-m" style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px" id="ac-stats">
          <div class="panel text-center"><b style="font-family:var(--font-display);font-size:30px;font-weight:400;display:block">{{ $user->orders_count ?? $user->orders()->count() }}</b><span class="hint">Total orders</span></div>
          <div class="panel text-center"><b style="font-family:var(--font-display);font-size:30px;font-weight:400;display:block">{{ $wishlistCount }}</b><span class="hint">Wishlist items</span></div>
          <div class="panel text-center"><b style="font-family:var(--font-display);font-size:30px;font-weight:400;display:block">Rs {{ number_format((float) $user->orders()->whereNotIn('order_status', ['cancelled', 'returned'])->sum('total_amount'), 0) }}</b><span class="hint">Lifetime spend</span></div>
        </div>

        <!-- recent orders -->
        <div class="panel mt-m">
          <div class="row-between"><h3>Recent Orders</h3><a class="link-arrow" href="orders.html">All Orders</a></div>
          <div id="ac-orders">
            @forelse ($orders as $order)
              <div class="order-card">
                <div class="order-head"><span><b>{{ $order->order_number }}</b> · {{ $order->created_at->format('d M Y') }}</span><span>{{ ucfirst($order->order_status) }}</span></div>
                <div class="row-between"><span>{{ $order->items->sum('quantity') }} item(s)</span><b>Rs {{ number_format((float) $order->total_amount, 0) }}</b></div>
                <a href="{{ route('orders.show', $order) }}">View order</a>
              </div>
            @empty
              <p class="hint">You have not placed an order yet.</p>
            @endforelse
          </div>
        </div>

        <!-- addresses + wishlist -->
        <div class="mt-m" style="display:grid;grid-template-columns:1fr 1fr;gap:18px" id="ac-two">
          <div class="panel">
            <div class="row-between"><h3>Addresses</h3></div>
            <div id="ac-addresses">
              @forelse ($addresses as $address)
                <p><b>{{ $address->name }}</b> · {{ $address->phone }}<br>{{ $address->address }}{{ $address->area ? ', '.$address->area : '' }}, {{ $address->city }} {{ $address->postal_code }}</p>
                <form method="POST" action="{{ route('addresses.destroy', $address) }}">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-outline btn-sm" type="submit">Remove address</button>
                </form>
              @empty
                <p class="hint">No saved addresses.</p>
              @endforelse
            </div>
            <form class="mt-m" method="POST" action="{{ route('addresses.store') }}">
              @csrf
              <h4>Add Address</h4>
              <div class="field"><label>Name</label><input name="name" required></div>
              <div class="field"><label>Phone</label><input name="phone" required></div>
              <div class="field"><label>City</label><input name="city" required></div>
              <div class="field"><label>Area</label><input name="area"></div>
              <div class="field"><label>Address</label><textarea name="address" required></textarea></div>
              <div class="field"><label>Postal Code</label><input name="postal_code"></div>
              <div class="field"><label>Type</label><select name="address_type"><option value="home">Home</option><option value="work">Work</option><option value="other">Other</option></select></div>
              <label class="check"><input type="checkbox" name="is_default" value="1"> Default address</label>
              <button class="btn btn-dark btn-sm" type="submit">Save Address</button>
            </form>
          </div>
          <div class="panel">
            <div class="row-between"><h3>Wishlist</h3><a class="link-arrow" href="wishlist.html">Open</a></div>
            <div><p class="hint">{{ $wishlistCount }} saved product(s)</p><a href="{{ route('wishlist') }}">View wishlist</a></div>
          </div>
        </div>
      </div>
    </div>
  </div>

@endsection

@section('scripts')
@endsection
