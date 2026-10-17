@extends('layouts.layout')

@section('title', 'Contact Us — BeastWear.pk')

@section('meta')
    <meta name="description" content="Contact BeastWear.pk: WhatsApp, phone, email, social media and business information.">
@endsection

@section('content')

<section class="page-hero">
    <div class="wrap">
      <div class="crumbs"><a href="index.html">Home</a><i>/</i><span>Contact</span></div>
      <h1>Get In Touch</h1>
      <p>Order help, sizing advice, wholesale or collaborations — we reply within minutes on WhatsApp.</p>
    </div>
  </section>

  <section class="section-sm">
    <div class="wrap">
      <div class="cart-layout">
        <!-- ============ FORM ============ -->
        <div class="panel">
          <h3>Send Us A Message</h3>
          @if (session('status'))
            <div class="alert alert-ok" role="status">{{ session('status') }}</div>
          @endif
          @if ($errors->any())
            <div class="alert alert-err" role="alert">{{ $errors->first() }}</div>
          @endif
          <form id="contact-form" method="POST" action="{{ route('contact.store') }}">
            @csrf
            <div class="form-grid">
              <div class="field"><label for="ct-name">Full Name</label>
                <input type="text" id="ct-name" name="name" value="{{ old('name') }}" placeholder="Your name" required>
                <span class="err">Please enter your name.</span></div>
              <div class="field"><label for="ct-phone">Mobile Number</label>
                <input type="tel" id="ct-phone" name="phone" value="{{ old('phone') }}" placeholder="03XX XXXXXXX">
                <span class="err">Enter a valid mobile number.</span></div>
            </div>
            <div class="field"><label for="ct-email">Email</label>
              <input type="email" id="ct-email" name="email" value="{{ old('email') }}" placeholder="you@email.com" required>
              <span class="err">Enter a valid email address.</span></div>
            <div class="field"><label for="ct-subject">Subject</label>
              <select id="ct-subject" name="subject" required>
                <option>Order / Delivery question</option>
                <option>Size &amp; fit advice</option>
                <option>Exchange or return</option>
                <option>Wholesale / bulk order</option>
                <option>Collaboration</option>
                <option>Other</option>
              </select></div>
            <div class="field"><label for="ct-body">Message</label>
              <textarea id="ct-body" name="message" placeholder="Tell us how we can help (include your order number if relevant)" required>{{ old('message') }}</textarea>
              <span class="err">Please write a short message.</span></div>
            <button class="btn btn-dark btn-block" type="submit">Send Message</button>
            <p class="hint mt-s">Your message will be saved securely for our support team.</p>
          </form>
        </div>

        <!-- ============ DETAILS ============ -->
        <div>
          <div class="panel">
            <h3>Contact Details</h3>
            <div class="icon-line" style="margin-bottom:16px"><span class="ico">
              <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2z"/></svg></span>
              <div><h4>WhatsApp (fastest)</h4><p><a href="{{ $siteSettings->whatsapp_url }}" target="_blank" rel="noopener">{{ $siteSettings->whatsapp }}</a> · 10 AM – 10 PM</p></div></div>
            <div class="icon-line" style="margin-bottom:16px"><span class="ico">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M5 3h3l2 5-2.5 1.5a11 11 0 0 0 5 5L14 12l5 2v3a2 2 0 0 1-2.2 2A16 16 0 0 1 3 5.2A2 2 0 0 1 5 3z"/></svg></span>
              <div><h4>Phone</h4><p>{{ $siteSettings->phone }} · Mon–Sat, 11 AM – 8 PM</p></div></div>
            <div class="icon-line" style="margin-bottom:16px"><span class="ico">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg></span>
              <div><h4>Email</h4><p>{{ $siteSettings->email }} · reply within 12 hours</p></div></div>
            <div class="icon-line"><span class="ico">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 21s7-6.1 7-11a7 7 0 1 0-14 0c0 4.9 7 11 7 11z"/><circle cx="12" cy="10" r="2.4"/></svg></span>
              <div><h4>Studio</h4><p>{{ $siteSettings->address }}</p></div></div>

            <div class="socials mt-m">
              @if ($siteSettings->instagram)<a href="{{ $siteSettings->instagram }}" target="_blank" rel="noopener" aria-label="Instagram">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="3.6"/><circle cx="17" cy="7" r="1" fill="currentColor"/></svg></a>@endif
              @if ($siteSettings->facebook)<a href="{{ $siteSettings->facebook }}" target="_blank" rel="noopener" aria-label="Facebook">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M14 9h3V6h-3c-2.2 0-4 1.8-4 4v2H8v3h2v7h3v-7h2.5l.5-3H13v-2c0-.6.4-1 1-1z"/></svg></a>@endif
              @if ($siteSettings->tiktok)<a href="{{ $siteSettings->tiktok }}" target="_blank" rel="noopener" aria-label="TikTok">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 3h-2.5v11.2a2.6 2.6 0 1 1-2.1-2.5V9.1A5.4 5.4 0 1 0 16 14.4V9.8a6.6 6.6 0 0 0 4 1.3V8.6A4.1 4.1 0 0 1 16 3z"/></svg></a>@endif
              @if ($siteSettings->youtube)<a href="{{ $siteSettings->youtube }}" target="_blank" rel="noopener" aria-label="YouTube">YouTube</a>@endif
              @if ($siteSettings->whatsapp)<a href="{{ $siteSettings->whatsapp_url }}" target="_blank" rel="noopener" aria-label="WhatsApp">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2z"/></svg></a>@endif
            </div>
          </div>

          <div class="panel mt-m">
            <h3>Business Information</h3>
            <div class="table-wrap"><table class="bw" style="min-width:0">
              <tbody>
                <tr><td class="bold">Brand</td><td>BeastWear.pk (BW)</td></tr>
                <tr><td class="bold">Category</td><td>Menswear &amp; kids clothing</td></tr>
                <tr><td class="bold">Established</td><td>2021, Lahore</td></tr>
                <tr><td class="bold">Support hours</td><td>10:00 – 22:00 (PKT), 7 days</td></tr>
                <tr><td class="bold">Wholesale</td><td>Minimum 25 pieces per design</td></tr>
                <tr><td class="bold">Payment</td><td>Cash on Delivery, bank transfer</td></tr>
              </tbody>
            </table></div>
          </div>

          <div class="panel panel-dark mt-m">
            <h3>Order Help</h3>
            <p style="color:#b6b5b1">Need to track a parcel or start an exchange?</p>
            <div style="display:grid;gap:10px">
              <a class="btn btn-light btn-sm" href="track-order.html">Track Order</a>
              <a class="btn btn-outline btn-sm" href="returns.html" style="color:#fff;border-color:#4a4a50">Exchange Policy</a>
              <a class="btn btn-wa btn-sm" href="https://wa.me/923001234567" target="_blank" rel="noopener">Chat on WhatsApp</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

@endsection
