@extends('admin.layouts.layout')

@section('title', 'Orders | BeastWear.pk Admin')
@section('page', 'orders')

@section('content')

<div class="page-header">
        <div><div class="breadcrumb"><a href="/admin/dashboard">Home</a><span class="sep">/</span><span>Sales</span><span class="sep">/</span><span>Orders</span></div><h1>Orders</h1><p class="subtitle">Filtered total: <b id="orderTotal" style="color:var(--text)">—</b></p></div>
        <div class="page-actions"><button class="btn btn-outline" id="exportOrders">Export CSV</button><a class="btn btn-primary" href="/admin/dashboard">Dashboard</a></div>
      </div>
      <div class="card">
        <div class="status-tabs" id="statusTabs"></div>
        <div class="toolbar">
          <div class="search-box"><svg class="icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.3-4.3"/></svg><input class="form-control" id="oSearch" type="search" placeholder="Search order ID, customer name or phone…"></div>
          <select class="form-control" id="oPayment"><option value="">All Payments</option><option>Cash on Delivery</option><option>JazzCash</option><option>EasyPaisa</option><option>Bank Transfer</option><option>Debit/Credit Card</option></select>
          <select class="form-control" id="oCity"></select>
          <input type="date" class="form-control" id="oFrom" title="From date"><input type="date" class="form-control" id="oTo" title="To date">
          <button class="btn btn-ghost btn-sm" id="clearFilters">Clear</button>
        </div>
        <div class="table-wrap">
          <table class="table" id="ordersTable">
            <thead><tr><th data-sort="id">Order ID</th><th data-sort="customer">Customer</th><th data-sort="date">Date</th><th>Products</th><th data-sort="total">Amount</th><th data-sort="payment">Payment Method</th><th>City</th><th data-sort="status">Status</th><th>Actions</th></tr></thead>
            <tbody></tbody>
          </table>
        </div>
        <div class="table-footer"><span id="pageInfo"></span><div id="pager"></div></div>
      </div>

@endsection

@section('scripts')
<script src="{{ asset('admin-assets/js/orders.js') }}"></script>
@endsection
