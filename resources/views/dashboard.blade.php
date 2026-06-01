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
            <svg id="dashPatientsChartSvg" viewBox="0 0 360 150" preserveAspectRatio="none" aria-hidden="true"></svg>
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
            <svg id="dashRevenueChartSvg" viewBox="0 0 520 190" preserveAspectRatio="none" aria-hidden="true"></svg>
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
    <div class="dash-chart" style="height: 220px;" role="img" aria-label="Appointment overview chart">
        <svg id="dashAppointmentsChartSvg" viewBox="0 0 760 220" preserveAspectRatio="none" aria-hidden="true"></svg>
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
        const elPatientsChart = document.getElementById('dashPatientsChartSvg');
        const elRevenueChart = document.getElementById('dashRevenueChartSvg');
        const elAppointmentsChart = document.getElementById('dashAppointmentsChartSvg');

        function svg(tag) {
            return document.createElementNS('http://www.w3.org/2000/svg', tag);
        }

        function renderLineAreaChart(svgEl, labels, series, options) {
            if (!svgEl) return;
            const W = options?.width ?? 520;
            const H = options?.height ?? 190;
            const padL = options?.padL ?? 40;
            const padR = options?.padR ?? 10;
            const padT = options?.padT ?? 20;
            const padB = options?.padB ?? 20;
            const innerW = W - padL - padR;
            const innerH = H - padT - padB;

            svgEl.innerHTML = '';

            const allValues = series.flatMap(s => s.data.map(v => Number(v || 0)));
            const max = Math.max(1, ...allValues);
            const min = 0;

            const grid = svg('g');
            grid.setAttribute('opacity', '0.35');
            grid.setAttribute('stroke', '#cbd5e1');
            grid.setAttribute('stroke-width', '1');
            for (let i = 0; i < 4; i++) {
                const y = padT + (innerH * i) / 3;
                const line = svg('line');
                line.setAttribute('x1', String(padL));
                line.setAttribute('x2', String(W - padR));
                line.setAttribute('y1', String(y));
                line.setAttribute('y2', String(y));
                grid.appendChild(line);
            }
            svgEl.appendChild(grid);

            const defs = svg('defs');
            series.forEach((s, idx) => {
                const g = svg('linearGradient');
                g.setAttribute('id', `${options.gradPrefix}${idx}`);
                g.setAttribute('x1', '0');
                g.setAttribute('x2', '0');
                g.setAttribute('y1', '0');
                g.setAttribute('y2', '1');
                const a = svg('stop');
                a.setAttribute('offset', '0%');
                a.setAttribute('stop-color', s.color);
                a.setAttribute('stop-opacity', String(s.fillOpacityTop ?? 0.22));
                const b = svg('stop');
                b.setAttribute('offset', '100%');
                b.setAttribute('stop-color', s.color);
                b.setAttribute('stop-opacity', String(s.fillOpacityBottom ?? 0.03));
                g.appendChild(a);
                g.appendChild(b);
                defs.appendChild(g);
            });
            svgEl.appendChild(defs);

            function points(vals) {
                return vals.map((v, i) => {
                    const x = padL + (innerW * (vals.length === 1 ? 0 : i / (vals.length - 1)));
                    const y = padT + innerH - ((v - min) / (max - min)) * innerH;
                    return [x, y];
                });
            }

            series.forEach((s, idx) => {
                const vals = s.data.map(v => Number(v || 0));
                const pts = points(vals);
                if (!pts.length) return;

                const area = svg('path');
                const dArea = `M${pts[0][0]},${pts[0][1]} ` +
                    pts.slice(1).map(p => `L${p[0]},${p[1]}`).join(' ') +
                    ` L${pts[pts.length - 1][0]},${padT + innerH} L${pts[0][0]},${padT + innerH} Z`;
                area.setAttribute('d', dArea);
                area.setAttribute('fill', `url(#${options.gradPrefix}${idx})`);
                svgEl.appendChild(area);

                const line = svg('path');
                const dLine = `M${pts[0][0]},${pts[0][1]} ` + pts.slice(1).map(p => `L${p[0]},${p[1]}`).join(' ');
                line.setAttribute('d', dLine);
                line.setAttribute('fill', 'none');
                line.setAttribute('stroke', s.color);
                line.setAttribute('stroke-width', '3');
                line.setAttribute('stroke-linecap', 'round');
                line.setAttribute('stroke-linejoin', 'round');
                line.setAttribute('opacity', String(s.lineOpacity ?? 1));
                svgEl.appendChild(line);
            });

            // x labels (first/last)
            const lg = svg('g');
            lg.setAttribute('fill', '#64748b');
            lg.setAttribute('font-size', '11');
            lg.setAttribute('font-family', 'Inter, system-ui, sans-serif');
            const first = labels?.[0] ? String(labels[0]).slice(5) : '';
            const last = labels?.[labels.length - 1] ? String(labels[labels.length - 1]).slice(5) : '';
            const t1 = svg('text');
            t1.setAttribute('x', String(padL));
            t1.setAttribute('y', String(H - 6));
            t1.setAttribute('text-anchor', 'start');
            t1.textContent = first;
            const t2 = svg('text');
            t2.setAttribute('x', String(W - padR));
            t2.setAttribute('y', String(H - 6));
            t2.setAttribute('text-anchor', 'end');
            t2.textContent = last;
            lg.appendChild(t1);
            lg.appendChild(t2);
            svgEl.appendChild(lg);
        }

        function renderPatientsChart(chart) {
            if (!elPatientsChart) return;
            const labels = chart?.labels || [];
            const data = (chart?.data || []).map(v => Number(v || 0));
            renderLineAreaChart(elPatientsChart, labels, [{
                data,
                color: '#3b82f6',
                fillOpacityTop: 0.35,
                fillOpacityBottom: 0.02
            }], { width: 360, height: 150, padL: 36, padR: 10, padT: 18, padB: 18, gradPrefix: 'poFillDyn' });
        }

        function renderRevenueChart(chart) {
            if (!elRevenueChart) return;
            const labels = chart?.labels || [];
            renderLineAreaChart(elRevenueChart, labels, [
                { data: chart?.billing || [], color: '#3b82f6', fillOpacityTop: 0.28, fillOpacityBottom: 0.03 },
                { data: chart?.medicines_added || [], color: '#10b981', fillOpacityTop: 0.22, fillOpacityBottom: 0.03, lineOpacity: 0.95 },
                { data: chart?.lab_tests || [], color: '#f59e0b', fillOpacityTop: 0.18, fillOpacityBottom: 0.03, lineOpacity: 0.92 },
            ], { width: 520, height: 190, padL: 40, padR: 10, padT: 20, padB: 22, gradPrefix: 'revFillDyn' });
        }

        function renderAppointmentsChart(chart) {
            if (!elAppointmentsChart) return;
            const labels = chart?.labels || [];
            const values = (chart?.data || []).map(v => Number(v || 0));

            const W = 760, H = 220;
            const padL = 50, padR = 20, padT = 20, padB = 40;
            const innerW = W - padL - padR;
            const innerH = H - padT - padB;

            elAppointmentsChart.innerHTML = '';

            const grid = svg('g');
            grid.setAttribute('opacity', '0.35');
            grid.setAttribute('stroke', '#cbd5e1');
            grid.setAttribute('stroke-width', '1');
            for (let i = 0; i < 5; i++) {
                const y = padT + (innerH * i) / 4;
                const line = svg('line');
                line.setAttribute('x1', String(padL));
                line.setAttribute('x2', String(W - padR));
                line.setAttribute('y1', String(y));
                line.setAttribute('y2', String(y));
                grid.appendChild(line);
            }
            elAppointmentsChart.appendChild(grid);

            const max = Math.max(1, ...values);
            const gap = innerW / Math.max(1, values.length);
            const barW = gap * 0.55;
            const colors = ['#60a5fa', '#34d399', '#fbbf24', '#f87171'];

            const bars = svg('g');
            values.forEach((v, i) => {
                const h = (v / max) * innerH;
                const x = padL + i * gap + (gap - barW) / 2;
                const y = padT + innerH - h;
                const rect = svg('rect');
                rect.setAttribute('x', String(x));
                rect.setAttribute('y', String(y));
                rect.setAttribute('width', String(barW));
                rect.setAttribute('height', String(h));
                rect.setAttribute('rx', '8');
                rect.setAttribute('fill', colors[i % colors.length]);
                rect.setAttribute('opacity', '0.9');
                bars.appendChild(rect);
            });
            elAppointmentsChart.appendChild(bars);

            const lg = svg('g');
            lg.setAttribute('fill', '#64748b');
            lg.setAttribute('font-size', '11');
            lg.setAttribute('font-family', 'Inter, system-ui, sans-serif');
            labels.forEach((l, i) => {
                const t = svg('text');
                t.setAttribute('x', String(padL + i * gap + gap / 2));
                t.setAttribute('y', String(H - 12));
                t.setAttribute('text-anchor', 'middle');
                t.textContent = String(l).replaceAll('_', ' ');
                lg.appendChild(t);
            });
            elAppointmentsChart.appendChild(lg);
        }

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

                if (data.charts) {
                    renderPatientsChart(data.charts.patients_overview);
                    renderRevenueChart(data.charts.revenue_overview);
                    renderAppointmentsChart(data.charts.appointments_overview);
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
