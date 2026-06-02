<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- ✅ CSRF TOKEN -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Hospital HMS - Auth')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-soft: #dbeafe;
            --accent: #06b6d4;
            --danger: #e53935;
            --gray-light: #f3f5f9;
            --border-color: #dde2ee;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --card-bg: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background:
                radial-gradient(900px 520px at 12% 10%, rgba(37, 99, 235, 0.18), transparent 60%),
                radial-gradient(700px 420px at 88% 20%, rgba(6, 182, 212, 0.14), transparent 55%),
                linear-gradient(135deg, #eef3fb, #f9fbff 60%, #eef6ff);
            color: var(--text-main);
        }

        .auth-wrapper {
            width: 100%;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
        }

        .auth-shell {
            width: min(1180px, 100%);
            min-height: 720px;
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            overflow: hidden;
            border-radius: 28px;
            background: rgba(255, 255, 255, 0.72);
            border: 1px solid rgba(255, 255, 255, 0.72);
            box-shadow: 0 28px 80px rgba(15, 23, 42, 0.14);
            backdrop-filter: blur(14px);
        }

        .auth-visual {
            position: relative;
            padding: 36px;
            color: #ffffff;
            display: flex;
            align-items: flex-end;
            background:
                linear-gradient(135deg,
                    rgba(15, 23, 42, 0.92) 0%,
                    rgba(17, 94, 89, 0.88) 18%,
                    rgba(37, 99, 235, 0.92) 42%,
                    rgba(168, 85, 247, 0.84) 68%,
                    rgba(14, 165, 233, 0.88) 100%);
        }

        .auth-visual::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 18% 18%, rgba(255, 255, 255, 0.18), transparent 22%),
                radial-gradient(circle at 82% 24%, rgba(6, 182, 212, 0.24), transparent 18%),
                radial-gradient(circle at 72% 82%, rgba(255, 255, 255, 0.14), transparent 20%),
                radial-gradient(circle at 22% 82%, rgba(244, 114, 182, 0.16), transparent 16%),
                radial-gradient(circle at 88% 68%, rgba(251, 191, 36, 0.12), transparent 18%);
            pointer-events: none;
        }

        .auth-visual-content {
            position: relative;
            z-index: 1;
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 22px;
        }

        .auth-visual-brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .auth-visual-brand-mark {
            width: 56px;
            height: 56px;
            /* border-radius: 18px; */
            display: grid;
            place-items: center;
            /* background: rgba(255, 255, 255, 0.14); */
            /* border: 1px solid rgba(255, 255, 255, 0.18); */
            /* box-shadow: 0 18px 30px rgba(15, 23, 42, 0.18); */
            overflow: hidden;
            flex: 0 0 auto;
        }

        .auth-visual-brand-mark img {
            width: 42px;
            height: 42px;
            object-fit: contain;
            display: block;
        }

        .auth-visual-brand-copy {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .auth-visual-brand-title {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .auth-visual-brand-subtitle {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.78);
        }

        .auth-visual-hero {
            display: grid;
            grid-template-columns: 118px 1fr;
            gap: 18px;
            align-items: center;
            margin-top: 4px;
        }

        .auth-visual-hero-badge {
            width: 118px;
            height: 118px;
            border-radius: 34px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.14);
            display: grid;
            place-items: center;
            position: relative;
            overflow: hidden;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.16);
        }

        .auth-visual-hero-badge::before {
            content: '';
            position: absolute;
            width: 84px;
            height: 84px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.14);
            background: rgba(255, 255, 255, 0.08);
        }

        .auth-visual-hero-badge::after {
            content: '';
            position: absolute;
            width: 42px;
            height: 42px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.18);
        }

        .auth-visual-hero-badge i {
            position: relative;
            z-index: 1;
            font-size: 40px;
            color: #ffffff;
        }

        .auth-visual-hero-copy {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .auth-visual-badge {
            width: fit-content;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.16);
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.02em;
        }

        .auth-visual-badge-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: #7dd3fc;
            box-shadow: 0 0 0 6px rgba(125, 211, 252, 0.18);
        }

        .auth-visual-title {
            font-size: 40px;
            line-height: 1.02;
            margin: 0;
            font-weight: 800;
            letter-spacing: -0.04em;
            max-width: 12ch;
        }

        .auth-visual-copy {
            max-width: 500px;
            font-size: 15px;
            line-height: 1.65;
            color: rgba(255, 255, 255, 0.86);
            margin: 0;
        }

        .auth-visual-pill-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .auth-visual-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.14);
            font-size: 12px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.94);
        }

        .auth-visual-pill i {
            color: #dbeafe;
        }

        .auth-visual-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .auth-visual-stat {
            padding: 14px 14px 13px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.14);
        }

        .auth-visual-stat-value {
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .auth-visual-stat-label {
            font-size: 12px;
            line-height: 1.45;
            color: rgba(255, 255, 255, 0.78);
        }

        .auth-visual-list {
            display: grid;
            gap: 10px;
            margin: 0;
            padding: 0;
            list-style: none;
            max-width: 420px;
        }

        .auth-visual-list li {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.84);
        }

        .auth-visual-list i {
            width: 26px;
            height: 26px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            font-size: 11px;
        }

        .auth-visual-feature-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .auth-visual-feature {
            padding: 14px 14px 13px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.14);
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .auth-visual-feature-icon {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            background: rgba(255, 255, 255, 0.14);
            color: #ffffff;
            flex: 0 0 auto;
        }

        .auth-visual-feature-title {
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .auth-visual-feature-text {
            font-size: 12px;
            line-height: 1.55;
            color: rgba(255, 255, 255, 0.76);
        }

        .auth-visual-board {
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            gap: 12px;
        }

        .auth-visual-board-card {
            min-height: 152px;
            border-radius: 20px;
            padding: 16px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.14);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.12);
        }

        .auth-visual-board-title {
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.72);
            margin-bottom: 10px;
        }

        .auth-visual-board-big {
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .auth-visual-board-copy {
            font-size: 12px;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.8);
        }

        .auth-visual-board-list {
            display: grid;
            gap: 10px;
        }

        .auth-visual-board-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.88);
        }

        .auth-visual-board-item i {
            width: 28px;
            height: 28px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: rgba(255, 255, 255, 0.14);
            color: #ffffff;
            font-size: 11px;
        }

        .auth-panel {
            padding: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(249, 251, 255, 0.96));
        }

        .auth-panel-inner {
            width: 100%;
            max-width: 430px;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .auth-header h1 {
            font-size: 26px;
            font-weight: 600;
            margin: 0;
            color: #111827;
        }

        .auth-subtitle {
            margin-top: 6px;
            font-size: 13px;
            color: var(--text-muted);
        }

        .auth-grid {
            width: 100%;
        }

        .auth-card {
            background: var(--card-bg);
            border-radius: 20px;
            box-shadow: 0 16px 45px rgba(15, 23, 42, 0.08);
            padding: 28px 28px 24px;
            border: 1px solid rgba(226, 232, 240, 0.9);
        }

        .auth-card-login {
            min-height: 560px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .auth-brand {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            justify-content: center;
        }

        .auth-brand-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .auth-brand-title {
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 0.01em;
            color: var(--primary);
        }

        .auth-brand-center {
            justify-content: center;
        }

        .auth-brand-sub {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.16em;
            color: var(--text-muted);
        }

        .auth-card-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .auth-card-form {
            padding: 18px 18px 16px;
            /* box-shadow: 0 16px 45px rgba(15, 23, 42, 0.08); */
            /* border-radius: 18px; */
            /* background: #ffffff; */
            /* margin-top: 18px; */
            /* border: 1px solid rgba(226, 232, 240, 0.8); */
        }

        .auth-card-title-center {
            text-align: center;
            margin-bottom: 14px;
        }

        .auth-card-caption {
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 18px;
        }

        .form-group {
            margin-bottom: 14px;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 6px;
            color: #4b5563;
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 11px 12px;
            font-size: 13px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            background-color: #f9fafb;
            transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 1px rgba(37, 99, 235, 0.3);
            background-color: #ffffff;
        }

        .form-input::placeholder {
            color: #9ca3af;
        }

        .form-row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .form-actions {
            margin-top: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: var(--text-muted);
        }

        .remember-me input[type="checkbox"] {
            width: 14px;
            height: 14px;
            border-radius: 4px;
            border: 1px solid var(--border-color);
        }

        .btn-primary {
            border: none;
            border-radius: 14px;
            padding: 12px 22px;
            font-size: 14px;
            font-weight: 700;
            color: #ffffff;
            background: linear-gradient(135deg, var(--primary), #1e40af);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            box-shadow: 0 16px 28px rgba(37, 99, 235, 0.22);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            /* box-shadow: 0 14px 30px rgba(30, 136, 229, 0.42); */
            background: linear-gradient(135deg, #2196f3, #1e88e5);
        }

        .btn-primary:active {
            transform: translateY(0);
            box-shadow: 0 8px 16px rgba(30, 136, 229, 0.35);
        }

        .link-muted {
            font-size: 12px;
            color: var(--primary);
            text-decoration: none;
        }

        .link-muted:hover {
            text-decoration: underline;
        }

        .text-danger {
            color: var(--danger);
            font-size: 11px;
            margin-top: 4px;
        }

        .alert {
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 12px;
            margin-bottom: 14px;
        }

        .alert-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #047857;
        }

        .alert-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }

        @media (max-width: 960px) {
            .auth-wrapper {
                padding: 24px 14px;
            }

            .auth-shell {
                grid-template-columns: 1fr;
                min-height: auto;
            }

            .auth-visual {
                padding: 26px 22px;
            }

            .auth-visual-title {
                font-size: 32px;
            }

            .auth-visual-hero {
                grid-template-columns: 1fr;
            }

            .auth-visual-hero-badge {
                width: 96px;
                height: 96px;
            }

            .auth-visual-stats {
                grid-template-columns: 1fr;
            }

            .auth-visual-feature-grid {
                grid-template-columns: 1fr;
            }

            .auth-visual-board {
                grid-template-columns: 1fr;
            }

            .auth-panel {
                padding: 22px;
            }
        }

        @media (max-width: 640px) {
            .auth-card {
                padding: 20px 16px 18px;
            }

            .auth-header h1 {
                font-size: 22px;
            }

            .auth-visual-copy {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>

    <div class="auth-wrapper">
        <div class="auth-shell">
            <aside class="auth-visual">
                <div class="auth-visual-content">
                    <div class="auth-visual-brand">
                        <div class="auth-visual-brand-mark">
                            <img src="{{ asset('images/HMS logo.png') }}" alt="Hospital HMS Logo">
                        </div>
                        <div class="auth-visual-brand-copy">
                            <div class="auth-visual-brand-title">Hospital HMS</div>
                            <div class="auth-visual-brand-subtitle">Secure care management platform</div>
                        </div>
                    </div>

                    <div class="auth-visual-hero">
                        <div class="auth-visual-hero-badge" aria-hidden="true">
                            <i class="fa-solid fa-hospital"></i>
                        </div>
                        <div class="auth-visual-hero-copy">
                            <div>
                                <h1 class="auth-visual-title">Modern care operations in one secure place.</h1>
                                <p class="auth-visual-copy">
                                    A polished workspace for hospital teams, designed to feel calm, fast, and
                                    trustworthy from the first login.
                                </p>
                            </div>
                            <div class="auth-visual-pill-row">
                                <div class="auth-visual-pill"><i class="fa-solid fa-shield-heart"></i> Secure access</div>
                                <div class="auth-visual-pill"><i class="fa-solid fa-stethoscope"></i> Clinical flow</div>
                                <div class="auth-visual-pill"><i class="fa-solid fa-bed-pulse"></i> Patient care</div>
                            </div>
                        </div>
                    </div>

                    <div class="auth-visual-board">
                        <div class="auth-visual-board-card">
                            <div class="auth-visual-board-title">Today</div>
                            <div class="auth-visual-board-big">Live hospital board</div>
                            <div class="auth-visual-board-copy">
                                Track admissions, assigned beds, and key actions in a clean command-center style view.
                            </div>
                        </div>
                        <div class="auth-visual-board-card">
                            <div class="auth-visual-board-title">Quick modules</div>
                            <div class="auth-visual-board-list">
                                <div class="auth-visual-board-item"><i class="fa-solid fa-user-doctor"></i> Doctors and nurses</div>
                                <div class="auth-visual-board-item"><i class="fa-solid fa-vial"></i> Lab and results</div>
                                <div class="auth-visual-board-item"><i class="fa-solid fa-pills"></i> Pharmacy and billing</div>
                            </div>
                        </div>
                    </div>

                    <div class="auth-visual-stats">
                        <div class="auth-visual-stat">
                            <div class="auth-visual-stat-value">24/7</div>
                            <div class="auth-visual-stat-label">Access for hospital teams across shifts.</div>
                        </div>
                        <div class="auth-visual-stat">
                            <div class="auth-visual-stat-value">8+</div>
                            <div class="auth-visual-stat-label">Core modules for staff, patients, billing, and reports.</div>
                        </div>
                        <div class="auth-visual-stat">
                            <div class="auth-visual-stat-value">RBAC</div>
                            <div class="auth-visual-stat-label">Role-based access for secure workflow control.</div>
                        </div>
                    </div>

                    <ul class="auth-visual-list">
                        <li><i class="fa-solid fa-circle-check"></i> Clean dashboard experience for admins and staff.</li>
                        <li><i class="fa-solid fa-circle-check"></i> Fast onboarding for login and sign up.</li>
                        <li><i class="fa-solid fa-circle-check"></i> Responsive layout for desktop and mobile.</li>
                    </ul>
                </div>
            </aside>

            <main class="auth-panel">
                <div class="auth-panel-inner">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <script>
        (function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            document.addEventListener('submit', async function(e) {
                const form = e.target;

                if (!form.matches('form[data-ajax="true"]')) return;

                e.preventDefault(); // prevent default submit

                const url = form.action;
                const method = form.method.toUpperCase() || 'POST';
                const errorBox = form.querySelector('.js-form-error');
                const successBox = form.closest('.auth-card')?.querySelector('.js-form-success') || form.querySelector('.js-form-success');

                if (errorBox) {
                    errorBox.style.display = 'none';
                    errorBox.innerHTML = '';
                }
                if (successBox) {
                    successBox.style.display = 'none';
                    successBox.innerHTML = '';
                }

                const formData = new FormData(form);

                try {
                    const res = await fetch(url, {
                        method,
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: formData,
                        credentials: 'same-origin'
                    });

                    const data = await res.json();

                    if (!res.ok || data.status === 'error') {
                        const message = data.message || Object.values(data.errors || {}).flat().join('<br>') || 'Something went wrong';
                        if (errorBox) {
                            errorBox.innerHTML = message;
                            errorBox.style.display = 'block';
                        }
                        return;
                    }

                    if (data.message && successBox) {
                        successBox.innerHTML = data.message;
                        successBox.style.display = 'block';
                    }

                    // ✅ Redirect if returned
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }

                } catch (err) {
                    if (errorBox) {
                        errorBox.innerHTML = 'Network error. Try again.';
                        errorBox.style.display = 'block';
                    }
                }
            });
        })();
    </script>

</body>

</html>
