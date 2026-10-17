@extends('admin.layouts.layout')

@section('title', 'Admin Users | BeastWear.pk Admin')
@section('page', 'admins')

@section('content')

<div class="page-header">
        <div><div class="breadcrumb"><a href="/admin/dashboard">Home</a><span class="sep">/</span><span>Administration</span><span class="sep">/</span><span>Admin Users</span></div><h1>Admin Users</h1><p class="subtitle">Manage staff accounts and access roles.</p></div>
        <div class="page-actions"><button class="btn btn-primary" id="addAdmin">+ Create Admin User</button></div>
      </div>
      <div class="grid grid-2-1">
        <div class="card">
          <div class="table-wrap">
            <table class="table" id="adminsTable">
              <thead><tr><th data-sort="name">Name</th><th data-sort="email">Email</th><th data-sort="role">Role</th><th data-sort="status">Status</th><th data-sort="lastLogin">Last Login</th><th>Actions</th></tr></thead>
              <tbody></tbody>
            </table>
          </div>
          <div class="table-footer"><span id="pageInfo"></span><div id="pager"></div></div>
        </div>
        <div class="card">
          <div class="card-header"><div class="card-title">Role Permissions</div></div>
          <div class="card-body">
            <div class="info-block mb-16"><h4>Super Admin</h4><p class="small text-muted">Full access to everything including settings, admin users, shipping and store configuration.</p></div>
            <div class="info-block mb-16"><h4>Admin</h4><p class="small text-muted">Manage products, categories, inventory, orders, customers, coupons, reviews and homepage content.</p></div>
            <div class="info-block"><h4>Manager</h4><p class="small text-muted">Process orders, update order status, view customers and reports. No catalog or settings access.</p></div>
            <div class="alert mt-16"><svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg><span>Role enforcement should be implemented server-side when connecting the backend (e.g. Laravel policies / middleware).</span></div>
          </div>
        </div>
      </div>

@endsection

@section('scripts')
<script src="{{ asset('admin-assets/js/settings.js') }}"></script>
@endsection
