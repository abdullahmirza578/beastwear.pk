@extends('admin.layouts.layout')

@section('title', 'Shipping Settings | BeastWear.pk Admin')
@section('page', 'shipping')

@section('content')

<form id="shippingForm" novalidate>
      <div class="page-header">
        <div><div class="breadcrumb"><a href="/admin/dashboard">Home</a><span class="sep">/</span><span>Administration</span><span class="sep">/</span><span>Shipping</span></div><h1>Shipping Settings</h1><p class="subtitle">Pakistan-wide delivery configuration.</p></div>
        <div class="page-actions"><button class="btn btn-primary" type="submit">Save Shipping Settings</button></div>
      </div>
      <div class="grid grid-2-1">
        <div>
          <div class="card">
            <div class="card-header"><div class="card-title">Delivery Zones & Charges</div></div>
            <div class="card-body">
              <div class="switch-list mb-24">
                <label class="switch"><input type="checkbox" name="nationwide"><span class="track"></span><span class="switch-label">Pakistan-wide delivery<small>Accept orders from all provinces, AJK and Gilgit-Baltistan</small></span></label>
              </div>
              <div class="form-row">
                <div class="form-group"><label class="form-label">Default Delivery Charge (Rs.)<span class="req">*</span></label><div class="input-group"><span class="prefix">Rs.</span><input type="number" class="form-control" name="defaultCharge" required min="0"></div></div>
                <div class="form-group"><label class="form-label">Free Delivery Threshold (Rs.)<span class="req">*</span></label><div class="input-group"><span class="prefix">Rs.</span><input type="number" class="form-control" name="freeThreshold" required min="0"></div><div class="form-hint">Orders at or above this subtotal ship free.</div></div>
              </div>
              <div class="form-row cols-3">
                <div class="form-group"><label class="form-label">Karachi Delivery Charge<span class="req">*</span></label><div class="input-group"><span class="prefix">Rs.</span><input type="number" class="form-control" name="karachiCharge" required min="0"></div></div>
                <div class="form-group"><label class="form-label">Other City Delivery Charge<span class="req">*</span></label><div class="input-group"><span class="prefix">Rs.</span><input type="number" class="form-control" name="otherCityCharge" required min="0"></div></div>
                <div class="form-group"><label class="form-label">Remote Area Charge</label><div class="input-group"><span class="prefix">Rs.</span><input type="number" class="form-control" name="remoteAreaCharge" min="0"></div></div>
              </div>
            </div>
          </div>
          <div class="card">
            <div class="card-header"><div class="card-title">Delivery Time & Courier</div></div>
            <div class="card-body">
              <div class="form-row cols-3">
                <div class="form-group"><label class="form-label">Estimated Days — Karachi</label><input class="form-control" name="karachiDays" placeholder="1-2"></div>
                <div class="form-group"><label class="form-label">Estimated Days — Other Cities</label><input class="form-control" name="otherDays" placeholder="3-5"></div>
                <div class="form-group"><label class="form-label">Default Courier</label><select class="form-control" name="courier"><option>TCS</option><option>Leopards</option><option>M&P</option><option>PostEx</option><option>Trax</option><option>Call Courier</option></select></div>
              </div>
            </div>
          </div>
          <div class="card">
            <div class="card-header"><div class="card-title">Cash on Delivery</div></div>
            <div class="card-body">
              <div class="switch-list mb-24"><label class="switch"><input type="checkbox" name="codEnabled"><span class="track"></span><span class="switch-label">COD available<small>Allow customers to pay cash on delivery</small></span></label></div>
              <div class="form-group" style="max-width:300px"><label class="form-label">COD Handling Fee (Rs.)</label><div class="input-group"><span class="prefix">Rs.</span><input type="number" class="form-control" name="codFee" min="0"></div><div class="form-hint">Set 0 for no extra charge.</div></div>
            </div>
          </div>
        </div>
        <div>
          <div class="card">
            <div class="card-header"><div class="card-title">Delivery Calculator<small>Preview what customers will be charged</small></div></div>
            <div class="card-body">
              <div class="form-group"><label class="form-label">Order Subtotal (Rs.)</label><input type="number" class="form-control" id="calcAmount" value="2499"></div>
              <div class="form-group"><label class="form-label">Destination</label><select class="form-control" id="calcCity"><option>Karachi</option><option>Other City</option><option>Remote</option></select></div>
              <div class="info-block" id="calcResult"></div>
            </div>
          </div>
          <div class="card"><div class="card-body"><div class="alert"><svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg><span>Saved shipping values are served from the database and applied to the storefront, cart, and checkout.</span></div></div></div>
        </div>
      </div>
      <div class="sticky-actions"><button class="btn btn-primary btn-lg" type="submit">Save Shipping Settings</button></div>
      </form>

@endsection

@section('scripts')
<script src="{{ asset('admin-assets/js/settings.js') }}"></script>
@endsection
