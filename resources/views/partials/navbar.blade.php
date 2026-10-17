<div id="site-header">
    <div class="announce">
        <div class="wrap">
            <span>{{ $shippingSettings->cod_enabled ? 'Cash on Delivery' : 'Prepaid orders only' }}</span><i class="dot"></i>
            <span>Free delivery over Rs {{ number_format($shippingSettings->free_threshold) }}</span><i class="dot"></i>
            <span>{{ $shippingSettings->nationwide ? 'Pakistan-wide shipping' : 'Delivery currently unavailable' }}</span>
        </div>
    </div>
    <header class="site-header">
        <div class="wrap header-inner">
            <button class="icon-btn hamburger" id="nav-open" aria-label="Open menu">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
            <a class="logo" href="{{ route('home') }}">
                @if ($siteSettings?->logo_url)
                    <img src="{{ $siteSettings->logo_url }}" alt="{{ $siteSettings->store_name }}" style="max-width:64px;max-height:42px;object-fit:contain">
                @else
                    <span class="logo-mark">BW</span>
                @endif
                <span class="logo-text"><b>{{ $siteSettings->store_name }}</b><small>{{ $siteSettings->tagline }}</small></span>
            </a>
            <nav class="main-nav" aria-label="Main navigation">
                <a href="{{ route('home') }}" data-nav="home">Home</a>
                <a href="{{ route('shop') }}" data-nav="shop">Shop</a>
                <div class="nav-drop">
                    <a href="{{ route('category') }}" data-nav="category">Categories</a>
                    <div class="nav-menu"><div class="nav-menu-inner">
                        <a href="{{ route('category', ['cat' => 't-shirts']) }}">T-Shirts</a>
                        <a href="{{ route('category', ['cat' => 'oversized']) }}">Oversized T-Shirts</a>
                        <a href="{{ route('category', ['cat' => 'hoodies']) }}">Hoodies</a>
                        <a href="{{ route('category', ['cat' => 'sweatshirts']) }}">Sweatshirts</a>
                        <a href="{{ route('category', ['cat' => 'trousers']) }}">Trousers</a>
                        <a href="{{ route('category', ['cat' => 'kids']) }}">Kids Collection</a>
                        <a href="{{ route('category') }}" class="all">View all categories</a>
                    </div></div>
                </div>
                <a href="{{ route('shop', ['sort' => 'new']) }}" data-nav="new">New Arrivals</a>
                <a href="{{ route('shop', ['sale' => 1]) }}" data-nav="sale">Sale</a>
            </nav>
            <div class="header-actions">
                <button class="icon-btn theme-toggle" id="customer-theme-toggle" type="button" aria-label="Switch to dark mode" title="Switch to dark mode">
                    <svg class="theme-icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/></svg>
                    <svg class="theme-icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20.5 14.2A8.5 8.5 0 0 1 9.8 3.5 8.5 8.5 0 1 0 20.5 14.2Z"/></svg>
                </button>
                <button class="icon-btn" id="search-open" aria-label="Search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                </button>
                <a class="icon-btn" href="{{ route('wishlist') }}" aria-label="Wishlist">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 20s-7-4.4-7-9.3A4.1 4.1 0 0 1 12 8a4.1 4.1 0 0 1 7 2.7C19 15.6 12 20 12 20z"/></svg>
                    <span class="badge-count" data-wish-count style="display:none">0</span>
                </a>
                <a class="icon-btn" href="{{ route('account') }}" aria-label="Account">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="3.6"/><path d="M4.5 20c1.4-3.6 4-5.2 7.5-5.2s6.1 1.6 7.5 5.2"/></svg>
                </a>
                <a class="icon-btn" href="{{ route('cart') }}" aria-label="Cart">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 6h2.2l2.3 10h9.2l2.1-7.2H7.2"/><circle cx="10" cy="19.4" r="1.4"/><circle cx="17" cy="19.4" r="1.4"/></svg>
                    <span class="badge-count" data-cart-count style="display:none">0</span>
                </a>
            </div>
        </div>
    </header>

    <div class="scrim" id="scrim"></div>
    <aside class="drawer" id="nav-drawer" aria-label="Mobile menu">
        <div class="drawer-head"><b>Menu</b><button class="modal-close" data-close-drawer aria-label="Close menu">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
        </button></div>
        <div class="drawer-body"><nav class="drawer-nav">
            <a href="{{ route('home') }}">Home</a><a href="{{ route('shop') }}">Shop All</a>
            <button type="button" id="m-cats">Categories <span>+</span></button>
            <div id="m-cats-list" class="hide">
                <a class="sub" href="{{ route('category', ['cat' => 't-shirts']) }}">T-Shirts</a>
                <a class="sub" href="{{ route('category', ['cat' => 'oversized']) }}">Oversized T-Shirts</a>
                <a class="sub" href="{{ route('category', ['cat' => 'hoodies']) }}">Hoodies</a>
                <a class="sub" href="{{ route('category', ['cat' => 'sweatshirts']) }}">Sweatshirts</a>
                <a class="sub" href="{{ route('category', ['cat' => 'trousers']) }}">Trousers</a>
                <a class="sub" href="{{ route('category', ['cat' => 'kids']) }}">Kids Collection</a>
            </div>
            <a href="{{ route('shop', ['sort' => 'new']) }}">New Arrivals</a><a href="{{ route('shop', ['sale' => 1]) }}">Sale</a>
            <a href="{{ route('wishlist') }}">Wishlist</a><a href="{{ route('account') }}">My Account</a>
            <a href="{{ route('orders') }}">My Orders</a><a href="{{ route('about') }}">About Us</a>
            <a href="{{ route('contact') }}">Contact</a><a href="{{ route('size-guide') }}">Size Guide</a>
        </nav></div>
        <div class="drawer-foot">
            <a href="https://wa.me/923458848846" target="_blank" rel="noopener">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2z"/></svg>
                WhatsApp Order
            </a>
            <a href="{{ route('login') }}">Login / Register</a>
        </div>
    </aside>

    <div class="search-overlay" id="search-overlay">
        <div class="wrap search-head"><div class="search-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            <input type="text" id="search-input" placeholder="Search t-shirts, hoodies, trousers..." autocomplete="off">
        </div><button class="modal-close" id="search-close" aria-label="Close search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
        </button></div>
        <div class="search-wrap">
            <div id="search-suggest" class="suggest"></div>
            <div class="mt-m"><span class="eyebrow">Popular searches</span><div class="search-tags">
                <a class="chip" href="{{ route('search', ['q' => 'oversized']) }}">Oversized Tee</a>
                <a class="chip" href="{{ route('search', ['q' => 'hoodie']) }}">Hoodie</a>
                <a class="chip" href="{{ route('search', ['q' => 'cargo']) }}">Cargo</a>
                <a class="chip" href="{{ route('search', ['q' => 'black']) }}">Black Tee</a>
                <a class="chip" href="{{ route('search', ['q' => 'kids']) }}">Kids</a>
                <a class="chip" href="{{ route('shop', ['sale' => 1]) }}">Sale</a>
            </div></div>
        </div>
    </div>
    <div class="modal" id="quick-modal"><div class="modal-card">
        <div class="modal-head"><b>Quick View</b><button class="modal-close" data-close-modal aria-label="Close">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
        </button></div>
        <div class="modal-body" id="quick-body"></div>
    </div></div>
    <a class="wa-float" href="https://wa.me/923458848846" target="_blank" rel="noopener" aria-label="Chat on WhatsApp">
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2z"/></svg>
    </a>
    <div class="toast-wrap" id="bw-toasts"></div>
</div>
