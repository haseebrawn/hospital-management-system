<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Hospital HMS - Auth')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1e88e5;
            --primary-dark: #1565c0;
            --danger: #e53935;
            --gray-light: #f3f5f9;
            --border-color: #dde2ee;
            --text-main: #1f2933;
            --text-muted: #6b7280;
            --card-bg: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #eef3fb, #f9fbff);
            color: var(--text-main);
        }

        .auth-wrapper {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 16px;
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
            max-width: 520px;
            margin: 0 auto;
        }

        .auth-card {
            background: var(--card-bg);
            border-radius: 14px;
            box-shadow: 0 16px 45px rgba(15, 23, 42, 0.08);
            padding: 26px 28px 24px;
            border: 1px solid rgba(226, 232, 240, 0.9);
        }

        .auth-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
        }

        .auth-logo {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            background-color: var(--primary);
            position: relative;
            box-shadow: 0 6px 18px rgba(30, 136, 229, 0.45);
        }

        .auth-logo::before,
        .auth-logo::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #ffffff;
            border-radius: 2px;
        }

        .auth-logo::before {
            width: 60%;
            height: 22%;
        }

        .auth-logo::after {
            width: 22%;
            height: 60%;
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
            padding: 9px 11px;
            font-size: 13px;
            border-radius: 9px;
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
            border-radius: 999px;
            padding: 9px 22px;
            font-size: 13px;
            font-weight: 500;
            color: #ffffff;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 22px rgba(30, 136, 229, 0.35);
            transition: transform 0.08s ease, box-shadow 0.08s ease, background 0.15s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 30px rgba(30, 136, 229, 0.42);
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
            border-radius: 10px;
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
                margin: 24px auto 32px;
            }
        }

        @media (max-width: 640px) {
            .auth-card {
                padding: 22px 18px 20px;
            }

            .auth-header h1 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-header">
            <!-- <h1>Authentication Module</h1> -->
            <!-- <div class="auth-subtitle">Desktop Admin Views · Mobile User Views</div> -->
        </div>

        @yield('content')
    </div>

    <script>
        (function () {
            const metaCsrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const forms = document.querySelectorAll('form[data-ajax="true"]');

            forms.forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();

                    const url = form.getAttribute('action');
                    const method = (form.getAttribute('method') || 'POST').toUpperCase();
                    const errorBox = form.querySelector('.js-form-error');
                    const csrfField = form.querySelector('input[name="_token"]');
                    const csrfToken = csrfField ? csrfField.value : metaCsrfToken || '';

                    if (errorBox) {
                        errorBox.style.display = 'none';
                        errorBox.textContent = '';
                    }

                    const formData = new FormData(form);

                    fetch(url, {
                        method: method,
                        credentials: 'same-origin',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: formData,
                    })
                        .then(async (response) => {
                            const data = await response.json().catch(() => ({}));

                            if (!response.ok || data.status === 'error') {
                                const message =
                                    (data && (data.message || (data.errors && Object.values(data.errors)[0][0]))) ||
                                    'Something went wrong. Please try again.';

                                if (errorBox) {
                                    errorBox.textContent = message;
                                    errorBox.style.display = 'block';
                                } else {
                                    alert(message);
                                }
                                return;
                            }

                            if (data.redirect) {
                                window.location.href = data.redirect;
                            }
                        })
                        .catch(() => {
                            if (errorBox) {
                                errorBox.textContent = 'Network error. Please try again.';
                                errorBox.style.display = 'block';
                            } else {
                                alert('Network error. Please try again.');
                            }
                        });
                });
            });
        })();
    </script>
</body>
</html>

