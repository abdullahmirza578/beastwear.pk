@extends('admin.layouts.layout')

@section('title', 'Settings | BeastWear.pk Admin')
@section('page', 'settings')

@section('content')

<form id="settingsForm" novalidate>
      <div class="page-header">
        <div><div class="breadcrumb"><a href="/admin/dashboard">Home</a><span class="sep">/</span><span>Administration</span><span class="sep">/</span><span>Settings</span></div><h1>General Settings</h1><p class="subtitle">Store identity, contact details, social links and website behaviour.</p></div>
        <div class="page-actions"><button class="btn btn-primary" type="submit">Save Settings</button></div>
      </div>
      <div class="grid grid-1-2" style="grid-template-columns:240px 1fr">
        <div class="card" style="align-self:start;position:sticky;top:88px">
          <div class="card-body settings-nav">
            <button type="button" class="active" data-target="store">Store Information</button>
            <button type="button" data-target="social">Social Media</button>
            <button type="button" data-target="order">Order Settings</button>
            <button type="button" data-target="website">Website Settings</button>
            <a class="btn btn-outline btn-sm mt-16" href="{{ route('admin.shipping-settings') }}">Shipping Settings →</a>
          </div>
        </div>
        <div>
          <div class="card" data-section="store">
            <div class="card-header"><div class="card-title">Store Information</div></div>
            <div class="card-body">
              <div class="form-row">
                <div class="form-group"><label class="form-label">Store Name<span class="req">*</span></label><input class="form-control" name="storeName" required></div>
                <div class="form-group"><label class="form-label">Tagline</label><input class="form-control" name="tagline"></div>
              </div>
              <div class="form-row">
                <div class="form-group"><label class="form-label">Logo</label><div class="flex"><div id="logoPrev"></div><div class="upload-box" style="flex:1;padding:14px"><input type="file" id="logoFile" accept="image/*"><strong>Upload logo</strong><small>SVG/PNG, transparent</small></div></div></div>
                <div class="form-group"><label class="form-label">Favicon</label><div class="flex"><div id="favPrev"></div><div class="upload-box" style="flex:1;padding:14px"><input type="file" id="favFile" accept="image/*"><strong>Upload favicon</strong><small>64×64px PNG</small></div></div></div>
              </div>
              <div class="form-row cols-3">
                <div class="form-group"><label class="form-label">Support Email<span class="req">*</span></label><input class="form-control" type="email" name="email" required></div>
                <div class="form-group"><label class="form-label">Phone<span class="req">*</span></label><input class="form-control" name="phone" required></div>
                <div class="form-group"><label class="form-label">WhatsApp<span class="req">*</span></label><input class="form-control" name="whatsapp" required data-validate="phone"></div>
              </div>
              <div class="form-group"><label class="form-label">Store Address</label><textarea class="form-control" name="address" style="min-height:80px"></textarea></div>
            </div>
          </div>
          <div class="card hidden" data-section="social">
            <div class="card-header"><div class="card-title">Social Media</div></div>
            <div class="card-body">
              <div class="form-row">
                <div class="form-group"><label class="form-label">Instagram</label><input class="form-control" name="instagram" placeholder="https://instagram.com/…"></div>
                <div class="form-group"><label class="form-label">Facebook</label><input class="form-control" name="facebook" placeholder="https://facebook.com/…"></div>
                <div class="form-group"><label class="form-label">TikTok</label><input class="form-control" name="tiktok" placeholder="https://tiktok.com/@…"></div>
                <div class="form-group"><label class="form-label">YouTube</label><input class="form-control" name="youtube" placeholder="https://youtube.com/@…"></div>
              </div>
            </div>
          </div>
          <div class="card hidden" data-section="order">
            <div class="card-header"><div class="card-title">Order Settings</div></div>
            <div class="card-body">
              <div class="form-row">
                <div class="form-group"><label class="form-label">Minimum Order (Rs.)</label><div class="input-group"><span class="prefix">Rs.</span><input type="number" class="form-control" name="minOrder" min="0"></div></div>
                <div class="form-group"><label class="form-label">Order Confirmation Method</label><select class="form-control" name="orderConfirmation"><option value="whatsapp">WhatsApp message</option><option value="call">Phone call</option><option value="sms">SMS</option><option value="email">Email only</option><option value="none">No confirmation required</option></select></div>
              </div>
              <div class="switch-list">
                <label class="switch"><input type="checkbox" name="codEnabled"><span class="track"></span><span class="switch-label">Cash on Delivery enabled<small>Customers can choose COD at checkout</small></span></label>
                <label class="switch"><input type="checkbox" name="autoConfirmPrepaid"><span class="track"></span><span class="switch-label">Auto-confirm prepaid orders<small>Manual transfer receipts are always verified by an admin before payment is marked paid.</small></span></label>
                <label class="switch"><input type="checkbox" name="lowStockAlerts"><span class="track"></span><span class="switch-label">Low stock email alerts</span></label>
              </div>
              <div class="card-header" style="padding-left:0;padding-right:0"><div class="card-title">Prepaid Transfer Accounts</div></div>
              <p class="form-hint">Only methods with all required account details are offered at checkout. Enter the real business account details before enabling a method.</p>
              <div class="form-row cols-3">
                <div class="form-group"><label class="form-label">Bank Name</label><input class="form-control" name="bankName"></div>
                <div class="form-group"><label class="form-label">Bank Account Title</label><input class="form-control" name="bankAccountTitle"></div>
                <div class="form-group"><label class="form-label">Bank Account Number / IBAN</label><input class="form-control" name="bankAccountNumber"></div>
              </div>
              <div class="form-row">
                <div class="form-group"><label class="form-label">JazzCash Account Title</label><input class="form-control" name="jazzcashAccountTitle"></div>
                <div class="form-group"><label class="form-label">JazzCash Account Number</label><input class="form-control" name="jazzcashAccountNumber"></div>
                <div class="form-group"><label class="form-label">EasyPaisa Account Title</label><input class="form-control" name="easypaisaAccountTitle"></div>
                <div class="form-group"><label class="form-label">EasyPaisa Account Number</label><input class="form-control" name="easypaisaAccountNumber"></div>
              </div>
            </div>
          </div>
          <div class="card hidden" data-section="website">
            <div class="card-header"><div class="card-title">Website Settings</div></div>
            <div class="card-body">
              <div class="form-row">
                <div class="form-group"><label class="form-label">Currency</label><select class="form-control" name="currency"><option value="PKR">PKR — Pakistani Rupee (Rs.)</option></select><div class="form-hint">Store prices and checkout are configured in Pakistani Rupees.</div></div>
                <div class="form-group"><label class="form-label">Timezone</label><select class="form-control" name="timezone"><option value="Asia/Karachi">Asia/Karachi (PKT, UTC+5)</option><option value="Asia/Dubai">Asia/Dubai (UTC+4)</option><option value="UTC">UTC</option></select></div>
              </div>
              <div class="switch-list"><label class="switch"><input type="checkbox" name="maintenance"><span class="track"></span><span class="switch-label">Maintenance Mode<small>Hide the storefront and show a "We'll be back soon" page. Admin stays accessible.</small></span></label></div>
            </div>
          </div>
        </div>
      </div>
      <div class="sticky-actions"><button class="btn btn-primary btn-lg" type="submit">Save Settings</button></div>
      </form>

@endsection

@section('scripts')
<script src="{{ asset('admin-assets/js/settings.js') }}"></script>
@endsection
