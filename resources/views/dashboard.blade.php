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

    /* =========================
   📱 MOBILE (max-width: 640px)
    ========================= */
    @media (max-width: 640px) {

        .dash-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .dash-title {
            font-size: 16px;
        }

        .dash-stats {
            grid-template-columns: 1fr;
        }

        .dash-grid {
            grid-template-columns: 1fr;
        }

        .dash-lower {
            grid-template-columns: 1fr;
        }

        .dash-bed {
            grid-template-columns: 1fr;
        }

        .dash-stat {
            min-height: auto;
            padding: 12px;
        }

        .dash-table {
            display: block;
            overflow-x: auto;
        }

        .dash-chart {
            height: 150px;
        }

        .dash-chart--compact {
            height: 120px;
        }
    }

    /* =========================
   📲 TABLET (641px - 1024px)
    ========================= */
    @media (min-width: 641px) and (max-width: 1024px) {

        .dash-stats {
            grid-template-columns: repeat(2, 1fr);
        }

        .dash-grid {
            grid-template-columns: 1fr;
        }

        .dash-lower {
            grid-template-columns: 1fr;
        }

        .dash-bed {
            grid-template-columns: 1fr 1fr;
        }
    }

    /* =========================
   💻 LAPTOP (1025px - 1440px)
     ========================= */
    @media (min-width: 1025px) and (max-width: 1440px) {

        .dash-stats {
            grid-template-columns: repeat(4, 1fr);
        }

        .dash-grid {
            grid-template-columns: 1fr 1fr;
        }

        .dash-lower {
            grid-template-columns: 1fr;
        }
    }

    /* =========================
   🖥️ LARGE SCREENS (1441px+)
     ========================= */
    @media (min-width: 1441px) {

        .dash-stats {
            grid-template-columns: repeat(4, 1fr);
        }

        .dash-grid {
            grid-template-columns: 1.35fr 0.65fr 1fr;
        }

        .dash-lower {
            grid-template-columns: 0.92fr 1.08fr;
        }
    }

    /* @media (max-width: 1200px) {
        .dash-stats {
            grid-template-columns: repeat(2, minmax(180px, 1fr));
        }

        .dash-grid {
            grid-template-columns: 1fr;
        }

        .dash-lower {
            grid-template-columns: 1fr;
        }
    } */
</style>

<div class="dash-header">
    <div>
        <h2 class="dash-title">Dashboard</h2>
    </div>
</div>

<div class="dash-stats">
    <div class="dash-stat dash-stat--blue">
        <div class="dash-stat__meta">
            <div class="dash-stat__label">Total Patients</div>
            <div class="dash-stat__value" id="dashTotalPatients">{{ number_format($totalPatients ?? 0) }}</div>
        </div>
        <div class="dash-stat__icon"><i class="fa-solid fa-user-group"></i></div>
    </div>
    <div class="dash-stat dash-stat--green">
        <div class="dash-stat__meta">
            <div class="dash-stat__label">New Appointments</div>
            <div class="dash-stat__value" id="dashNewAppointments">{{ number_format($newAppointments ?? 0) }}</div>
        </div>
        <div class="dash-stat__icon"><i class="fa-regular fa-calendar-check"></i></div>
    </div>
    <div class="dash-stat dash-stat--yellow">
        <div class="dash-stat__meta">
            <div class="dash-stat__label">Lab Tests Pending</div>
            <div class="dash-stat__value" id="dashLabPending">{{ number_format($labTestsPending ?? 0) }}</div>
        </div>
        <div class="dash-stat__icon"><i class="fa-solid fa-flask"></i></div>
    </div>
    <div class="dash-stat dash-stat--purple">
        <div class="dash-stat__meta">
            <div class="dash-stat__label">Today's Revenue</div>
            <div class="dash-stat__value" id="dashTodaysRevenue">{{ number_format((float) ($todaysRevenue ?? 0), 2) }}</div>
        </div>
        <div class="dash-stat__icon"><i class="fa-solid fa-sack-dollar"></i></div>
    </div>
</div>

