@extends('layouts.layout')

@section('title', 'Track Order — BeastWear.pk')

@section('meta')
    <meta name="description" content="Track your BeastWear.pk order using the order number and phone used at checkout.">
@endsection

@section('main-class', 'section')

@section('content')
<div class="wrap wrap-sm">
    <div class="crumbs"><a href="{{ route('home') }}">Home</a><i>/</i><span>Track Order</span></div>
    <div class="sec-head"><div><span class="eyebrow">Delivery updates</span><h2>Track Your Order</h2></div></div>

    <div class="panel">
        @if ($errors->any())<div class="alert alert-err" role="alert">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('track-order.lookup') }}" class="row" style="gap:10px">
            @csrf
            <div class="field" style="flex:1;margin:0;min-width:220px">
                <label for="track-num">Order Number, Tracking ID or Parcel Tracking Number</label>
                <input type="text" id="track-num" name="order_number" value="{{ old('order_number', $order?->order_number) }}" placeholder="Order number or courier tracking number" required>
            </div>
            <div class="field" style="flex:1;margin:0;min-width:220px">
                <label for="track-phone">Checkout Phone Number</label>
                <input type="tel" id="track-phone" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="03XX XXXXXXX" required>
            </div>
            <button class="btn btn-dark" type="submit" style="align-self:flex-end">Track Order</button>
        </form>
        <p class="hint" style="margin:14px 0 0">Enter your order number, BeastWear tracking ID, or courier tracking number, plus the phone number used at checkout.</p>
    </div>

    @if ($order)
        <div class="panel mt-m">
            <div class="row-between">
                <div><h3>{{ $order->order_number }}</h3><span class="hint">Placed {{ $order->created_at->format('d M Y') }}</span></div>
                <span class="status">{{ ucfirst(str_replace('_', ' ', $order->order_status)) }}</span>
            </div>
            <p>BeastWear Tracking ID: <strong>{{ $order->public_tracking_id }}</strong></p>
            <p>Shipping status: <strong>{{ ucfirst(str_replace('_', ' ', $order->shipping_status)) }}</strong></p>
            @if ($order->tracking_number)
                <p>Courier: <strong>{{ $order->shipping_courier ?: 'Not specified' }}</strong> · Parcel tracking number: <strong>{{ $order->tracking_number }}</strong></p>
            @endif
            @foreach ($order->items as $item)
                <div class="row-between"><span>{{ $item->product_name }} · Qty {{ $item->quantity }}</span><span>Rs {{ number_format((float) $item->total, 0) }}</span></div>
            @endforeach
            <p class="mt-s"><strong>Total: Rs {{ number_format((float) $order->total_amount, 0) }}</strong> · {{ $order->payment_method }}</p>
            @if (auth()->check() && auth()->id() === $order->user_id)
                <a href="{{ route('orders.show', $order) }}" class="btn btn-outline btn-sm">Open order details</a>
            @endif
        </div>
    @endif
</div>
@endsection
