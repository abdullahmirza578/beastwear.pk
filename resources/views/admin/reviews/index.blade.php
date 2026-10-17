@extends('admin.layouts.layout')

@section('title', 'Reviews | BeastWear.pk Admin')
@section('page', 'reviews')

@section('content')

<div class="page-header">
        <div><div class="breadcrumb"><a href="{{ route('admin.dashboard') }}">Home</a><span class="sep">/</span><span>Catalog</span><span class="sep">/</span><span>Reviews</span></div><h1>Product Reviews</h1><p class="subtitle">Moderate customer feedback before it appears on the storefront.</p></div>
        <div class="page-actions"><button class="btn btn-primary" id="approveAll">Approve All Pending</button></div>
      </div>
      <div class="grid grid-2-1">
        <div class="card">
          <div class="status-tabs" id="rTabs"></div>
          <div class="toolbar"><div class="search-box"><svg class="icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.3-4.3"/></svg><input class="form-control" id="rSearch" type="search" placeholder="Search customer, product or review text…"></div><select class="form-control" id="rRating"><option value="">All Ratings</option><option value="5">★★★★★</option><option value="4">★★★★</option><option value="3">★★★</option><option value="2">★★</option><option value="1">★</option></select></div>
          <div id="reviewList"></div>
        </div>
        <div>
          <div class="stats-grid" style="grid-template-columns:1fr 1fr">
            <div class="stat-card accent"><div class="stat-top"><span class="stat-label">Avg. Rating</span></div><div class="stat-value" id="sAvg">—</div></div>
            <div class="stat-card"><div class="stat-top"><span class="stat-label">Pending</span></div><div class="stat-value" id="sPending">—</div></div>
          </div>
          <div class="card"><div class="card-header"><div class="card-title">Rating Distribution<small><span id="sTotal">0</span> reviews total</small></div></div><div class="card-body" id="ratingDist"></div></div>
        </div>
      </div>

@endsection

@section('scripts')
<script src="{{ asset('admin-assets/js/reviews.js') }}"></script>
@endsection
