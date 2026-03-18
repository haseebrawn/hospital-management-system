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

        .main {
            flex: 1;
            padding: 24px 24px 32px;
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

            .main {
                padding: 16px 14px 24px;
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
                <!-- Notification Icon -->
                <div class="notification-icon">
                    <i class="fa-solid fa-bell"></i>
                </div>

                <!-- 👤 Profile -->
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

        <main class="main">
            <div class="">
                @yield('content')
            </div>
        </main>
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