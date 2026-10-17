@extends('admin.layouts.layout')

@section('title', 'Customers | BeastWear.pk Admin')
@section('page', 'customers')

@section('content')

<div class="page-header">
        <div><div class="breadcrumb"><a href="/admin/dashboard">Home</a><span class="sep">/</span><span>Sales</span><span class="sep">/</span><span>Customers</span></div><h1>Customers</h1><p class="subtitle">Registered customers and their purchase history.</p></div>
      </div>
      <div class="stats-grid">
        <div class="stat-card"><div class="stat-top"><span class="stat-label">Total Customers</span></div><div class="stat-value" id="sCount">—</div></div>
        <div class="stat-card"><div class="stat-top"><span class="stat-label">Active</span></div><div class="stat-value" id="sActive">—</div></div>
        <div class="stat-card"><div class="stat-top"><span class="stat-label">Repeat Buyers</span></div><div class="stat-value" id="sRepeat">—</div></div>
        <div class="stat-card"><div class="stat-top"><span class="stat-label">Avg. Lifetime Value</span></div><div class="stat-value" id="sAvg">—</div></div>
      </div>
      <div class="card">
        <div class="toolbar">
          <div class="search-box"><svg class="icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.3-4.3"/></svg><input class="form-control" id="cSearch" type="search" placeholder="Search name, phone or email…"></div>
          <select class="form-control" id="cStatus"><option value="">All Status</option><option>Active</option><option>Blocked</option></select>
          <select class="form-control" id="cCity"></select>
          <select class="form-control" id="cSort"><option value="spending">Sort: Top Spending</option><option value="total">Sort: Most Orders</option><option value="last">Sort: Recent Order</option><option value="name">Sort: Name A–Z</option></select>
        </div>
        <div class="table-wrap">
          <table class="table" id="customersTable">
            <thead><tr><th data-sort="name">Customer</th><th>Phone</th><th>Email</th><th data-sort="city">City</th><th data-sort="total" class="num">Total Orders</th><th data-sort="spending" class="num">Total Spending</th><th data-sort="last">Last Order</th><th data-sort="status">Status</th><th>Actions</th></tr></thead>
            <tbody></tbody>
          </table>
        </div>
        <div class="table-footer"><span id="pageInfo"></span><div id="pager"></div></div>
      </div>

@endsection

@section('scripts')
<script src="{{ asset('admin-assets/js/customers.js') }}"></script>
@endsection
