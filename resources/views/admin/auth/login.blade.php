@extends('admin.layouts.auth')

@section('title', 'Admin Login | BeastWear.pk')
@section('page', 'login')

@section('content')

<div class="auth-page">
  <section class="auth-visual">
    <div class="flex"><div class="brand-mark" style="background:#fff;color:#000">BW</div><div class="brand-text"><strong>BeastWear.pk</strong><span>Admin Panel</span></div></div>
    <div>
      <h2>Run the brand.<br>Own the numbers.</h2>
      <p>Manage products, orders, inventory, customers and the storefront of BeastWear.pk from one secure dashboard.</p>
      <div class="auth-stats"><div><strong>Rs. 2.4M+</strong><span>Monthly Sales</span></div><div><strong>128</strong><span>Orders / mo</span></div><div><strong>Pakistan-wide</strong><span>Delivery</span></div></div>
    </div>
    <p class="small" style="color:rgba(255,255,255,.4)">© <span id="year"></span> BeastWear.pk — Restricted access. Authorized staff only.</p>
  </section>
  <section class="auth-form-wrap">
    <div class="auth-card">
      <div class="brand-mark">BW</div>
      <h1>BeastWear.pk Admin</h1>
      <p class="lead">Sign in to continue to the dashboard.</p>
      @if ($errors->any())
        <div class="alert alert-warning" role="alert">{{ $errors->first() }}</div>
      @endif
      <form id="loginForm" method="POST" action="{{ route('admin.login.store') }}">
        @csrf
        <div class="form-group"><label class="form-label" for="email">Email Address</label><input class="form-control" type="email" id="email" name="email" value="{{ old('email') }}" placeholder="admin@beastwear.pk" required autocomplete="username"></div>
        <div class="form-group"><label class="form-label" for="password">Password</label><div style="position:relative"><input class="form-control" type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password" style="padding-right:44px"><button type="button" class="icon-btn" id="togglePw" style="position:absolute;right:2px;top:1px;height:38px;width:38px" aria-label="Show password">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
        </button></div></div>
        <div class="flex-between mb-24"><label class="form-check"><input type="checkbox" id="remember" name="remember" value="1" checked> Remember me</label></div>
        <button class="btn btn-primary btn-lg btn-block" type="submit" id="loginBtn">Login to Dashboard</button>
      </form>
      <p class="auth-footer">Protected area · All activity is logged</p>
    </div>
  </section>
</div>

@endsection

@section('scripts')
<script>
    document.getElementById('year').textContent = new Date().getFullYear();
    document.getElementById('togglePw').addEventListener('click', function () {
        const password = document.getElementById('password');
        password.type = password.type === 'password' ? 'text' : 'password';
    });
</script>
@endsection
