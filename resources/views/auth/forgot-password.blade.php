@extends('layouts.layout')

@section('title', 'Forgot Password — BeastWear.pk')

@section('meta')
    <meta name="description" content="Reset your BeastWear.pk account password.">
@endsection

@section('main-class', 'auth')

@section('content')

<div class="auth-media">
    <div class="cap"><h3>Account Recovery</h3><p style="color:#c9c8c4;margin:0">We will email you a secure reset link.</p></div>
  </div>

  <div class="auth-form-wrap">
    <div class="auth-box">
      <span class="eyebrow">Password help</span>
      <h1>Forgot Password</h1>
      <p>Enter the email you registered with and we will send you a reset link.</p>

      @if (session('status'))
        <div class="alert" role="status">{{ session('status') }}</div>
      @endif
      @if (session('reset_link'))
        <div class="alert" role="status">
          Local development only: email is not sent. Use this reset link:
          <a href="{{ session('reset_link') }}">Reset your password</a>
        </div>
      @endif
      @if ($errors->any())
        <div class="alert alert-err" role="alert">{{ $errors->first() }}</div>
      @endif

      <form id="forgot-form" method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="field"><label for="fp-email">Email Address</label>
          <input type="email" id="fp-email" name="email" value="{{ old('email') }}" placeholder="you@email.com" autocomplete="email" required>
        </div>
        <button class="btn btn-dark btn-block" type="submit">Send Reset Link</button>
      </form>

      <p class="auth-alt"><a href="{{ route('login') }}" style="text-decoration:underline">Back to login</a> ·
        <a href="{{ route('register') }}" style="text-decoration:underline">Create account</a></p>
    </div>
  </div>

@endsection
