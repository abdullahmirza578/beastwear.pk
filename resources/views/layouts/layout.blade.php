<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $siteSettings->store_name ?? 'BeastWear.pk')</title>
    @yield('meta')
    <script>
        try {
            document.documentElement.dataset.theme = localStorage.getItem('bw_customer_theme') || 'light';
        } catch (error) {
            document.documentElement.dataset.theme = 'light';
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @if (! empty($siteSettings?->favicon_url))
        <link rel="icon" href="{{ $siteSettings->favicon_url }}">
    @else
        <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='7' fill='%230b0b0c'/><text x='16' y='22' font-family='Arial Narrow' font-size='15' fill='white' text-anchor='middle'>BW</text></svg>">
    @endif
    @vite(['resources/css/style.css', 'resources/css/components.css', 'resources/css/responsive.css', 'resources/css/theme.css'])
    @yield('styles')
</head>
<body>
    @include('partials.navbar')

    <main class="@yield('main-class')">
        @yield('content')
    </main>

    @include('partials.footer')

    <script>
        window.BW_CATALOG = {{ \Illuminate\Support\Js::from($storefrontCatalog ?? ['categories' => [], 'products' => []]) }};
        window.BW_CATALOG_REVIEWS = {{ \Illuminate\Support\Js::from($storefrontReviews ?? []) }};
        window.BW_PRODUCT_SLUG = {{ \Illuminate\Support\Js::from($storefrontProductSlug ?? null) }};
        window.BW_SHOP_URL = {{ \Illuminate\Support\Js::from(route('shop')) }};
        window.BW_IS_AUTHENTICATED = {{ auth()->check() ? 'true' : 'false' }};
        window.BW_SERVER_CART_COUNT = {{ \Illuminate\Support\Js::from($serverCartCount ?? null) }};
        window.BW_SERVER_WISHLIST = {{ \Illuminate\Support\Js::from($serverWishlistIds ?? []) }};
    </script>
    <script src="{{ asset('js/products.js') }}"></script>
    <script src="{{ asset('js/cart.js') }}"></script>
    <script src="{{ asset('js/wishlist.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>
    @yield('scripts')
</body>
</html>
