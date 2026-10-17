<aside class="sidebar" id="sidebar">
    <a class="sidebar-brand" href="{{ route('admin.dashboard') }}">
        <div class="brand-mark">BW</div>
        <div class="brand-text"><strong>BeastWear.pk</strong><span>Admin Panel</span></div>
    </a>
    <nav class="sidebar-nav">
        <div class="nav-section"><div class="nav-section-title">Main</div>
            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}" title="Dashboard"><span data-admin-icon="dashboard"></span><span>Dashboard</span></a>
        </div>
        <div class="nav-section"><div class="nav-section-title">Catalog</div>
            <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}" title="Products"><span data-admin-icon="box"></span><span>Products</span></a>
            <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}" title="Categories"><span data-admin-icon="tag"></span><span>Categories</span></a>
            <a class="nav-link {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}" href="{{ route('admin.inventory.index') }}" title="Inventory"><span data-admin-icon="layers"></span><span>Inventory</span><em class="nav-count" data-admin-count="lowStock"></em></a>
            <a class="nav-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}" href="{{ route('admin.reviews.index') }}" title="Reviews"><span data-admin-icon="star"></span><span>Reviews</span><em class="nav-count" data-admin-count="pendingReviews"></em></a>
        </div>
        <div class="nav-section"><div class="nav-section-title">Sales</div>
            <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}" title="Orders"><span data-admin-icon="cart"></span><span>Orders</span><em class="nav-count" data-admin-count="pendingOrders"></em></a>
            <a class="nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}" href="{{ route('admin.payments.index') }}" title="Payment Review"><span data-admin-icon="credit-card"></span><span>Payment Review</span></a>
            <a class="nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}" href="{{ route('admin.customers.index') }}" title="Customers"><span data-admin-icon="users"></span><span>Customers</span></a>
            <a class="nav-link {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}" href="{{ route('admin.coupons.index') }}" title="Coupons"><span data-admin-icon="ticket"></span><span>Coupons</span></a>
        </div>
        <div class="nav-section"><div class="nav-section-title">Website</div>
            <a class="nav-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}" href="{{ route('admin.banners.index') }}" title="Homepage & Banners"><span data-admin-icon="image"></span><span>Homepage &amp; Banners</span></a>
        </div>
        <div class="nav-section"><div class="nav-section-title">Reports</div>
            <a class="nav-link {{ request()->routeIs('admin.reports') ? 'active' : '' }}" href="{{ route('admin.reports') }}" title="Sales Reports"><span data-admin-icon="chart"></span><span>Sales Reports</span></a>
            <a class="nav-link" href="{{ route('admin.reports') }}#analytics" title="Analytics"><span data-admin-icon="pie"></span><span>Analytics</span></a>
        </div>
        <div class="nav-section"><div class="nav-section-title">Administration</div>
            <a class="nav-link {{ request()->routeIs('admin.admins.*') ? 'active' : '' }}" href="{{ route('admin.admins.index') }}" title="Admin Users"><span data-admin-icon="shield"></span><span>Admin Users</span></a>
            <a class="nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}" href="{{ route('admin.settings') }}" title="Settings"><span data-admin-icon="settings"></span><span>Settings</span></a>
            <a class="nav-link {{ request()->routeIs('admin.shipping-settings') ? 'active' : '' }}" href="{{ route('admin.shipping-settings') }}" title="Shipping Settings"><span data-admin-icon="truck"></span><span>Shipping Settings</span></a>
        </div>
        <div class="nav-section"><div class="nav-section-title">Account</div>
            <a class="nav-link {{ request()->routeIs('admin.profile') ? 'active' : '' }}" href="{{ route('admin.profile') }}" title="My Profile"><span data-admin-icon="user"></span><span>My Profile</span></a>
            <a class="nav-link" href="{{ route('admin.logout') }}" data-logout title="Logout"><span data-admin-icon="logout"></span><span>Logout</span></a>
        </div>
    </nav>
    <div class="sidebar-footer">
        <a class="sidebar-user" href="{{ route('admin.profile') }}">
            <span class="avatar sm" data-admin-avatar></span>
            <div class="meta"><strong data-admin-name></strong><span>Administrator</span></div>
        </a>
    </div>
</aside>
