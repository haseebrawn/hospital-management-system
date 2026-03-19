<aside class="app-sidebar" aria-label="Sidebar navigation">
    <div class="app-sidebar__header">
        <div class="app-sidebar__brand">
            <div class="app-sidebar__brand-icon" aria-hidden="true">
                <i class="fa-solid fa-hospital"></i>
            </div>
            <span class="app-sidebar__brand-text">Hospital HMS</span>
        </div>
    </div>

    <nav class="app-sidebar__nav" aria-label="Main">
        <a class="app-sidebar__item {{ request()->routeIs('dashboard') ? 'is-active' : '' }}"
            href="{{ route('dashboard') }}">
            <span class="app-sidebar__icon"><i class="fa-solid fa-gauge"></i></span>
            <span class="app-sidebar__label">Dashboard</span>
        </a>

        <a class="app-sidebar__item" href="#" aria-disabled="true">
            <span class="app-sidebar__icon"><i class="fa-solid fa-user-group"></i></span>
            <span class="app-sidebar__label">Patients</span>
        </a>

        <a class="app-sidebar__item" href="#" aria-disabled="true">
            <span class="app-sidebar__icon"><i class="fa-regular fa-calendar-check"></i></span>
            <span class="app-sidebar__label">Appointments</span>
        </a>

        <a class="app-sidebar__item" href="#" aria-disabled="true">
            <span class="app-sidebar__icon"><i class="fa-solid fa-flask"></i></span>
            <span class="app-sidebar__label">Lab Tests</span>
        </a>

        <a class="app-sidebar__item" href="#" aria-disabled="true">
            <span class="app-sidebar__icon"><i class="fa-solid fa-pills"></i></span>
            <span class="app-sidebar__label">Pharmacy</span>
            <span class="app-sidebar__caret"><i class="fa-solid fa-chevron-down"></i></span>
        </a>

        <a class="app-sidebar__item" href="#" aria-disabled="true">
            <span class="app-sidebar__icon"><i class="fa-solid fa-file-invoice-dollar"></i></span>
            <span class="app-sidebar__label">Billing</span>
        </a>

        <a class="app-sidebar__item" href="#" aria-disabled="true">
            <span class="app-sidebar__icon"><i class="fa-solid fa-user-doctor"></i></span>
            <span class="app-sidebar__label">Staff</span>
        </a>

        <a class="app-sidebar__item" href="#" aria-disabled="true">
            <span class="app-sidebar__icon"><i class="fa-solid fa-clock-rotate-left"></i></span>
            <span class="app-sidebar__label">Shifts</span>
        </a>

        <a class="app-sidebar__item" href="#" aria-disabled="true">
            <span class="app-sidebar__icon"><i class="fa-solid fa-bed"></i></span>
            <span class="app-sidebar__label">Wards &amp; Beds</span>
            <span class="app-sidebar__caret"><i class="fa-solid fa-chevron-down"></i></span>
        </a>

        <a class="app-sidebar__item" href="#" aria-disabled="true">
            <span class="app-sidebar__icon"><i class="fa-solid fa-chart-line"></i></span>
            <span class="app-sidebar__label">Reports</span>
        </a>

        <a class="app-sidebar__item" href="#" aria-disabled="true">
            <span class="app-sidebar__icon"><i class="fa-solid fa-shield-halved"></i></span>
            <span class="app-sidebar__label">Admin Panel</span>
        </a>
    </nav>

    <div class="app-sidebar__footer">
        <div class="app-sidebar__user">
            <div class="app-sidebar__user-avatar" aria-hidden="true">
                @php
                    $initial = auth()->check() ? mb_substr(auth()->user()->name, 0, 1) : 'A';
                @endphp
                {{ mb_strtoupper($initial) }}
            </div>
            <div class="app-sidebar__user-meta">
                <div class="app-sidebar__user-name">
                    @auth
                        {{ auth()->user()->name }}
                    @else
                        Administrator
                    @endauth
                </div>
                <div class="app-sidebar__user-role">
                    @auth
                        {{ optional(auth()->user()->department)->name ?? 'Staff Member' }}
                    @else
                        Administration
                    @endauth
                </div>
            </div>
        </div>
    </div>
</aside>