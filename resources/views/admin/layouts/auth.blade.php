<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Login | BeastWear.pk')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('admin-assets/css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/css/admin-components.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/css/admin-responsive.css') }}">
    @yield('styles')
</head>
<body data-page="@yield('page')">
    @yield('content')
    @yield('scripts')
</body>
</html>
