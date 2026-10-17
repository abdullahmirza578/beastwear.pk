@extends('admin.layouts.layout')

@section('title', 'Homepage & Banners | BeastWear.pk Admin')
@section('page', 'banners')

@section('content')

<div class="page-header">
        <div><div class="breadcrumb"><a href="/admin/dashboard">Home</a><span class="sep">/</span><span>Website</span><span class="sep">/</span><span>Homepage & Banners</span></div><h1>Homepage & Banners</h1><p class="subtitle">Control what customers see on the BeastWear.pk homepage.</p></div>
      </div>

      <div class="card">
        <div class="card-header"><div class="card-title">Hero Banner<small>Main banner at the top of the homepage</small></div></div>
        <div class="card-body">
          <form id="heroForm" enctype="multipart/form-data" novalidate>
          <div class="grid grid-2">
            <div>
              <div class="form-group"><label class="form-label">Heading<span class="req">*</span></label><input class="form-control" name="heading" required></div>
              <div class="form-group"><label class="form-label">Description</label><textarea class="form-control" name="description" style="min-height:80px"></textarea></div>
              <div class="form-row"><div class="form-group"><label class="form-label">Button Text<span class="req">*</span></label><input class="form-control" name="buttonText" required></div><div class="form-group"><label class="form-label">Button Link<span class="req">*</span></label><input class="form-control mono" name="buttonLink" required placeholder="/collections/new-arrivals"></div></div>
              <div class="form-row"><div class="form-group"><label class="form-label">Desktop Image</label><div class="upload-box" style="padding:16px"><input type="file" id="heroDesktop" name="desktopImage" accept="image/*"><strong>Upload desktop image</strong><small>1920×800px</small></div></div><div class="form-group"><label class="form-label">Mobile Image</label><div class="upload-box" style="padding:16px"><input type="file" id="heroMobile" name="mobileImage" accept="image/*"><strong>Upload mobile image</strong><small>800×1000px</small></div></div></div>
              <div class="flex-between"><label class="switch"><input type="checkbox" name="active"><span class="track"></span><span class="switch-label">Hero banner active</span></label><button class="btn btn-primary" type="submit">Save Hero Banner</button></div>
            </div>
            <div>
              <label class="form-label">Desktop Preview</label><div class="hero-preview mb-16" id="heroPrev"></div>
              <label class="form-label">Mobile Preview</label><img id="mobPrev" alt="" style="width:120px;aspect-ratio:4/5;object-fit:cover;border-radius:10px;border:1px solid var(--border);filter:grayscale(.5)">
            </div>
          </div>
          </form>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><div class="card-title">Promotional Banners<small>Secondary banners placed across the homepage</small></div><button class="btn btn-outline btn-sm" id="addPromo">+ Add Banner</button></div>
        <div id="promoList"></div>
      </div>

      <div class="card">
        <div class="card-header"><div class="card-title">Featured Categories<small>Choose which categories appear on the homepage</small></div><button class="btn btn-primary btn-sm" id="saveCats">Save Categories</button></div>
        <div class="card-body"><div class="selectable-grid" id="catSelect"></div></div>
      </div>

      <div class="grid grid-3">
        <div class="card">
          <div class="card-header"><div class="card-title">Featured Products<small><span id="featuredCount">0</span>/8 selected</small></div><button class="btn btn-primary btn-sm" id="saveFeatured">Save</button></div>
          <div class="toolbar"><input class="form-control w-100" placeholder="Filter products…"></div>
          <div class="card-body" style="max-height:420px;overflow:auto"><div class="check-list" id="featuredSel"></div></div>
        </div>
        <div class="card">
          <div class="card-header"><div class="card-title">New Arrivals<small><span id="newCount">0</span>/8 selected</small></div><button class="btn btn-primary btn-sm" id="saveNew">Save</button></div>
          <div class="toolbar"><input class="form-control w-100" placeholder="Filter products…"></div>
          <div class="card-body" style="max-height:420px;overflow:auto"><div class="check-list" id="newSel"></div></div>
        </div>
        <div class="card">
          <div class="card-header"><div class="card-title">Best Sellers<small><span id="bestCount">0</span>/8 selected</small></div><button class="btn btn-primary btn-sm" id="saveBest">Save</button></div>
          <div class="toolbar"><input class="form-control w-100" placeholder="Filter products…"></div>
          <div class="card-body" style="max-height:420px;overflow:auto"><div class="check-list" id="bestSel"></div></div>
        </div>
      </div>

@endsection

@section('scripts')
<script src="{{ asset('admin-assets/js/banners.js') }}"></script>
@endsection
