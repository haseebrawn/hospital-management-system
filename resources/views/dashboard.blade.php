@extends('layouts.app')

@section('title', 'Dashboard - Hospital HMS')

@section('content')
<style>
    .dash-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 16px;
    }

    .dash-title {
        font-size: 20px;
        font-weight: 700;
        margin: 0;
    }

    .dash-subtitle {
        margin: 6px 0 0;
        font-size: 13px;
        color: var(--text-muted);
    }

    .dash-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(180px, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .dash-stat {
        border-radius: 16px;
        padding: 16px 16px;
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.16);
        box-shadow: 0 18px 45px rgba(2, 6, 23, 0.12);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        min-height: 88px;
    }

    .dash-stat__meta {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .dash-stat__label {
        font-size: 12px;
        opacity: 0.88;
    }

    .dash-stat__value {
        font-size: 20px;
        font-weight: 700;
        letter-spacing: 0.2px;
    }

    .dash-stat__icon {
        width: 40px;
        height: 40px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.18);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    .dash-stat--blue {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
    }

    .dash-stat--green {
        background: linear-gradient(135deg, #34d399, #059669);
    }

    .dash-stat--yellow {
        background: linear-gradient(135deg, #fbbf24, #f59e0b);
        color: #0f172a;
    }

    .dash-stat--purple {
        background: linear-gradient(135deg, #a78bfa, #7c3aed);
    }

    .dash-grid {
        display: grid;
        grid-template-columns: 1.35fr 0.65fr 1fr;
        gap: 16px;
        align-items: start;
    }

    .dash-card__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
    }

    .dash-card__title {
        font-size: 14px;
        font-weight: 700;
        margin: 0;
    }

    .dash-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    .dash-table th,
    .dash-table td {
        padding: 10px 8px;
        border-bottom: 1px solid rgba(229, 231, 235, 0.95);
        text-align: left;
        white-space: nowrap;
    }

    .dash-table th {
        font-size: 11px;
        color: var(--text-muted);
        font-weight: 600;
    }

    .dash-badge {
        font-size: 11px;
        padding: 4px 8px;
        border-radius: 999px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .dash-badge--upcoming {
        background: rgba(16, 185, 129, 0.14);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.25);
    }

    .dash-badge--checked {
        background: rgba(59, 130, 246, 0.14);
        color: #2563eb;
        border: 1px solid rgba(59, 130, 246, 0.25);
    }

    .dash-badge--cancelled {
        background: rgba(239, 68, 68, 0.12);
        color: #dc2626;
        border: 1px solid rgba(239, 68, 68, 0.25);
    }

    .dash-bed {
        display: grid;
        grid-template-columns: 1fr 1fr;
        /* gap: 12px; */
        margin-top: 10px;
    }

    .dash-bed__ring {
        border-radius: 16px;
        padding: 14px 14px 0px 14px;
        /* border: 1px solid rgba(229, 231, 235, 0.95); */
        display: flex;
        flex-direction: column;
        gap: 10px;
        align-items: center;
        justify-content: center;
        min-height: 120px;
    }

    .dash-bed__circle {
        width: 62px;
        height: 62px;
        border-radius: 999px;
        display: grid;
        place-items: center;
        font-weight: 800;
    }

    .dash-bed__circle--green {
        background: rgba(16, 185, 129, 0.15);
        color: #059669;
        border: 6px solid rgba(16, 185, 129, 0.35);
    }

    .dash-bed__circle--red {
        background: rgba(239, 68, 68, 0.12);
        color: #dc2626;
        border: 6px solid rgba(239, 68, 68, 0.32);
    }

    .dash-bed__label {
        font-size: 12px;
        color: var(--text-muted);
        margin: 0;
    }

    .dash-chart {
        height: 190px;
        border-radius: 14px;
        /* background: linear-gradient(180deg, rgba(59, 130, 246, 0.10), rgba(16, 185, 129, 0.08)); */
        /* border: 1px solid rgba(229, 231, 235, 0.95); */
        padding: 10px 10px 6px;
        overflow: hidden;
    }

    .dash-chart svg {
        width: 100%;
        height: 100%;
        display: block;
    }

    .dash-chart--compact {
        height: 150px;
        padding: 8px 10px 6px;
        /* background: #f8fafc; */
    }

    .dash-legend {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        font-size: 11px;
        color: var(--text-muted);
    }

    .dash-legend__item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .dash-legend__dot {
        width: 9px;
        height: 9px;
        border-radius: 999px;
        background: #60a5fa;
    }

    .dash-legend__dot--green {
        background: #34d399;
    }

    .dash-legend__dot--yellow {
        background: #fbbf24;
    }

    .dash-lower {
        display: grid;
        grid-template-columns: 0.92fr 1.08fr;
        gap: 16px;
        margin-top: 16px;
    }

    .dash-stack {
        display: grid;
        grid-template-rows: auto auto;
        gap: 16px;
        align-items: start;
    }

    .dash-appointment {
        margin-top: 16px;
    }

    .dash-notify {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .dash-notify__item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 10px 0;
        border-bottom: 1px solid rgba(229, 231, 235, 0.95);
        font-size: 12px;
    }

    .dash-notify__left {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .dash-dot {
        width: 9px;
        height: 9px;
        border-radius: 999px;
        background: #60a5fa;
        flex: 0 0 auto;
    }

    .dash-dot--green {
        background: #34d399;
    }

    .dash-dot--red {
        background: #f87171;
    }

    .dash-notify__text {
        min-width: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .dash-notify__time {
        color: var(--text-muted);
        font-size: 11px;
        flex: 0 0 auto;
    }

    @media (max-width: 1200px) {
        .dash-stats {
            grid-template-columns: repeat(2, minmax(180px, 1fr));
        }

        .dash-grid {
            grid-template-columns: 1fr;
        }

        .dash-lower {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="dash-header">
    <div>
        <h2 class="dash-title">Dashboard</h2>
        <!-- <p class="dash-subtitle">
            Welcome,
            <strong>{{ auth()->user()->name }}</strong>
            ({{ optional(auth()->user()->department)->name ?? 'Staff Member' }})
        </p> -->
    </div>
</div>

<div class="dash-stats">
    <div class="dash-stat dash-stat--blue">
        <div class="dash-stat__meta">
            <div class="dash-stat__label">Total Patients</div>
            <div class="dash-stat__value">1,250</div>
        </div>
        <div class="dash-stat__icon"><i class="fa-solid fa-user-group"></i></div>
    </div>
    <div class="dash-stat dash-stat--green">
        <div class="dash-stat__meta">
            <div class="dash-stat__label">New Appointments</div>
            <div class="dash-stat__value">38</div>
        </div>
        <div class="dash-stat__icon"><i class="fa-regular fa-calendar-check"></i></div>
    </div>
    <div class="dash-stat dash-stat--yellow">
        <div class="dash-stat__meta">
            <div class="dash-stat__label">Lab Tests Pending</div>
            <div class="dash-stat__value">12</div>
        </div>
        <div class="dash-stat__icon"><i class="fa-solid fa-flask"></i></div>
    </div>
    <div class="dash-stat dash-stat--purple">
        <div class="dash-stat__meta">
            <div class="dash-stat__label">Today's Revenue</div>
            <div class="dash-stat__value">52,300</div>
        </div>
        <div class="dash-stat__icon"><i class="fa-solid fa-sack-dollar"></i></div>
    </div>
</div>

<div class="dash-grid">
    <div class="card">
        <div class="dash-card__head">
            <h3 class="dash-card__title">Recent Appointments</h3>
            <a href="#" style="font-size:12px; color: var(--primary); text-decoration:none;">View All</a>
        </div>
        <table class="dash-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Doctor</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Sarah Ahmed</td>
                    <td>Dr. Hassan Ali</td>
                    <td><span class="dash-badge dash-badge--upcoming">Upcoming</span></td>
                </tr>
                <tr>
                    <td>John Smith</td>
                    <td>Dr. Ayesha Khan</td>
                    <td><span class="dash-badge dash-badge--checked">Checked-in</span></td>
                </tr>
                <tr>
                    <td>Jatten Stuus</td>
                    <td>Dr. Haider Khan</td>
                    <td><span class="dash-badge dash-badge--cancelled">Cancelled</span></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="card">
        <div class="dash-card__head">
            <h3 class="dash-card__title">Bed Status</h3>
        </div>
        <div class="dash-bed">
            <div class="dash-bed__ring">
                <div class="dash-bed__circle dash-bed__circle--green">31</div>
                <p class="dash-bed__label">Available</p>
            </div>
            <div class="dash-bed__ring">
                <div class="dash-bed__circle dash-bed__circle--red">30</div>
                <p class="dash-bed__label">Occupied</p>
            </div>
        </div>
        <div style="display:flex; justify-content:space-around; margin-top: 0px; font-size:12px; color: var(--text-muted);">
            <span> <strong style="color:#059669;">41</strong></span>
            <span> <strong style="color:#dc2626;">26</strong></span>
        </div>
    </div>

    <div class="card">
        <div class="dash-card__head">
            <h3 class="dash-card__title">Patients Overview</h3>
        </div>
        <div class="dash-chart dash-chart--compact" role="img" aria-label="Patients overview chart (static)">
            <svg viewBox="0 0 360 150" preserveAspectRatio="none" aria-hidden="true">
                <defs>
                    <linearGradient id="poFill" x1="0" x2="0" y1="0" y2="1">
                        <stop offset="0%" stop-color="#60a5fa" stop-opacity="0.35" />
                        <stop offset="100%" stop-color="#60a5fa" stop-opacity="0.02" />
                    </linearGradient>
                </defs>

                <!-- grid -->
                <g opacity="0.45" stroke="#cbd5e1" stroke-width="1" stroke-dasharray="2 4">
                    <line x1="36" y1="24" x2="350" y2="24" />
                    <line x1="36" y1="54" x2="350" y2="54" />
                    <line x1="36" y1="84" x2="350" y2="84" />
                    <line x1="36" y1="114" x2="350" y2="114" />
                </g>

                <!-- area -->
                <path d="M36,112 L90,70 L144,84 L198,60 L252,72 L306,44 L350,30 L350,132 L36,132 Z" fill="url(#poFill)" />

                <!-- line -->
                <path d="M36,112 L90,70 L144,84 L198,60 L252,72 L306,44 L350,30" fill="none" stroke="#3b82f6"
                    stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />

                <!-- points -->
                <g fill="#ffffff" stroke="#3b82f6" stroke-width="3">
                    <circle cx="36" cy="112" r="4.5" />
                    <circle cx="90" cy="70" r="4.5" />
                    <circle cx="144" cy="84" r="4.5" />
                    <circle cx="198" cy="60" r="4.5" />
                    <circle cx="252" cy="72" r="4.5" />
                    <circle cx="306" cy="44" r="4.5" />
                    <circle cx="350" cy="30" r="4.5" />
                </g>

                <!-- labels -->
                <g fill="#64748b" font-size="11" font-family="Inter, system-ui, sans-serif">
                    <text x="18" y="28" text-anchor="end">300</text>
                    <text x="18" y="58" text-anchor="end">200</text>
                    <text x="18" y="88" text-anchor="end">100</text>
                    <text x="22" y="118" text-anchor="end">50</text>

                    <text x="36" y="140" text-anchor="start">May</text>
                    <text x="116" y="140" text-anchor="middle">6605</text>
                    <text x="198" y="140" text-anchor="middle">Mar</text>
                    <text x="278" y="140" text-anchor="middle">App</text>
                    <text x="350" y="140" text-anchor="end">Anyy</text>
                </g>
            </svg>
        </div>
    </div>
</div>

<div class="dash-lower">
    <div class="dash-stack">
        <div class="card">
            <div class="dash-card__head">
                <h3 class="dash-card__title">Notifications</h3>
                <a href="#" style="font-size:12px; color: var(--primary); text-decoration:none;">View All</a>
            </div>
            <div class="dash-notify">
                <div class="dash-notify__item">
                    <div class="dash-notify__left">
                        <span class="dash-dot dash-dot--green"></span>
                        <div class="dash-notify__text">New patient Sarah Ahmed registered</div>
                    </div>
                    <div class="dash-notify__time">Just now</div>
                </div>
                <div class="dash-notify__item">
                    <div class="dash-notify__left">
                        <span class="dash-dot"></span>
                        <div class="dash-notify__text">New appointment for Ali Raza</div>
                    </div>
                    <div class="dash-notify__time">2m ago</div>
                </div>
                <div class="dash-notify__item" style="border-bottom:0;">
                    <div class="dash-notify__left">
                        <span class="dash-dot dash-dot--red"></span>
                        <div class="dash-notify__text">Bed 14 ready for next patient</div>
                    </div>
                    <div class="dash-notify__time">1h ago</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="dash-card__head">
                <h3 class="dash-card__title">Notifications</h3>
                <a href="#" style="font-size:12px; color: var(--primary); text-decoration:none;">View All</a>
            </div>
            <div class="dash-notify">
                <div class="dash-notify__item">
                    <div class="dash-notify__left">
                        <span class="dash-dot"></span>
                        <div class="dash-notify__text">Inventory updated in Pharmacy</div>
                    </div>
                    <div class="dash-notify__time">Today</div>
                </div>
                <div class="dash-notify__item">
                    <div class="dash-notify__left">
                        <span class="dash-dot dash-dot--green"></span>
                        <div class="dash-notify__text">Lab report ready for Patient #665</div>
                    </div>
                    <div class="dash-notify__time">Yesterday</div>
                </div>
                <div class="dash-notify__item" style="border-bottom:0;">
                    <div class="dash-notify__left">
                        <span class="dash-dot dash-dot--red"></span>
                        <div class="dash-notify__text">Billing reminder: pending invoices</div>
                    </div>
                    <div class="dash-notify__time">2d ago</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="dash-card__head">
            <h3 class="dash-card__title">Hospital Revenue</h3>
            <div class="dash-legend" aria-label="Legend">
                <span class="dash-legend__item"><span class="dash-legend__dot"></span>Billing</span>
                <span class="dash-legend__item"><span class="dash-legend__dot dash-legend__dot--green"></span>Pharmacy</span>
                <span class="dash-legend__item"><span class="dash-legend__dot dash-legend__dot--yellow"></span>Lab</span>
            </div>
        </div>
        <div class="dash-chart" role="img" aria-label="Hospital revenue chart (static)">
            <svg viewBox="0 0 520 190" preserveAspectRatio="none" aria-hidden="true">
                <defs>
                    <linearGradient id="revB" x1="0" x2="0" y1="0" y2="1">
                        <stop offset="0%" stop-color="#60a5fa" stop-opacity="0.28" />
                        <stop offset="100%" stop-color="#60a5fa" stop-opacity="0.03" />
                    </linearGradient>
                    <linearGradient id="revG" x1="0" x2="0" y1="0" y2="1">
                        <stop offset="0%" stop-color="#34d399" stop-opacity="0.22" />
                        <stop offset="100%" stop-color="#34d399" stop-opacity="0.03" />
                    </linearGradient>
                    <linearGradient id="revY" x1="0" x2="0" y1="0" y2="1">
                        <stop offset="0%" stop-color="#fbbf24" stop-opacity="0.18" />
                        <stop offset="100%" stop-color="#fbbf24" stop-opacity="0.03" />
                    </linearGradient>
                </defs>

                <g opacity="0.35" stroke="#cbd5e1" stroke-width="1">
                    <line x1="40" y1="30" x2="510" y2="30" />
                    <line x1="40" y1="70" x2="510" y2="70" />
                    <line x1="40" y1="110" x2="510" y2="110" />
                    <line x1="40" y1="150" x2="510" y2="150" />
                </g>

                <!-- areas -->
                <path d="M40,140 L110,120 L180,132 L250,92 L320,110 L390,78 L460,86 L510,60 L510,170 L40,170 Z"
                    fill="url(#revB)" />
                <path d="M40,150 L110,138 L180,146 L250,112 L320,126 L390,98 L460,106 L510,84 L510,170 L40,170 Z"
                    fill="url(#revG)" />
                <path d="M40,158 L110,148 L180,154 L250,132 L320,138 L390,124 L460,128 L510,110 L510,170 L40,170 Z"
                    fill="url(#revY)" />

                <!-- lines -->
                <path d="M40,140 L110,120 L180,132 L250,92 L320,110 L390,78 L460,86 L510,60" fill="none"
                    stroke="#3b82f6" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M40,150 L110,138 L180,146 L250,112 L320,126 L390,98 L460,106 L510,84" fill="none"
                    stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" opacity="0.95" />
                <path d="M40,158 L110,148 L180,154 L250,132 L320,138 L390,124 L460,128 L510,110" fill="none"
                    stroke="#f59e0b" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" opacity="0.92" />

                <g fill="#64748b" font-size="11" font-family="Inter, system-ui, sans-serif">
                    <text x="40" y="184" text-anchor="start">Jan</text>
                    <text x="180" y="184" text-anchor="middle">Mar</text>
                    <text x="320" y="184" text-anchor="middle">May</text>
                    <text x="460" y="184" text-anchor="middle">Jul</text>
                    <text x="510" y="184" text-anchor="end">Sep</text>
                </g>
            </svg>
        </div>
    </div>
</div>

<div class="card dash-appointment">
    <div class="dash-card__head">
        <h3 class="dash-card__title">Appointment Overview</h3>
        <div class="dash-legend" aria-label="Legend">
            <span class="dash-legend__item"><span class="dash-legend__dot"></span>New Patients</span>
            <span class="dash-legend__item"><span class="dash-legend__dot dash-legend__dot--green"></span>Appointment</span>
            <span class="dash-legend__item"><span class="dash-legend__dot dash-legend__dot--yellow"></span>Pending</span>
            <span class="dash-legend__item"><span class="dash-legend__dot" style="background:#f87171;"></span>Cancelled</span>
        </div>
    </div>
    <div class="dash-chart" style="height: 220px;" role="img" aria-label="Appointment overview chart (static)">
        <svg viewBox="0 0 760 220" preserveAspectRatio="none" aria-hidden="true">
            <g opacity="0.35" stroke="#cbd5e1" stroke-width="1">
                <line x1="40" y1="35" x2="740" y2="35" />
                <line x1="40" y1="75" x2="740" y2="75" />
                <line x1="40" y1="115" x2="740" y2="115" />
                <line x1="40" y1="155" x2="740" y2="155" />
                <line x1="40" y1="195" x2="740" y2="195" />
            </g>

            <!-- grouped bars -->
            <g>
                <!-- Billing -->
                <rect x="80" y="120" width="22" height="75" rx="6" fill="#60a5fa" opacity="0.9" />
                <rect x="108" y="90" width="22" height="105" rx="6" fill="#34d399" opacity="0.9" />
                <rect x="136" y="135" width="22" height="60" rx="6" fill="#fbbf24" opacity="0.9" />
                <rect x="164" y="150" width="22" height="45" rx="6" fill="#f87171" opacity="0.9" />

                <!-- Pharmacy -->
                <rect x="250" y="105" width="22" height="90" rx="6" fill="#60a5fa" opacity="0.9" />
                <rect x="278" y="70" width="22" height="125" rx="6" fill="#34d399" opacity="0.9" />
                <rect x="306" y="120" width="22" height="75" rx="6" fill="#fbbf24" opacity="0.9" />
                <rect x="334" y="160" width="22" height="35" rx="6" fill="#f87171" opacity="0.9" />

                <!-- Lab -->
                <rect x="420" y="115" width="22" height="80" rx="6" fill="#60a5fa" opacity="0.9" />
                <rect x="448" y="80" width="22" height="115" rx="6" fill="#34d399" opacity="0.9" />
                <rect x="476" y="110" width="22" height="85" rx="6" fill="#fbbf24" opacity="0.9" />
                <rect x="504" y="165" width="22" height="30" rx="6" fill="#f87171" opacity="0.9" />

                <!-- Total -->
                <rect x="590" y="85" width="22" height="110" rx="6" fill="#60a5fa" opacity="0.9" />
                <rect x="618" y="50" width="22" height="145" rx="6" fill="#34d399" opacity="0.9" />
                <rect x="646" y="95" width="22" height="100" rx="6" fill="#fbbf24" opacity="0.9" />
                <rect x="674" y="140" width="22" height="55" rx="6" fill="#f87171" opacity="0.9" />
            </g>

            <g fill="#64748b" font-size="11" font-family="Inter, system-ui, sans-serif">
                <text x="122" y="212" text-anchor="middle">Billing</text>
                <text x="292" y="212" text-anchor="middle">Pharmacy</text>
                <text x="462" y="212" text-anchor="middle">Lab</text>
                <text x="632" y="212" text-anchor="middle">Total</text>
            </g>
        </svg>
    </div>
</div>
@endsection