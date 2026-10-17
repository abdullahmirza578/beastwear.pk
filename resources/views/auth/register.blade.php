@extends('layouts.layout')

@section('title', 'Create Account — BeastWear.pk')

@section('meta')
    <meta name="description" content="Create your BeastWear.pk account for faster checkout, order tracking and wishlist sync.">
@endsection

@section('main-class', 'auth')

@section('content')

<div class="auth-media">
    <div class="cap"><h3>Create your account</h3><p style="color:#c9c8c4;margin:0">Keep your orders and wishlist together.</p></div>
  </div>

  <div class="auth-form-wrap">
    <div class="auth-box">
      <span class="eyebrow">Create account</span>
      <h1>Register</h1>
      <p>It takes less than a minute.</p>

      @if ($errors->any())
        <div class="alert alert-err" role="alert">{{ $errors->first() }}</div>
      @endif

      <form id="register-form" method="POST" action="{{ route('register.store') }}">
        @csrf
        <div class="field"><label for="rg-name">Full Name</label>
          <input type="text" id="rg-name" name="name" value="{{ old('name') }}" placeholder="e.g. Ahmed Raza" required autocomplete="name">
          <span class="err">Please enter your name.</span></div>
        <div class="field"><label for="rg-email">Email Address</label>
          <input type="email" id="rg-email" name="email" value="{{ old('email') }}" placeholder="you@email.com" required autocomplete="email">
          <span class="err">Enter a valid email address.</span></div>
        <div class="field"><label for="rg-phone">Mobile Number <small>(optional)</small></label>
          <input type="tel" id="rg-phone" name="phone" placeholder="03XX XXXXXXX">
          <span class="err">Enter a valid mobile number.</span></div>
        <div class="form-grid">
          <div class="field"><label for="rg-pass">Password</label>
            <input type="password" id="rg-pass" name="password" placeholder="Min 8 characters" required minlength="8" autocomplete="new-password">
            <span class="err">At least 8 characters.</span></div>
          <div class="field"><label for="rg-pass2">Confirm Password</label>
            <input type="password" id="rg-pass2" name="password_confirmation" placeholder="Repeat password" required minlength="8" autocomplete="new-password">
            <span class="err">Passwords must match.</span></div>
        </div>
        <label class="check" style="margin-bottom:18px"><input type="checkbox" name="terms" value="1" required>
          I agree to the <a href="terms.html" style="text-decoration:underline">Terms &amp; Conditions</a> and
          <a href="{{ route('privacy') }}" style="text-decoration:underline">Privacy Policy</a></label>
        <button class="btn btn-dark btn-block" type="submit">Create Account</button>
      </form>

      <p class="auth-alt">Already have an account? <a href="{{ route('login') }}" style="text-decoration:underline">Login here</a></p>
    </div>
  </div>

@endsection

@section('scripts')
@endsection
