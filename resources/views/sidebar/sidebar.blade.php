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

        @auth
            @if (auth()->user()->hasAnyRole(['super_admin', 'admin', 'doctor', 'nurse', 'receptionist']))
                <a class="app-sidebar__item {{ request()->routeIs('patients.*') ? 'is-active' : '' }}"
                    href="{{ route('patients.index') }}">
                    <span class="app-sidebar__icon"><i class="fa-solid fa-user-group"></i></span>
                    <span class="app-sidebar__label">Patients</span>
                </a>
            @endif
        @endauth

        @auth
            @if (auth()->user()->hasAnyRole(['super_admin', 'admin', 'doctor', 'nurse', 'receptionist']))
                <a class="app-sidebar__item {{ request()->routeIs('appointments.*') ? 'is-active' : '' }}"
                    href="{{ route('appointments.index') }}">
                    <span class="app-sidebar__icon"><i class="fa-regular fa-calendar-check"></i></span>
                    <span class="app-sidebar__label">Appointments</span>
                </a>
            @endif
        @endauth

        @auth
            @if (auth()->user()->hasAnyRole(['super_admin', 'admin', 'lab_technician', 'doctor']))
                <a class="app-sidebar__item {{ request()->routeIs('lab-tests.*') ? 'is-active' : '' }}"
                    href="{{ route('lab-tests.index') }}">
                    <span class="app-sidebar__icon"><i class="fa-solid fa-flask"></i></span>
                    <span class="app-sidebar__label">Lab Tests</span>
                </a>
            @endif
        @endauth

        @auth
            @if (auth()->user()->hasAnyRole(['super_admin', 'admin', 'pharmacist']))
                <a class="app-sidebar__item {{ request()->routeIs('medicines.*') ? 'is-active' : '' }}"
                    href="{{ route('medicines.index') }}">
                    <span class="app-sidebar__icon"><i class="fa-solid fa-pills"></i></span>
                    <span class="app-sidebar__label">Pharmacy</span>
                </a>
            @endif
        @endauth

        @auth
            @if (auth()->user()->hasAnyRole(['super_admin', 'admin', 'accountant']))
                <a class="app-sidebar__item {{ request()->routeIs('billing.*') ? 'is-active' : '' }}"
                    href="{{ route('billing.index') }}">
                    <span class="app-sidebar__icon"><i class="fa-solid fa-file-invoice-dollar"></i></span>
                    <span class="app-sidebar__label">Billing</span>
                </a>
            @endif
        @endauth

        @auth
            @if (auth()->user()->hasAnyRole(['super_admin', 'admin', 'hr_manager']))
                <a class="app-sidebar__item {{ request()->routeIs('staff.*') ? 'is-active' : '' }}"
                    href="{{ route('staff.index') }}">
                    <span class="app-sidebar__icon"><i class="fa-solid fa-user-doctor"></i></span>
                    <span class="app-sidebar__label">Staff</span>
                </a>
            @endif
        @endauth

        @auth
            @if (auth()->user()->hasAnyRole(['super_admin', 'admin', 'hr_manager', 'doctor']))
                <a class="app-sidebar__item {{ request()->routeIs('shifts.*') ? 'is-active' : '' }}"
                    href="{{ route('shifts.index') }}">
                    <span class="app-sidebar__icon"><i class="fa-solid fa-clock-rotate-left"></i></span>
                    <span class="app-sidebar__label">Shifts</span>
                </a>
            @endif
        @endauth

        @auth
            @if (auth()->user()->hasAnyRole(['super_admin', 'admin', 'nurse', 'doctor']))
                <a class="app-sidebar__item {{ request()->routeIs('wards-beds.*') ? 'is-active' : '' }}"
                    href="{{ route('wards-beds.index') }}">
                    <span class="app-sidebar__icon"><i class="fa-solid fa-bed"></i></span>
                    <span class="app-sidebar__label">Wards &amp; Beds</span>
                </a>
            @endif
        @endauth

        @auth
            @if (auth()->user()->hasAnyRole(['super_admin', 'admin', 'doctor', 'nurse', 'receptionist', 'accountant', 'pharmacist', 'lab_technician', 'hr_manager']))
                <a class="app-sidebar__item {{ request()->routeIs('reports.*') ? 'is-active' : '' }}"
                    href="{{ route('reports.index') }}">
                    <span class="app-sidebar__icon"><i class="fa-solid fa-chart-line"></i></span>
                    <span class="app-sidebar__label">Reports</span>
                </a>
            @endif
        @endauth

        @auth
            @if (auth()->user()->hasAnyRole(['super_admin', 'admin']))
                <a class="app-sidebar__item {{ request()->routeIs('admin.appointments.*') ? 'is-active' : '' }}"
                    href="{{ route('admin.appointments.index') }}">
                    <span class="app-sidebar__icon"><i class="fa-solid fa-calendar-check"></i></span>
                    <span class="app-sidebar__label">Admin Appointments</span>
                </a>

                <a class="app-sidebar__item {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}"
                    href="{{ route('admin.users.index') }}">
                    <span class="app-sidebar__icon"><i class="fa-solid fa-users-gear"></i></span>
                    <span class="app-sidebar__label">Admin Users</span>
                </a>

                <a class="app-sidebar__item {{ request()->routeIs('system.backups.*') ? 'is-active' : '' }}"
                    href="{{ route('system.backups.index') }}">
                    <span class="app-sidebar__icon"><i class="fa-solid fa-database"></i></span>
                    <span class="app-sidebar__label">Backups</span>
                </a>

                <a class="app-sidebar__item {{ request()->routeIs('system.logs.*') ? 'is-active' : '' }}"
                    href="{{ route('system.logs.activity') }}">
                    <span class="app-sidebar__icon"><i class="fa-solid fa-clipboard-list"></i></span>
                    <span class="app-sidebar__label">Audit Logs</span>
                </a>
            @endif
        @endauth
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
