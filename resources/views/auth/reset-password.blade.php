@extends('layouts.layout')

@section('title', 'Reset Password — BeastWear.pk')

@section('meta')
    <meta name="description" content="Choose a new password for your BeastWear.pk account.">
@endsection

@section('main-class', 'auth')

@section('content')
<div class="auth-media">
    <div class="cap"><h3>Choose a New Password</h3><p style="color:#c9c8c4;margin:0">Secure your BeastWear account.</p></div>
</div>

<div class="auth-form-wrap">
    <div class="auth-box">
        <span class="eyebrow">Account recovery</span>
        <h1>Reset Password</h1>
        @if ($errors->any())<div class="alert alert-err" role="alert">{{ $errors->first() }}</div>@endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="field">
                <label for="reset-email">Email Address</label>
                <input type="email" id="reset-email" name="email" value="{{ old('email', $email) }}" autocomplete="email" required>
            </div>
            <div class="field">
                <label for="reset-password">New Password</label>
                <input type="password" id="reset-password" name="password" autocomplete="new-password" required>
            </div>
            <div class="field">
                <label for="reset-password-confirmation">Confirm New Password</label>
                <input type="password" id="reset-password-confirmation" name="password_confirmation" autocomplete="new-password" required>
            </div>
            <button class="btn btn-dark btn-block" type="submit">Reset Password</button>
        </form>
        <p class="auth-alt"><a href="{{ route('login') }}" style="text-decoration:underline">Back to login</a></p>
    </div>
</div>
@endsection
