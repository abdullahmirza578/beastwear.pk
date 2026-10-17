@extends('layouts.layout')

@section('title', 'My Orders — BeastWear.pk')

@section('meta')
    <meta name="description" content="View and track all your BeastWear.pk orders.">
@endsection

@section('main-class', 'section-sm')

@section('content')
<div class="wrap wrap-sm">
    <div class="crumbs"><a href="{{ route('home') }}">Home</a><i>/</i><a href="{{ route('account') }}">My Account</a><i>/</i><span>Orders</span></div>
    <div class="sec-head">
        <div><span class="eyebrow">Order history</span><h2>My Orders</h2></div>
        <span class="hint">Signed in as <b>{{ auth()->user()->name }}</b></span>
    </div>

    @if (session('status'))
        <div class="alert alert-ok" role="status">{{ session('status') }}</div>
    @endif

    @forelse ($orders as $order)
        <article class="panel mt-m">
            <div class="row-between">
                <div><span class="eyebrow">Order</span><h3>{{ $order->order_number }}</h3></div>
                <span class="status">{{ ucfirst(str_replace('_', ' ', $order->order_status)) }}</span>
            </div>
            <p class="hint">Placed {{ $order->created_at->format('d M Y') }} · {{ $order->items->sum('quantity') }} item(s)</p>
            <div class="row-between">
                <span>{{ $order->payment_method }} · {{ ucfirst($order->payment_status) }}</span>
                <b>Rs {{ number_format((float) $order->total_amount, 0) }}</b>
            </div>
            <a class="btn btn-outline btn-sm mt-s" href="{{ route('orders.show', $order) }}">View order details</a>
        </article>
    @empty
        <div class="empty">
            <h3>No orders yet</h3>
            <p>Your completed checkouts will appear here.</p>
            <a class="btn btn-dark" href="{{ route('shop') }}">Browse Products</a>
        </div>
    @endforelse

    {{ $orders->links() }}
</div>
@endsection
