@extends('admin.layouts.layout')

@section('title', 'Dashboard | BeastWear.pk Admin')
@section('page', 'dashboard')

@section('content')

<div class="page-header">
        <div><div class="breadcrumb"><a href="/admin/dashboard">Home</a><span class="sep">/</span><span>Dashboard</span></div><h1>Dashboard</h1><p class="subtitle">Overview of BeastWear.pk store performance.</p></div>
        <div class="page-actions"><a class="btn btn-outline" href="/admin/reports">View Reports</a><a class="btn btn-primary" href="/admin/products/create">+ Add Product</a></div>
      </div>

      <div class="stats-grid cols-7" id="statsGrid"></div>

      <div class="grid grid-2-1">
        <div class="card">
          <div class="card-header">
            <div class="card-title">Sales Overview<small id="chartSub">Revenue from non-cancelled orders</small></div>
            <div class="flex"><div class="tabs" id="periodTabs"><button class="tab active" data-period="daily">Daily</button><button class="tab" data-period="weekly">Weekly</button><button class="tab" data-period="monthly">Monthly</button></div><button class="btn-icon" id="chartType" title="Switch chart type"></button></div>
          </div>
          <div class="card-body">
            <div class="flex gap-16 flex-wrap mb-24"><div><div class="stat-label">Total</div><div class="stat-value" id="chartTotal">—</div></div><div><div class="stat-label">Orders</div><div class="stat-value" style="font-size:18px;margin-top:6px" id="chartOrders">—</div></div><div><div class="stat-label">Avg. Order</div><div class="stat-value" style="font-size:18px;margin-top:6px" id="chartAvg">—</div></div></div>
            <div id="salesChart"></div>
          </div>
        </div>
        <div class="card">
          <div class="card-header"><div class="card-title">Orders by Status</div><a href="/admin/orders" class="small fw-600">View all →</a></div>
          <div class="card-body" id="statusChart"></div>
        </div>
      </div>

      <div class="card mt-24">
        <div class="card-header"><div class="card-title">Recent Orders<small>Latest 8 orders placed on the store</small></div><a href="/admin/orders" class="btn btn-outline btn-sm">All Orders</a></div>
        <div class="table-wrap"><table class="table"><thead><tr><th>Order ID</th><th>Customer</th><th>Date</th><th>Amount</th><th>Payment</th><th>Status</th><th>Action</th></tr></thead><tbody id="recentOrders"></tbody></table></div>
      </div>

      <div class="grid grid-2 mt-24">
        <div class="card">
          <div class="card-header"><div class="card-title">Top Selling Products</div><a href="/admin/reports" class="small fw-600">Report →</a></div>
          <div class="list-rows" id="topProducts"></div>
        </div>
        <div class="card">
          <div class="card-header"><div class="card-title">Low Stock Products</div><a href="/admin/inventory" class="btn btn-outline btn-sm">Manage Inventory</a></div>
          <div class="table-wrap"><table class="table" style="min-width:0"><thead><tr><th>Product</th><th>SKU</th><th class="num">Stock</th><th>Status</th></tr></thead><tbody id="lowStock"></tbody></table></div>
        </div>
      </div>

@endsection

@section('scripts')
<script src="{{ asset('admin-assets/js/dashboard.js') }}"></script>
@endsection
