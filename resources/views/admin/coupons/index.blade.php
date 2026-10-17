@extends('admin.layouts.layout')

@section('title', 'Coupons | BeastWear.pk Admin')
@section('page', 'coupons')

@section('content')

<div class="page-header">
        <div><div class="breadcrumb"><a href="/admin/dashboard">Home</a><span class="sep">/</span><span>Sales</span><span class="sep">/</span><span>Coupons</span></div><h1>Coupons</h1><p class="subtitle"><b id="sActive" style="color:var(--text)">0</b> active coupons · <b id="sUsed" style="color:var(--text)">0</b> total redemptions</p></div>
        <div class="page-actions"><button class="btn btn-primary" id="addCoupon">+ Create Coupon</button></div>
      </div>
      <div class="card">
        <div class="status-tabs" id="cpTabs"></div>
        <div class="toolbar"><div class="search-box"><svg class="icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.3-4.3"/></svg><input class="form-control" id="cpSearch" type="search" placeholder="Search coupon code…"></div></div>
        <div class="table-wrap">
          <table class="table" id="couponsTable">
            <thead><tr><th data-sort="code">Coupon Code</th><th data-sort="amount">Discount</th><th data-sort="type">Type</th><th data-sort="minOrder">Minimum Order</th><th data-sort="used">Usage</th><th data-sort="expiry">Expiry</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody></tbody>
          </table>
        </div>
        <div class="table-footer"><span id="pageInfo"></span><div id="pager"></div></div>
      </div>

@endsection

@section('scripts')
<script src="{{ asset('admin-assets/js/coupons.js') }}"></script>
@endsection
