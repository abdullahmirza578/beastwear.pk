<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'BeastWear.pk Admin')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('admin-assets/css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/css/admin-components.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/css/admin-responsive.css') }}">
    @yield('styles')
</head>
<body data-page="@yield('page')">
    <div class="admin-shell">
        @include('admin.partials.sidebar')
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <div class="main">
            @include('admin.partials.navbar')
            <main class="content">
                @yield('content')
            </main>
            @include('admin.partials.footer')
        </div>
    </div>

    <form id="adminLogoutForm" method="POST" action="{{ route('admin.logout') }}" hidden>
        @csrf
    </form>
    @php
        $adminUser = auth()->user();
        $adminUserData = [
            'id' => $adminUser->id,
            'name' => $adminUser->name,
            'email' => $adminUser->email,
            'role' => 'Administrator',
            'avatar' => '',
        ];
    @endphp
    <script>
        window.BW_ADMIN_USER = {{ \Illuminate\Support\Js::from($adminUserData) }};
    </script>
    <script src="{{ asset('admin-assets/js/admin.js') }}"></script>
    @yield('scripts')
</body>
</html>
