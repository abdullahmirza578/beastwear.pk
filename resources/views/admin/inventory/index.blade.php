@extends('admin.layouts.layout')

@section('title', 'Inventory | BeastWear.pk Admin')
@section('page', 'inventory')

@section('content')

<div class="page-header">
        <div><div class="breadcrumb"><a href="{{ route('admin.dashboard') }}">Home</a><span class="sep">/</span><span>Catalog</span><span class="sep">/</span><span>Inventory</span></div><h1>Inventory</h1><p class="subtitle">Track stock levels and update quantities.</p></div>
        <div class="page-actions"><button class="btn btn-outline" id="exportInv">Export CSV</button><a class="btn btn-primary" href="{{ route('admin.products.create') }}">+ Add Product</a></div>
      </div>
      <div class="stats-grid">
        <div class="stat-card"><div class="stat-top"><span class="stat-label">Units in Stock</span></div><div class="stat-value" id="sUnits">—</div></div>
        <div class="stat-card"><div class="stat-top"><span class="stat-label">Stock Value (Cost)</span></div><div class="stat-value" id="sValue">—</div></div>
        <div class="stat-card"><div class="stat-top"><span class="stat-label">Retail Value</span></div><div class="stat-value" id="sRetail">—</div></div>
        <div class="stat-card"><div class="stat-top"><span class="stat-label">Stock Alerts</span></div><div class="stat-value text-warning" id="sAlerts">—</div></div>
      </div>
      <div class="card">
        <div class="status-tabs" id="stockTabs"></div>
        <div class="toolbar">
          <div class="search-box"><svg class="icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.3-4.3"/></svg><input class="form-control" id="iSearch" type="search" placeholder="Search product or SKU…"></div>
          <select class="form-control" id="iCategory"></select>
        </div>
        <div class="table-wrap">
          <table class="table" id="invTable">
            <thead><tr><th data-sort="name">Product</th><th data-sort="sku">SKU</th><th data-sort="category">Category</th><th data-sort="stock">Current Stock</th><th data-sort="sold" class="num">Sold</th><th class="num">Available</th><th class="num">Low Stock Threshold</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody></tbody>
          </table>
        </div>
        <div class="table-footer"><span id="pageInfo"></span><div id="pager"></div></div>
      </div>

@endsection

@section('scripts')
<script src="{{ asset('admin-assets/js/inventory.js') }}"></script>
@endsection
