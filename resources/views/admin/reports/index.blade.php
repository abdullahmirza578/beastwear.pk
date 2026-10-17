@extends('admin.layouts.layout')

@section('title', 'Sales Reports | BeastWear.pk Admin')
@section('page', 'reports')

@section('content')

<div class="page-header">
        <div><div class="breadcrumb"><a href="/admin/dashboard">Home</a><span class="sep">/</span><span>Reports</span><span class="sep">/</span><span>Sales Reports</span></div><h1>Sales Reports & Analytics</h1><p class="subtitle" id="rangeInfo">—</p></div>
        <div class="page-actions"><button class="btn btn-outline" id="exportCsv">Export CSV</button><button class="btn btn-outline" onclick="window.print()">Print</button></div>
      </div>
      <div class="card mb-24">
        <div class="toolbar">
          <div class="tabs" id="reportPeriod"><button class="tab active" data-period="daily">Daily</button><button class="tab" data-period="weekly">Weekly</button><button class="tab" data-period="monthly">Monthly</button><button class="tab" data-period="yearly">Yearly</button></div>
          <span class="spacer"></span>
          <div class="flex gap-6"><button class="btn btn-ghost btn-sm" data-range="7">7D</button><button class="btn btn-ghost btn-sm" data-range="30">30D</button><button class="btn btn-ghost btn-sm" data-range="90">90D</button><button class="btn btn-ghost btn-sm" data-range="365">1Y</button></div>
          <input type="date" class="form-control" id="dateFrom"><span class="text-muted">to</span><input type="date" class="form-control" id="dateTo">
          <button class="btn btn-primary" id="applyFilter">Apply</button>
        </div>
      </div>
      <div class="stats-grid cols-6" id="reportStats"></div>
      <div class="grid grid-2-1">
        <div class="card"><div class="card-header"><div class="card-title">Revenue<small>Delivered & in-progress orders</small></div></div><div class="card-body"><div id="revenueChart"></div></div></div>
        <div class="card"><div class="card-header"><div class="card-title">Order Volume</div></div><div class="card-body"><div id="ordersChart"></div></div></div>
      </div>
      <div class="grid grid-2 mt-24" id="analytics">
        <div class="card"><div class="card-header"><div class="card-title">Best Selling Products</div></div><div class="table-wrap"><table class="table" style="min-width:0"><thead><tr><th>Product</th><th class="num">Qty</th><th class="num">Revenue</th></tr></thead><tbody id="bestProducts"></tbody></table></div></div>
        <div class="card"><div class="card-header"><div class="card-title">Best Customers</div></div><div class="table-wrap"><table class="table" style="min-width:0"><thead><tr><th>Customer</th><th>City</th><th class="num">Orders</th><th class="num">Spent</th></tr></thead><tbody id="bestCustomers"></tbody></table></div></div>
        <div class="card"><div class="card-header"><div class="card-title">Sales by City</div></div><div class="table-wrap"><table class="table" style="min-width:0"><thead><tr><th>City</th><th class="num">Orders</th><th class="num">Revenue</th><th></th></tr></thead><tbody id="salesByCity"></tbody></table></div></div>
        <div class="card"><div class="card-header"><div class="card-title">Sales by Category</div></div><div class="card-body" id="salesByCategory"></div></div>
        <div class="card"><div class="card-header"><div class="card-title">Payment Methods</div></div><div class="card-body" id="paymentChart"></div></div>
      </div>

@endsection

@section('scripts')
<script src="{{ asset('admin-assets/js/dashboard.js') }}"></script>
@endsection
