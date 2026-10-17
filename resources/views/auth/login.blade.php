@extends('layouts.layout')

@section('title', 'Login — BeastWear.pk')

@section('meta')
    <meta name="description" content="Log in to your BeastWear.pk account to track orders, save your wishlist and check out faster.">
@endsection

@section('main-class', 'auth')

@section('content')

<!-- visual side -->
  <div class="auth-media">
    <div class="cap"><h3>Welcome to BeastWear.pk</h3><p style="color:#c9c8c4;margin:0">Sign in to manage your account and orders.</p></div>
  </div>

  <!-- form side -->
  <div class="auth-form-wrap">
    <div class="auth-box">
      <span class="eyebrow">Welcome back</span>
      <h1>Login</h1>
      <p>Sign in to your BeastWear.pk account.</p>

      @if ($errors->any())
        <div class="alert alert-err" role="alert">{{ $errors->first() }}</div>
      @endif

      <form id="login-form" method="POST" action="{{ route('login.store') }}">
        @csrf
        <div class="field"><label for="li-email">Email Address</label>
          <input type="email" id="li-email" name="email" value="{{ old('email') }}" placeholder="you@email.com" required autocomplete="username">
          <span class="err">Enter a valid email address.</span></div>
        <div class="field"><label for="li-pass">Password</label>
          <input type="password" id="li-pass" name="password" placeholder="••••••••" required autocomplete="current-password">
          <span class="err">Password must be at least 6 characters.</span></div>
        <div class="row-between" style="margin-bottom:18px">
          <label class="check"><input type="checkbox" name="remember" value="1" checked> Remember me</label>
          <a href="{{ route('forgot-password') }}" style="font-size:13px;text-decoration:underline">Forgot password?</a>
        </div>
        <button class="btn btn-dark btn-block" type="submit">Login</button>
      </form>

      <p class="auth-alt">New to BeastWear? <a href="{{ route('register', request()->filled('redirect') ? ['redirect' => request()->query('redirect')] : []) }}" style="text-decoration:underline">Create an account</a></p>
    </div>
  </div>

@endsection

@section('scripts')
@endsection
