<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Hospital HMS Dashboard')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-2: #06b6d4;
            --primary-dark: #1d4ed8;
            --bg: #f6f8fc;
            --card-bg: #ffffff;
            --border-color: rgba(148, 163, 184, 0.28);
            --text-main: #0f172a;
            --text-muted: #64748b;
            --shadow-sm: 0 10px 30px rgba(2, 6, 23, 0.06);
            --shadow-md: 0 18px 55px rgba(2, 6, 23, 0.10);
            --ring: 0 0 0 4px rgba(37, 99, 235, 0.18);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background:
                radial-gradient(1200px 600px at 10% -10%, rgba(37, 99, 235, 0.14), transparent 60%),
                radial-gradient(900px 500px at 90% 0%, rgba(6, 182, 212, 0.12), transparent 55%),
                var(--bg);
            color: var(--text-main);
        }

        .shell {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            height: 62px;
            padding: 0 22px 0 12px;
            background: rgba(255, 255, 255, 0.78);
            border-bottom: 1px solid rgba(148, 163, 184, 0.25);
            backdrop-filter: blur(10px);
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            gap: 18px;
            position: sticky;
            top: 0;
            z-index: 40;
        }

        .search-area {
            display: flex;
            justify-content: left;
        }

        /* Search Box Wrapper */
        .search-box {
            position: relative;
            width: 100%;
            max-width: 460px;
        }

        /* Input */
        .search-input {
            width: 100%;
            padding: 8px 14px 8px 35px;
            /* left space for icon */
            border-radius: 999px;
            border: 1px solid var(--border-color);
            font-size: 13px;
            outline: none;
        }

        /* Focus */
        .search-input:focus {
            border-color: var(--primary);
        }

        /* Icon */
        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 13px;
            color: var(--text-muted);
        }

        .search-results {
            position: absolute;
            top: calc(100% + 10px);
            left: 0;
            right: 0;
            z-index: 60;
            display: none;
            overflow: hidden;
            border: 1px solid var(--border-color);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.16);
            backdrop-filter: blur(14px);
        }

        .search-results.visible {
            display: block;
        }

        .search-result-item,
        .search-result-state {
            display: grid;
            grid-template-columns: 34px 1fr;
            gap: 10px;
            padding: 12px 14px;
            color: var(--text-dark);
            text-decoration: none;
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        }

        .search-result-item:last-child,
        .search-result-state:last-child {
            border-bottom: 0;
        }

        .search-result-item:hover {
            background: rgba(37, 99, 235, 0.07);
        }

        .search-result-icon {
            width: 34px;
            height: 34px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            color: var(--primary);
            background: rgba(37, 99, 235, 0.1);
        }

        .search-result-title {
            display: block;
            font-size: 13px;
            font-weight: 800;
        }

        .search-result-subtitle,
        .search-result-state {
            font-size: 12px;
            color: var(--text-muted);
        }

        .notification-icon {
            position: relative;
            font-size: 18px;
            cursor: pointer;
            color: var(--text-muted);
            border: 0;
            background: transparent;
            padding: 8px;
        }

        .notification-icon:hover {
            color: var(--primary);
        }

        /* Badge */
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -6px;
            background: #ef4444;
            color: #fff;
            font-size: 10px;
            min-width: 18px;
            height: 18px;
            padding: 2px 5px;
            border-radius: 999px;
            display: none;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            line-height: 1;
        }

        .notification-badge.is-visible {
            display: inline-flex;
        }

        .notification-dropdown {
            position: absolute;
            right: 48px;
            top: 50px;
            width: 360px;
            max-width: calc(100vw - 20px);
            background: var(--card-bg);
            border-radius: 18px;
            border: 1px solid rgba(229, 231, 235, 0.95);
            box-shadow: 0 22px 55px rgba(15, 23, 42, 0.16);
            overflow: hidden;
            display: none;
            z-index: 60;
        }

        .notification-dropdown.visible {
            display: block;
        }

        .notification-dropdown__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 13px 16px 11px;
            border-bottom: 1px solid rgba(229, 231, 235, 0.9);
        }

        .notification-dropdown__title {
            font-size: 14px;
            font-weight: 700;
        }

        .notification-dropdown__action {
            border: 0;
            background: transparent;
            color: var(--primary);
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
        }

        .notification-list {
            max-height: 380px;
            overflow: auto;
            display: grid;
        }

        .notification-item {
            display: grid;
            grid-template-columns: 34px 1fr;
            gap: 12px;
            padding: 13px 16px;
            text-decoration: none;
            color: var(--text-main);
            border-bottom: 1px solid rgba(229, 231, 235, 0.72);
            background: #ffffff;
        }

        .notification-item:hover {
            background: #f8fafc;
        }

        .notification-item.is-unread {
            background: rgba(37, 99, 235, 0.06);
        }

        .notification-item__icon {
            width: 34px;
            height: 34px;
            border-radius: 13px;
            display: grid;
            place-items: center;
            color: #2563eb;
            background: rgba(37, 99, 235, 0.1);
            font-size: 13px;
        }

        .notification-item__title {
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .notification-item__message {
            font-size: 12px;
            color: var(--text-muted);
            line-height: 1.5;
        }

        .notification-empty {
            padding: 20px 16px;
            color: var(--text-muted);
            font-size: 13px;
            text-align: center;
        }

        /* Right Area Alignment */
        .profile-area {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-logo {
            width: 28px;
            height: 28px;
            border-radius: 10px;
            background: radial-gradient(circle at 30% 30%, #ffffff, #1e88e5);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 15px;
            box-shadow: 0 8px 18px rgba(30, 136, 229, 0.5);
        }

        .brand-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .brand-title {
            font-size: 14px;
            font-weight: 600;
        }

        .brand-sub {
            font-size: 11px;
            color: var(--text-muted);
        }

        .profile-area {
            position: relative;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .profile-name {
            font-size: 13px;
            color: var(--text-muted);
        }

        .profile-avatar-btn {
            border: none;
            background: transparent;
            padding: 0;
            cursor: pointer;
        }

        /* EMAIL INPUT (RIGHT SIDE) */
        .email-box {
            position: relative;
            width: 180px;
        }

        .email-input {
            width: 100%;
            padding: 6px 10px 6px 30px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            font-size: 12px;
            background: #f9fafb;
        }

        .email-input:focus {
            outline: none;
            border-color: var(--primary);
            background: #fff;
        }

        /* Icon inside input */
        .email-icon {
            position: absolute;
            top: 50%;
            left: 8px;
            transform: translateY(-50%);
            font-size: 18px;
            color: #0257ff;
        }

        .profile-avatar {
            width: 34px;
            height: 34px;
            border-radius: 999px;
            background: linear-gradient(135deg, #60a5fa, #2563eb);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-weight: 600;
            font-size: 16px;
        }

        .profile-dropdown {
            position: absolute;
            right: 0;
            top: 46px;
            width: 200px;
            background: var(--card-bg);
            border-radius: 12px;
            border: 1px solid rgba(229, 231, 235, 0.9);
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.16);
            padding: 10px 12px 12px;
            display: none;
        }

        .profile-dropdown.visible {
            display: block;
        }

        .dropdown-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }

        .dropdown-avatar {
            width: 28px;
            height: 28px;
            border-radius: 999px;
            background: linear-gradient(135deg, #34d399, #059669);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
        }

        .dropdown-name {
            font-size: 13px;
            font-weight: 500;
        }

        .dropdown-role {
            font-size: 11px;
            color: var(--text-muted);
        }

        .dropdown-divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 8px 0;
        }

        .dropdown-item {
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 8px;
        }

        .btn-logout {
            width: 100%;
            border: none;
            border-radius: 999px;
            padding: 8px 0;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        /* App frame (Sidebar + Page) */
        .app-frame {
            flex: 1;
            display: flex;
            min-height: calc(100vh - 60px);
        }

        /* Sidebar */
        .app-sidebar {
            width: 260px;
            min-width: 260px;
            background: linear-gradient(180deg, #1f2f57, #152645);
            color: rgba(255, 255, 255, 0.92);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 60px;
            height: calc(100vh - 60px);
            overflow: auto;
        }

        .app-sidebar__header {
            padding: 18px 16px 10px;
        }

        .app-sidebar__brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .app-sidebar__brand-icon {
            width: 34px;
            height: 34px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.14);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px rgba(2, 6, 23, 0.25);
        }

        .app-sidebar__brand-text {
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.2px;
        }

        .app-sidebar__nav {
            padding: 8px 10px 12px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .app-sidebar__item {
            text-decoration: none;
            color: rgba(255, 255, 255, 0.85);
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 11px 12px;
            border-radius: 13px;
            font-size: 13px;
            line-height: 1;
            transition: background 160ms ease, color 160ms ease;
            user-select: none;
        }

        .app-sidebar__item[aria-disabled="true"] {
            cursor: default;
            opacity: 0.92;
        }

        .app-sidebar__item:hover {
            background: rgba(255, 255, 255, 0.09);
            color: #ffffff;
        }

        .app-sidebar__item.is-active {
            background: rgba(37, 99, 235, 0.35);
            border: 1px solid rgba(96, 165, 250, 0.35);
            box-shadow: 0 16px 40px rgba(2, 6, 23, 0.28);
            color: #ffffff;
        }

        .app-sidebar__icon {
            width: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.92);
        }

        .app-sidebar__label {
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .app-sidebar__caret {
            font-size: 11px;
            opacity: 0.7;
        }

        .app-sidebar__footer {
            margin-top: auto;
            padding: 12px 12px 16px;
        }

        .app-sidebar__user {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 10px;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .app-sidebar__user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 14px;
            background: linear-gradient(135deg, #60a5fa, #2563eb);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-weight: 700;
        }

        .app-sidebar__user-name {
            font-size: 13px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.92);
            max-width: 160px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .app-sidebar__user-role {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.7);
            margin-top: 2px;
            max-width: 160px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Page content area */
        .app-page {
            flex: 1;
            padding: 22px 22px 30px;
            min-width: 0;
        }

        .card {
            background: var(--card-bg);
            border-radius: 16px;
            border: 1px solid rgba(229, 231, 235, 0.9);
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.08);
            padding: 24px 22px;
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .card-subtitle {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 18px;
        }

        /* Tables (used across modules) */
        .dash-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .dash-table th,
        .dash-table td {
            padding: 12px 10px;
            border-bottom: 1px solid rgba(229, 231, 235, 0.95);
            text-align: left;
            vertical-align: top;
        }

        .dash-table th {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 600;
        }

        .workflow-chip-row {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            max-width: 420px;
        }

        .workflow-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 8px;
            border-radius: 999px;
            border: 1px solid var(--workflow-chip-border, rgba(148, 163, 184, 0.24));
            background: var(--workflow-chip-bg, rgba(248, 250, 252, 0.95));
            color: var(--workflow-chip-color, #64748b);
            font-size: 11px;
            line-height: 1.2;
            white-space: nowrap;
        }

        .workflow-chip__dot {
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: var(--workflow-chip-dot, #cbd5e1);
            flex: 0 0 auto;
        }

        .workflow-chip__meta {
            font-size: 12px;
            color: var(--text-muted);
        }

        .u-nowrap {
            white-space: nowrap;
        }

        .table-col-id {
            width: 72px;
        }

        .table-col-date {
            width: 118px;
        }

        .table-col-time {
            width: 84px;
        }

        .table-col-name {
            min-width: 180px;
        }

        .table-col-status {
            width: 120px;
        }

        .table-col-money {
            width: 110px;
        }

        .table-col-workflow {
            min-width: 260px;
        }

        .table-col-actions {
            width: 170px;
        }

        @media (max-width: 640px) {
            .topbar {
                padding: 0 14px;
            }

            .app-page {
                padding: 16px 14px 24px;
            }
        }

        @media (max-width: 980px) {
            .app-sidebar {
                display: none;
            }
        }
    </style>
    @php($viteManifest = public_path('build/manifest.json'))
    @if (file_exists($viteManifest))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/hms.css') }}">
    @endif
</head>

<body>
    <div class="shell">
        <header class="topbar">
            <!-- LEFT: Logo + Name -->
            <div class="brand">
                <div class="auth-logo" style="padding: 2px 0;">
                    <img src="{{ asset('images/HMS logo.png') }}" alt="Hospital HMS Logo"
                        style="width: 70px; height: 55px;">
                </div>
                <div class="brand-text">
                    <div class="brand-title">Hospital HMS</div>
                </div>
            </div>

            <!-- CENTER: Search -->
            <div class="search-area">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" placeholder="Search patients, appointments, billing..." class="search-input" id="globalSearchInput" autocomplete="off">
                    <div class="search-results" id="globalSearchResults"></div>
                </div>
            </div>

            <!-- RIGHT: Notification + Profile -->
            <div class="profile-area">
                <!-- Email Input -->
                <div class="email-box">
                    <i class="fa-solid fa-envelope email-icon"></i>
                    <input class="email-input">
                </div>

                <!-- Notification Icon -->
                <button class="notification-icon" id="notificationButton" type="button" aria-label="Notifications">
                    <i class="fa-solid fa-bell"></i>
                    <span class="notification-badge" id="notificationBadge">0</span>
                </button>

                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-dropdown__header">
                        <div class="notification-dropdown__title">Notifications</div>
                        <button class="notification-dropdown__action" id="notificationReadAllButton" type="button">
                            Mark all read
                        </button>
                    </div>
                    <div class="notification-list" id="notificationList">
                        <div class="notification-empty">Loading notifications...</div>
                    </div>
                </div>

                @php($initial = auth()->check() ? mb_substr(auth()->user()->name, 0, 1) : 'A')

                <!--  Profile -->
                <button class="profile-avatar-btn" id="profileAvatarButton" type="button">
                    <div class="profile-avatar">
                        {{ mb_strtoupper($initial) }}
                    </div>
                </button>

                <!-- Dropdown -->
                <div class="profile-dropdown" id="profileDropdown">
                    @auth
                    <div class="dropdown-header">
                        <div class="dropdown-avatar">
                            {{ mb_strtoupper($initial) }}
                        </div>
                        <div>
                            <div class="dropdown-name">{{ auth()->user()->name }}</div>
                            <div class="dropdown-role">
                                {{ optional(auth()->user()->department)->name ?? 'Staff Member' }}
                            </div>
                        </div>
                    </div>
                    @endauth

                    <div class="dropdown-divider"></div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-logout">Logout</button>
                    </form>
                </div>
            </div>
        </header>

        <div class="app-frame">
            @include('sidebar.sidebar')
            <main class="app-page">
                @include('partials.flash')
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        (function() {
            const avatarButton = document.getElementById('profileAvatarButton');
            const dropdown = document.getElementById('profileDropdown');
            const notificationButton = document.getElementById('notificationButton');
            const notificationDropdown = document.getElementById('notificationDropdown');
            const notificationBadge = document.getElementById('notificationBadge');
            const notificationList = document.getElementById('notificationList');
            const notificationReadAllButton = document.getElementById('notificationReadAllButton');
            const globalSearchInput = document.getElementById('globalSearchInput');
            const globalSearchResults = document.getElementById('globalSearchResults');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const globalSearchUrl = @json(auth()->check() ? route('search') : null);
            const notificationIndexUrl = @json(auth()->check() ? route('notifications.index') : null);
            const notificationReadAllUrl = @json(auth()->check() ? route('notifications.read-all') : null);
            const notificationReadUrlTemplate = @json(auth()->check() ? route('notifications.read', ['notification' => '__NOTIFICATION_ID__']) : null);
            const currentUserId = @json(auth()->id());
            let globalSearchTimer = null;
            let globalSearchController = null;

            function escapeHtml(value) {
                return String(value ?? '').replace(/[&<>"']/g, function(match) {
                    return ({
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#039;'
                    })[match];
                });
            }

            function setBadge(count) {
                if (!notificationBadge) return;

                const value = Number(count || 0);
                notificationBadge.textContent = value > 99 ? '99+' : String(value);
                notificationBadge.classList.toggle('is-visible', value > 0);
            }

            function setSearchState(message) {
                if (!globalSearchResults) return;

                globalSearchResults.innerHTML = `
                    <div class="search-result-state">
                        <span class="search-result-icon"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <span>${escapeHtml(message)}</span>
                    </div>
                `;
                globalSearchResults.classList.add('visible');
            }

            function renderSearchResults(results) {
                if (!globalSearchResults) return;

                if (!results || results.length === 0) {
                    setSearchState('No results found.');
                    return;
                }

                globalSearchResults.innerHTML = results.map(function(result) {
                    const title = escapeHtml(result.title || 'Search result');
                    const subtitle = escapeHtml(result.subtitle || '');
                    const icon = escapeHtml(result.icon || 'fa-solid fa-circle-info');
                    const url = result.url ? escapeHtml(result.url) : '#';

                    return `
                        <a class="search-result-item" href="${url}">
                            <span class="search-result-icon"><i class="${icon}"></i></span>
                            <span>
                                <span class="search-result-title">${title}</span>
                                <span class="search-result-subtitle">${subtitle}</span>
                            </span>
                        </a>
                    `;
                }).join('');
                globalSearchResults.classList.add('visible');
            }

            async function runGlobalSearch(query) {
                if (!globalSearchUrl || !query) return;

                if (globalSearchController) {
                    globalSearchController.abort();
                }

                globalSearchController = new AbortController();
                setSearchState('Searching...');

                try {
                    const response = await fetch(`${globalSearchUrl}?q=${encodeURIComponent(query)}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin',
                        signal: globalSearchController.signal
                    });

                    if (!response.ok) {
                        setSearchState('Unable to search right now.');
                        return;
                    }

                    const data = await response.json();
                    renderSearchResults(data.results || []);
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        setSearchState('Unable to search right now.');
                    }
                }
            }

            function renderNotifications(notifications) {
                if (!notificationList) return;

                if (!notifications || notifications.length === 0) {
                    notificationList.innerHTML = '<div class="notification-empty">No notifications yet.</div>';
                    return;
                }

                notificationList.innerHTML = notifications.map(function(notification) {
                    const title = escapeHtml(notification.title || 'Hospital notification');
                    const message = escapeHtml(notification.message || '');
                    const icon = escapeHtml(notification.icon || 'fa-solid fa-bell');
                    const url = notification.url ? escapeHtml(notification.url) : '#';
                    const unread = notification.read_at ? '' : ' is-unread';
                    const notificationId = escapeHtml(notification.id || '');

                    return `
                        <a class="notification-item${unread}" href="${url}" data-notification-id="${notificationId}">
                            <span class="notification-item__icon"><i class="${icon}"></i></span>
                            <span>
                                <span class="notification-item__title">${title}</span>
                                <span class="notification-item__message">${message}</span>
                            </span>
                        </a>
                    `;
                }).join('');
            }

            async function loadNotifications() {
                if (!notificationIndexUrl) return;

                try {
                    const response = await fetch(notificationIndexUrl, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    });

                    if (!response.ok) return;

                    const data = await response.json();
                    setBadge(data.unread_count || 0);
                    renderNotifications(data.notifications || []);
                } catch (error) {
                    if (notificationList) {
                        notificationList.innerHTML = '<div class="notification-empty">Unable to load notifications.</div>';
                    }
                }
            }

            async function markNotificationRead(notificationId) {
                if (!notificationReadUrlTemplate || !csrfToken || !notificationId) return null;

                const response = await fetch(notificationReadUrlTemplate.replace('__NOTIFICATION_ID__', encodeURIComponent(notificationId)), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) return null;

                return response.json();
            }

            async function markAllRead() {
                if (!notificationReadAllUrl || !csrfToken) return;

                const response = await fetch(notificationReadAllUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                if (response.ok) {
                    setBadge(0);
                    loadNotifications();
                }
            }

            if (avatarButton && dropdown) {
                avatarButton.addEventListener('click', function(event) {
                    event.stopPropagation();
                    dropdown.classList.toggle('visible');
                    notificationDropdown?.classList.remove('visible');
                });
            }

            if (notificationButton && notificationDropdown) {
                notificationButton.addEventListener('click', function(event) {
                    event.stopPropagation();
                    notificationDropdown.classList.toggle('visible');
                    dropdown?.classList.remove('visible');
                    loadNotifications();
                });
            }

            notificationReadAllButton?.addEventListener('click', function(event) {
                event.stopPropagation();
                markAllRead();
            });

            globalSearchInput?.addEventListener('input', function() {
                const query = globalSearchInput.value.trim();

                clearTimeout(globalSearchTimer);

                if (!query) {
                    globalSearchResults?.classList.remove('visible');
                    if (globalSearchResults) {
                        globalSearchResults.innerHTML = '';
                    }
                    return;
                }

                setSearchState('Searching...');
                globalSearchTimer = setTimeout(function() {
                    runGlobalSearch(query);
                }, 300);
            });

            globalSearchInput?.addEventListener('focus', function() {
                if (globalSearchInput.value.trim() && globalSearchResults?.innerHTML) {
                    globalSearchResults.classList.add('visible');
                }
            });

            notificationList?.addEventListener('click', async function(event) {
                const notificationItem = event.target.closest('.notification-item');

                if (!notificationItem || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                    return;
                }

                const notificationId = notificationItem.dataset.notificationId;
                const href = notificationItem.getAttribute('href') || '#';

                event.preventDefault();
                notificationItem.classList.remove('is-unread');

                try {
                    const data = await markNotificationRead(notificationId);

                    if (data && typeof data.unread_count !== 'undefined') {
                        setBadge(data.unread_count);
                    }
                } finally {
                    if (href && href !== '#') {
                        window.location.href = href;
                    }
                }
            });

            document.addEventListener('click', function(event) {
                if (dropdown && avatarButton && !dropdown.contains(event.target) && !avatarButton.contains(event.target)) {
                    dropdown.classList.remove('visible');
                }

                if (notificationDropdown && notificationButton && !notificationDropdown.contains(event.target) && !notificationButton.contains(event.target)) {
                    notificationDropdown.classList.remove('visible');
                }

                if (globalSearchResults && globalSearchInput && !globalSearchResults.contains(event.target) && !globalSearchInput.contains(event.target)) {
                    globalSearchResults.classList.remove('visible');
                }
            });

            loadNotifications();

            document.addEventListener('DOMContentLoaded', function() {
                if (!window.Echo || !currentUserId) return;

                window.Echo.private(`App.Models.User.${currentUserId}`)
                    .notification(function() {
                        loadNotifications();
                    });
            });
        })();
    </script>
</body>

</html>
