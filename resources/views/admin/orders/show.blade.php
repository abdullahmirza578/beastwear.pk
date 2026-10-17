@extends('admin.layouts.layout')

@section('title', 'Order Details | BeastWear.pk Admin')
@section('page', 'order-details')

@section('content')

<div class="page-header no-print">
        <div><div class="breadcrumb"><a href="/admin/dashboard">Home</a><span class="sep">/</span><a href="/admin/orders">Orders</a><span class="sep">/</span><span>Details</span></div><h1>Order <span id="orderId">—</span></h1><p class="subtitle" id="orderMeta"></p></div>
        <div class="page-actions"><a class="btn btn-ghost" href="/admin/orders">← Back</a><button class="btn btn-outline" id="contactCustomer">CONTACT CUSTOMER</button><button class="btn btn-outline" id="printInvoice">PRINT INVOICE</button><button class="btn btn-primary" id="updateOrder">UPDATE ORDER</button></div>
      </div>

      <div class="print-only card"><div class="card-body" id="invoice"></div></div>

      <div class="grid grid-2-1 no-print">
        <div>
          <div class="card">
            <div class="card-header"><div class="card-title">Products</div></div>
            <div class="table-wrap"><table class="table"><thead><tr><th>Product</th><th>Design</th><th>Size</th><th>Color</th><th class="num">Qty</th><th class="num">Price</th><th class="num">Total</th></tr></thead><tbody id="itemsBody"></tbody></table></div>
            <div class="card-footer" style="justify-content:flex-end"><div class="summary-rows" style="min-width:300px" id="summary"></div></div>
          </div>
          <div class="grid grid-2">
            <div class="card"><div class="card-header"><div class="card-title">Customer Information</div></div><div class="card-body" id="custInfo"></div></div>
            <div class="card"><div class="card-header"><div class="card-title">Delivery Address</div></div><div class="card-body" id="addrInfo"></div></div>
          </div>
        </div>
        <div>
          <div class="card">
            <div class="card-header"><div class="card-title">Order Status</div></div>
            <div class="card-body">
              <div class="form-group"><label class="form-label">Status</label><select class="form-control" id="fStatus"><option>Pending</option><option>Confirmed</option><option>Processing</option><option>Packed</option><option>Shipped</option><option>Out for Delivery</option><option>Delivered</option><option>Cancelled</option><option>Returned</option><option>Exchange Requested</option></select></div>
              <div class="form-group"><label class="form-label">Payment Status</label><select class="form-control" id="fPayStatus"><option>Unpaid</option><option>Paid</option><option>Refunded</option><option>Rejected</option></select></div>
              <div class="form-row"><div class="form-group"><label class="form-label">Courier</label><select class="form-control" id="fCourier"><option>TCS</option><option>Leopards</option><option>M&P</option><option>PostEx</option><option>Trax</option><option>Call Courier</option></select></div><div class="form-group"><label class="form-label">Tracking Number</label><input class="form-control mono" id="fTracking" placeholder="e.g. 4001234567"></div></div>
              <div class="form-group"><label class="form-label">Admin Notes</label><textarea class="form-control" id="fAdminNotes" style="min-height:80px" placeholder="Internal notes (not visible to customer)"></textarea></div>
              <div class="form-group"><label class="form-label">Customer Notes</label><textarea class="form-control" id="fCustNotes" style="min-height:70px"></textarea></div>
              <button class="btn btn-primary btn-block" onclick="document.getElementById('updateOrder').click()">UPDATE ORDER</button>
              <button class="btn btn-danger btn-block mt-8" id="cancelOrder">Cancel Order</button>
            </div>
          </div>
          <div class="card"><div class="card-header"><div class="card-title">Order Timeline</div></div><div class="card-body"><div class="timeline" id="timeline"></div></div></div>
        </div>
      </div>

@endsection

@section('scripts')
<script src="{{ asset('admin-assets/js/orders.js') }}"></script>
@endsection
