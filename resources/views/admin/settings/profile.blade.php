@extends('admin.layouts.layout')

@section('title', 'My Profile | BeastWear.pk Admin')
@section('page', 'profile')

@section('content')

<div class="page-header">
        <div><div class="breadcrumb"><a href="/admin/dashboard">Home</a><span class="sep">/</span><span>Account</span><span class="sep">/</span><span>My Profile</span></div><h1>My Profile</h1><p class="subtitle">Manage your personal details and password.</p></div>
      </div>
      <div class="grid grid-1-2">
        <div>
          <div class="card">
            <div class="card-body" style="text-align:center">
              <div id="avatarPrev" style="display:flex;justify-content:center;margin-bottom:14px"></div>
              <div id="profileHead"></div>
              <div class="flex mt-24" style="justify-content:center;gap:8px"><label class="btn btn-outline btn-sm" style="position:relative;overflow:hidden">Upload Photo<input type="file" id="avatarFile" accept="image/*" style="position:absolute;inset:0;opacity:0;cursor:pointer"></label><button class="btn btn-ghost btn-sm" id="removeAvatar">Remove</button></div>
            </div>
          </div>
          <div class="card"><div class="card-header"><div class="card-title">Recent Activity</div></div><div class="card-body"><div class="timeline" id="activity"></div></div></div>
        </div>
        <div>
          <div class="card">
            <div class="card-header"><div class="card-title">Profile Information</div></div>
            <form id="profileForm" class="card-body" novalidate>
              <div class="form-row"><div class="form-group"><label class="form-label">Full Name<span class="req">*</span></label><input class="form-control" name="name" required></div><div class="form-group"><label class="form-label">Phone</label><input class="form-control" name="phone" data-validate="phone" placeholder="03xx-xxxxxxx"></div></div>
              <div class="form-group"><label class="form-label">Email<span class="req">*</span></label><input class="form-control" type="email" name="email" required></div>
              <div class="form-actions"><button class="btn btn-primary" type="submit">UPDATE PROFILE</button></div>
            </form>
          </div>
          <div class="card">
            <div class="card-header"><div class="card-title">Change Password</div></div>
            <form id="passwordForm" class="card-body" novalidate>
              <div class="form-group"><label class="form-label">Current Password<span class="req">*</span></label><input class="form-control" type="password" name="current" required autocomplete="current-password"></div>
              <div class="form-row"><div class="form-group"><label class="form-label">New Password<span class="req">*</span></label><input class="form-control" type="password" name="newPass" required minlength="8" autocomplete="new-password"><div class="mt-8" id="pwStrength"></div></div><div class="form-group"><label class="form-label">Confirm New Password<span class="req">*</span></label><input class="form-control" type="password" name="confirm" required data-match="[name=newPass]" autocomplete="new-password"></div></div>
              <div class="form-hint mb-16">Use at least 8 characters with a mix of upper/lowercase letters, numbers and symbols.</div>
              <div class="form-actions"><button class="btn btn-primary" type="submit">CHANGE PASSWORD</button></div>
            </form>
          </div>
        </div>
      </div>

@endsection

@section('scripts')
<script src="{{ asset('admin-assets/js/settings.js') }}"></script>
@endsection
