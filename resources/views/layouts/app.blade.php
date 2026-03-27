<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Hospital HMS Dashboard')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1e88e5;
            --primary-dark: #1565c0;
            --bg: #f3f4f8;
            --card-bg: #ffffff;
            --border-color: #e5e7eb;
            --text-main: #111827;
            --text-muted: #6b7280;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
        }

        .shell {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            height: 60px;
            padding: 0 24px 0px 10px;
            background: var(--card-bg);
            border-bottom: 1px solid var(--border-color);
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            gap: 20px;
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
            max-width: 400px;
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

        .notification-icon {
            position: relative;
            font-size: 18px;
            cursor: pointer;
            color: var(--text-muted);
        }

        .notification-icon:hover {
            color: var(--primary);
        }

        /* Badge */
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -6px;
            background: red;
            color: #fff;
            font-size: 10px;
            padding: 2px 5px;
            border-radius: 999px;
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
            gap: 20px;
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
            padding: 18px 16px 12px;
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
            padding: 10px 10px 14px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .app-sidebar__item {
            text-decoration: none;
            color: rgba(255, 255, 255, 0.85);
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 12px;
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
            padding: 14px 12px 16px;
        }

        .app-sidebar__user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 10px;
            border-radius: 14px;
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
            padding: 24px 24px 32px;
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
                    <input type="text" placeholder="Search..." class="search-input">
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
                <div class="notification-icon">
                    <i class="fa-solid fa-bell"></i>
                </div>

                <!--  Profile -->
                <button class="profile-avatar-btn" id="profileAvatarButton" type="button">
                    @php
                    $initial = auth()->check() ? mb_substr(auth()->user()->name, 0, 1) : 'A';
                    @endphp
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
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        (function() {
            const avatarButton = document.getElementById('profileAvatarButton');
            const dropdown = document.getElementById('profileDropdown');

            if (!avatarButton || !dropdown) return;

            avatarButton.addEventListener('click', function(event) {
                event.stopPropagation();
                dropdown.classList.toggle('visible');
            });

            document.addEventListener('click', function(event) {
                if (!dropdown.contains(event.target) && !avatarButton.contains(event.target)) {
                    dropdown.classList.remove('visible');
                }
            });
        })();
    </script>
</body>

</html>