<div class="dash-grid">
    <div class="card">
        <div class="dash-card__head">
            <h3 class="dash-card__title">Recent Appointments</h3>
            <a href="{{ route('appointments.index') }}" style="font-size:12px; color: var(--primary); text-decoration:none;">View All</a>
        </div>
        <table class="dash-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Doctor</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="dashRecentAppointmentsBody">
                @php
                    $statusClass = function (string $status): string {
                        return match ($status) {
                            'approved', 'completed' => 'dash-badge--checked',
                            'cancelled' => 'dash-badge--cancelled',
                            default => 'dash-badge--upcoming',
                        };
                    };
                @endphp

                @forelse (($recentAppointments ?? []) as $appt)
                    <tr>
                        <td>
                            {{ optional($appt->patient)->first_name }} {{ optional($appt->patient)->last_name }}
                        </td>
                        <td>{{ optional($appt->doctor)->name ?? '—' }}</td>
                        <td>
                            <span class="dash-badge {{ $statusClass((string) ($appt->status ?? 'pending')) }}">
                                {{ ucfirst(str_replace('_', ' ', (string) ($appt->status ?? 'pending'))) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="padding: 12px;">No appointments yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card">
        <div class="dash-card__head">
            <h3 class="dash-card__title">Bed Status</h3>
        </div>
        <div class="dash-bed">
            <div class="dash-bed__ring">
                <div class="dash-bed__circle dash-bed__circle--green" id="dashBedsAvailable">{{ number_format($availableBeds ?? 0) }}</div>
                <p class="dash-bed__label">Available</p>
            </div>
            <div class="dash-bed__ring">
                <div class="dash-bed__circle dash-bed__circle--red" id="dashBedsOccupied">{{ number_format($occupiedBeds ?? 0) }}</div>
                <p class="dash-bed__label">Occupied</p>
            </div>
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
                <a href="#" style="font-size:12px; color: var(--primary); text-decoration:none;">Latest</a>
            </div>
            <div class="dash-notify" id="dashNotifications">
                @forelse (($notifications ?? []) as $n)
                    <div class="dash-notify__item" @if ($loop->last) style="border-bottom:0;" @endif>
                        <div class="dash-notify__left">
                            <span class="dash-dot"></span>
                            <div class="dash-notify__text">{{ $n['message'] ?? $n['text'] ?? 'Notification' }}</div>
                        </div>
                        <div class="dash-notify__time">—</div>
                    </div>
                @empty
                    <div class="dash-notify__item" style="border-bottom:0;">
                        <div class="dash-notify__left">
                            <span class="dash-dot"></span>
                            <div class="dash-notify__text">No notifications.</div>
                        </div>
                        <div class="dash-notify__time">—</div>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="dash-card__head">
                <h3 class="dash-card__title">Live Status</h3>
                <span style="font-size:12px; color: var(--text-muted);">Updates every 15s</span>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <span style="padding:6px 10px; border-radius:999px; border:1px solid var(--border-color); font-size:13px;">
                    Server: <b id="dashServerTime">—</b>
                </span>
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

<script>
    (function() {
        const endpoint = @json(route('dashboard.data'));

        const elTotalPatients = document.getElementById('dashTotalPatients');
        const elNewAppointments = document.getElementById('dashNewAppointments');
        const elLabPending = document.getElementById('dashLabPending');
        const elRevenue = document.getElementById('dashTodaysRevenue');
        const elBedsAvailable = document.getElementById('dashBedsAvailable');
        const elBedsOccupied = document.getElementById('dashBedsOccupied');
        const elRecentBody = document.getElementById('dashRecentAppointmentsBody');
        const elNotifications = document.getElementById('dashNotifications');
        const elServerTime = document.getElementById('dashServerTime');

        function formatNumber(n) {
            try {
                return new Intl.NumberFormat().format(n);
            } catch (e) {
                return String(n);
            }
        }

        function formatMoney(n) {
            try {
                return new Intl.NumberFormat(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(n);
            } catch (e) {
                return String(n);
            }
        }

        function badgeClass(status) {
            if (status === 'approved' || status === 'completed') return 'dash-badge--checked';
            if (status === 'cancelled') return 'dash-badge--cancelled';
            return 'dash-badge--upcoming';
        }

        function safeText(s) {
            return (s === null || s === undefined || s === '') ? '—' : String(s);
        }

        async function refreshDashboard() {
            try {
                const res = await fetch(endpoint, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!res.ok) return;

                const data = await res.json();

                if (elTotalPatients) elTotalPatients.textContent = formatNumber(data.total_patients ?? 0);
                if (elNewAppointments) elNewAppointments.textContent = formatNumber(data.new_appointments ?? 0);
                if (elLabPending) elLabPending.textContent = formatNumber(data.lab_tests_pending ?? 0);
                if (elRevenue) elRevenue.textContent = formatMoney(data.todays_revenue ?? 0);
                if (elBedsAvailable) elBedsAvailable.textContent = formatNumber(data.available_beds ?? 0);
                if (elBedsOccupied) elBedsOccupied.textContent = formatNumber(data.occupied_beds ?? 0);
                if (elServerTime) elServerTime.textContent = safeText(data.server_time);

                if (elRecentBody) {
                    const rows = Array.isArray(data.recent_appointments) ? data.recent_appointments : [];
                    if (rows.length === 0) {
                        elRecentBody.innerHTML = '<tr><td colspan="3" style="padding:12px;">No appointments yet.</td></tr>';
                    } else {
                        elRecentBody.innerHTML = rows.map(r => {
                            const status = safeText(r.status || 'pending');
                            return `
                                <tr>
                                    <td>${safeText(r.patient)}</td>
                                    <td>${safeText(r.doctor)}</td>
                                    <td><span class="dash-badge ${badgeClass(status)}">${status.replaceAll('_', ' ')}</span></td>
                                </tr>
                            `;
                        }).join('');
                    }
                }

                if (elNotifications) {
                    const notifs = Array.isArray(data.notifications) ? data.notifications : [];
                    if (notifs.length === 0) {
                        elNotifications.innerHTML = `
                            <div class="dash-notify__item" style="border-bottom:0;">
                                <div class="dash-notify__left">
                                    <span class="dash-dot"></span>
                                    <div class="dash-notify__text">No notifications.</div>
                                </div>
                                <div class="dash-notify__time">—</div>
                            </div>
                        `;
                    } else {
                        elNotifications.innerHTML = notifs.map((n, idx) => {
                            const last = idx === notifs.length - 1;
                            return `
                                <div class="dash-notify__item" ${last ? 'style="border-bottom:0;"' : ''}>
                                    <div class="dash-notify__left">
                                        <span class="dash-dot"></span>
                                        <div class="dash-notify__text">${safeText(n.text)}</div>
                                    </div>
                                    <div class="dash-notify__time">—</div>
                                </div>
                            `;
                        }).join('');
                    }
                }
            } catch (e) {
                // ignore
            }
        }

        refreshDashboard();
        setInterval(refreshDashboard, 15000);
    })();
</script>
@endsection
