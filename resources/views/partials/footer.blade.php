<div id="site-footer">
    <footer class="site-footer"><div class="wrap">
        <div class="footer-top">
            <div class="footer-brand">
                <div class="footer-logo">
                    @if ($siteSettings?->logo_url)
                        <img src="{{ $siteSettings->logo_url }}" alt="{{ $siteSettings->store_name }}" style="max-width:64px;max-height:42px;object-fit:contain">
                    @else
                        <span class="logo-mark">BW</span>
                    @endif
                    <span><b>{{ $siteSettings->store_name }}</b><small>{{ $siteSettings->tagline }}</small></span>
                </div>
                <p>Premium quality, affordable everyday essentials. {{ $shippingSettings->cod_enabled ? 'Cash on Delivery available.' : 'Prepaid payment only.' }}</p>
                <div class="socials">
                    @if ($siteSettings->instagram)<a href="{{ $siteSettings->instagram }}" target="_blank" rel="noopener" aria-label="Instagram"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="3.6"/><circle cx="17" cy="7" r="1" fill="currentColor"/></svg></a>@endif
                    @if ($siteSettings->facebook)<a href="{{ $siteSettings->facebook }}" target="_blank" rel="noopener" aria-label="Facebook"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14 9h3V6h-3c-2.2 0-4 1.8-4 4v2H8v3h2v7h3v-7h2.5l.5-3H13v-2c0-.6.4-1 1-1z"/></svg></a>@endif
                    @if ($siteSettings->tiktok)<a href="{{ $siteSettings->tiktok }}" target="_blank" rel="noopener" aria-label="TikTok"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16 3h-2.5v11.2a2.6 2.6 0 1 1-2.1-2.5V9.1A5.4 5.4 0 1 0 16 14.4V9.8a6.6 6.6 0 0 0 4 1.3V8.6A4.1 4.1 0 0 1 16 3z"/></svg></a>@endif
                    @if ($siteSettings->youtube)<a href="{{ $siteSettings->youtube }}" target="_blank" rel="noopener" aria-label="YouTube">YouTube</a>@endif
                    @if ($siteSettings->whatsapp)<a href="{{ $siteSettings->whatsapp_url }}" target="_blank" rel="noopener" aria-label="WhatsApp"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2z"/></svg></a>@endif
                </div>
            </div>
            <div><h4>Shop</h4>
                <a href="{{ route('category', ['cat' => 't-shirts']) }}">T-Shirts</a>
                <a href="{{ route('category', ['cat' => 'oversized']) }}">Oversized T-Shirts</a>
                <a href="{{ route('category', ['cat' => 'hoodies']) }}">Hoodies</a>
                <a href="{{ route('category', ['cat' => 'sweatshirts']) }}">Sweatshirts</a>
                <a href="{{ route('category', ['cat' => 'trousers']) }}">Trousers</a>
                <a href="{{ route('category', ['cat' => 'kids']) }}">Kids Collection</a>
            </div>
            <div><h4>Help</h4>
                <a href="{{ route('faq') }}">FAQs</a><a href="{{ route('size-guide') }}">Size Guide</a>
                <a href="{{ route('shipping') }}">Shipping Policy</a><a href="{{ route('returns') }}">Returns &amp; Exchange</a>
                <a href="{{ route('contact') }}">Contact Us</a><a href="{{ route('track-order') }}">Track Order</a>
            </div>
            <div><h4>Company</h4>
                <a href="{{ route('about') }}">About BeastWear</a><a href="{{ route('privacy') }}">Privacy Policy</a>
                <a href="{{ route('terms') }}">Terms &amp; Conditions</a><a href="{{ route('login') }}">Login</a>
                <a href="{{ route('register') }}">Create Account</a>
            </div>
            <div class="col-news"><h4>Newsletter</h4>
                <p style="font-size:13.5px;color:#9a9995">Join the BeastWear list for early access to drops and member-only discounts.</p>
                <p class="hint">Newsletter sign-up is not available yet.</p>
                <div style="margin-top:18px;font-size:13.5px">
                    <div style="display:flex;gap:9px;align-items:center;padding:4px 0"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M5 3h3l2 5-2.5 1.5a11 11 0 0 0 5 5L14 12l5 2v3a2 2 0 0 1-2.2 2A16 16 0 0 1 3 5.2 2 2 0 0 1 5 3z"/></svg> {{ $siteSettings->phone }}</div>
                    <div style="display:flex;gap:9px;align-items:center;padding:4px 0"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg> {{ $siteSettings->email }}</div>
                    <div style="display:flex;gap:9px;align-items:center;padding:4px 0"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M12 21s7-6.1 7-11a7 7 0 1 0-14 0c0 4.9 7 11 7 11z"/><circle cx="12" cy="10" r="2.4"/></svg> {{ $siteSettings->address }}</div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <span>© <span id="bw-year">2026</span> {{ $siteSettings->store_name }} — All rights reserved.</span>
            <div class="pay-tags">@if ($shippingSettings->cod_enabled)<span>Cash on Delivery</span>@endif<span>Bank Transfer</span><span>JazzCash</span><span>Easypaisa</span></div>
        </div>
    </div></footer>
</div>
