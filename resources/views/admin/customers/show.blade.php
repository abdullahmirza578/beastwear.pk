@extends('admin.layouts.layout')

@section('title', 'Customer Details | BeastWear.pk Admin')
@section('page', 'customer-details')

@section('content')

<div class="page-header">
        <div><div class="breadcrumb"><a href="/admin/dashboard">Home</a><span class="sep">/</span><a href="/admin/customers">Customers</a><span class="sep">/</span><span>Details</span></div><h1 id="custName">Customer</h1><p class="subtitle">Profile, statistics and order history.</p></div>
        <div class="page-actions"><a class="btn btn-outline" href="/admin/customers">← Back to Customers</a></div>
      </div>
      <div class="grid grid-1-2">
        <div>
          <div class="card"><div class="card-body" id="profile"></div></div>
          <div class="card"><div class="card-header"><div class="card-title">Most Purchased</div></div><div class="card-body" id="favChart"></div></div>
        </div>
        <div>
          <div class="stats-grid" id="stats"></div>
          <div class="card">
            <div class="card-header"><div class="card-title">Order History</div></div>
            <div class="table-wrap"><table class="table"><thead><tr><th>Order</th><th>Date</th><th>Items</th><th>Payment</th><th>Amount</th><th>Status</th><th></th></tr></thead><tbody id="ordersBody"></tbody></table></div>
          </div>
          <div class="card"><div class="card-header"><div class="card-title">Reviews</div></div><div id="reviewsList"></div></div>
        </div>
      </div>

@endsection

@section('scripts')
<script src="{{ asset('admin-assets/js/customers.js') }}"></script>
@endsection
