@extends('layouts.layout')

@section('title', 'Order Details — BeastWear.pk')

@section('meta')
    <meta name="description" content="Full details and delivery status of your BeastWear.pk order.">
@endsection

@section('main-class', 'section-sm')

@section('content')
<div class="wrap wrap-sm">
    <div class="crumbs"><a href="{{ route('home') }}">Home</a><i>/</i><a href="{{ route('orders') }}">My Orders</a><i>/</i><span>{{ $order->order_number }}</span></div>
    <div class="sec-head">
        <div><span class="eyebrow">Order details</span><h2>{{ $order->order_number }}</h2></div>
        <span class="status">{{ ucfirst(str_replace('_', ' ', $order->order_status)) }}</span>
    </div>
    <p class="hint">Placed on {{ $order->created_at->format('d M Y, H:i') }}</p>

    @if (session('status'))
        <div class="alert alert-ok" role="status">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="panel">
        <h3>Order Status</h3>
        <p>{{ ucfirst(str_replace('_', ' ', $order->order_status)) }} · Shipping: {{ ucfirst(str_replace('_', ' ', $order->shipping_status)) }}</p>
        <p>BeastWear Tracking ID: <strong>{{ $order->public_tracking_id }}</strong></p>
        @if ($order->tracking_number)
            <p>Courier: <strong>{{ $order->shipping_courier ?: 'Not specified' }}</strong> · Parcel tracking number: <strong>{{ $order->tracking_number }}</strong></p>
        @endif
        <p class="hint">Payment: {{ $order->payment_method }} · {{ ucfirst($order->payment_status) }}</p>
    </div>

    @php($payment = $order->payments->sortByDesc('id')->first())
    @if ($order->payment_method !== 'COD' && $order->payment_status === 'pending' && $payment)
        <div class="panel mt-m">
            <h3>Prepaid Payment — {{ $order->payment_method }}</h3>
            @if ($payment->proof_path)
                <p>Your receipt has been received and is awaiting admin verification. You may replace it while the payment remains pending.</p>
            @else
                <p>Upload a clear screenshot or PDF of your transfer receipt. The order will be processed after admin verification.</p>
            @endif
            <form method="POST" action="{{ route('orders.payment-proof', $order) }}" enctype="multipart/form-data">
                @csrf
                <div class="field">
                    <label for="transaction_id">Transaction / reference ID <small>(optional)</small></label>
                    <input type="text" id="transaction_id" name="transaction_id" maxlength="255" value="{{ old('transaction_id', $payment->transaction_id) }}">
                </div>
                <div class="field">
                    <label for="proof">Receipt image or PDF <small>(JPG, PNG, WEBP, PDF; max 5 MB)</small></label>
                    <input type="file" id="proof" name="proof" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                </div>
                <button class="btn btn-dark" type="submit">Submit payment receipt</button>
            </form>
        </div>
    @elseif ($payment && $payment->status === 'paid')
        <div class="panel mt-m"><h3>Payment confirmed</h3><p>Your payment receipt has been verified by the BeastWear team.</p></div>
    @elseif ($payment && $payment->status === 'rejected')
        <div class="panel mt-m"><h3>Payment rejected</h3><p>This order was cancelled. Please place a new order if you still want to purchase these items.</p></div>
    @endif

    <div class="panel mt-m">
        <h3>Products</h3>
        @foreach ($order->items as $item)
            <div class="row-between" style="padding:12px 0;border-bottom:1px solid var(--line)">
                <div>
                    <strong>{{ $item->product_name }}</strong>
                    <div class="hint">{{ $item->sku }}@if ($item->size) · {{ $item->size }}@endif @if ($item->color) · {{ $item->color }}@endif · Qty {{ $item->quantity }}</div>
                    @if ($item->design_name)
                        <div class="hint">Design: {{ $item->design_name }}</div>
                        @if ($item->design_image_path)
                            <img src="{{ \App\Models\ProductImage::urlFor($item->design_image_path) }}" alt="{{ $item->design_name }}" width="72" loading="lazy">
                        @endif
                    @endif
                </div>
                <b>Rs {{ number_format((float) $item->total, 0) }}</b>
            </div>
        @endforeach
    </div>

    <div class="mt-m" style="display:grid;grid-template-columns:1fr 1fr;gap:18px">
        <div class="panel">
            <h3>Delivery Address</h3>
            <p><strong>{{ $order->customer_name }}</strong><br>{{ $order->customer_phone }}<br>{{ $order->customer_email }}</p>
            <p>{{ $order->shipping_address }}<br>{{ $order->city }} {{ $order->postal_code }}</p>
        </div>
        <div class="panel">
            <h3>Payment &amp; Summary</h3>
            <div class="sum-line"><span>Subtotal</span><b>Rs {{ number_format((float) $order->subtotal, 0) }}</b></div>
            <div class="sum-line"><span>Delivery</span><span>Rs {{ number_format((float) $order->shipping_fee, 0) }}</span></div>
            <div class="sum-line"><span>Discount</span><span>Rs {{ number_format((float) $order->discount, 0) }}</span></div>
            <div class="sum-line"><strong>Total</strong><b>Rs {{ number_format((float) $order->total_amount, 0) }}</b></div>
            @if ($order->customer_note)<p class="hint"><strong>Order notes:</strong> {{ $order->customer_note }}</p>@endif
        </div>
    </div>
    <div class="row mt-m" style="gap:12px">
        <a class="btn btn-outline" href="{{ route('orders') }}">All Orders</a>
        <a class="btn btn-outline" href="{{ route('returns') }}">Exchange Policy</a>
    </div>
</div>
@endsection
