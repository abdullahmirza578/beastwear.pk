<header class="topbar" id="topbar">
    <button class="icon-btn" id="sidebarToggle" aria-label="Toggle sidebar"><span data-admin-icon="menu"></span></button>
    <div class="topbar-search">
        <span class="icon" data-admin-icon="search"></span>
        <input id="globalSearch" type="search" placeholder="Search products, orders, customers…" autocomplete="off">
        <kbd>/</kbd><div class="search-results" id="searchResults"></div>
    </div>
    <div class="topbar-right">
        <button class="icon-btn" id="themeToggle" title="Toggle theme"><span data-admin-icon="sun"></span></button>
        <div class="dropdown">
            <button class="icon-btn" data-dropdown="notifMenu" aria-label="Notifications">
                <span data-admin-icon="bell"></span><span class="dot" data-notification-dot hidden></span>
            </button>
            <div class="dropdown-menu wide" id="notifMenu">
                <div class="dropdown-header"><strong>Notifications</strong><a href="#" id="markAllRead">Mark all read</a></div>
                <div id="notificationItems"></div>
            </div>
        </div>
        <div class="dropdown">
            <button class="profile-btn" data-dropdown="profileMenu">
                <span class="avatar" data-admin-avatar></span>
                <div class="meta"><strong data-admin-name></strong><small>Administrator</small></div>
                <span class="chev" data-admin-icon="chevron"></span>
            </button>
            <div class="dropdown-menu" id="profileMenu">
                <div class="dropdown-header"><div><strong data-admin-name></strong><div class="small text-muted" data-admin-email></div></div></div>
                <a href="{{ route('admin.profile') }}"><span data-admin-icon="user"></span> My Profile</a>
                <a href="{{ route('admin.settings') }}"><span data-admin-icon="settings"></span> Settings</a>
                <div class="divider"></div>
                <a href="{{ route('admin.logout') }}" class="danger" data-logout><span data-admin-icon="logout"></span> Logout</a>
            </div>
        </div>
    </div>
</header>
