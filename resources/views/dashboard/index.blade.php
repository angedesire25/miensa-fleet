@extends('layouts.dashboard')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')

@section('content')

<style>
/* ── Stat cards ──────────────────────────────────────────────────────────── */
.stat-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 1rem; margin-bottom: 1.5rem; }
.stat-card {
    background: #fff; border-radius: .75rem; padding: 1.25rem 1.5rem;
    border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,.04);
}
.stat-icon {
    width: 48px; height: 48px; border-radius: .6rem;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.stat-value { font-size: 1.75rem; font-weight: 700; color: #0f172a; line-height: 1; }
.stat-label { font-size: .8rem; color: #64748b; margin-top: .2rem; }
.stat-sub { font-size: .75rem; color: #94a3b8; margin-top: .15rem; }

/* ── Section card ────────────────────────────────────────────────────────── */
.section-card {
    background: #fff; border-radius: .75rem; border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0,0,0,.04); overflow: hidden;
}
.section-header {
    padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9;
    display: flex; align-items: center; justify-content: space-between;
}
.section-title { font-size: .9rem; font-weight: 600; color: #0f172a; }
.section-link { font-size: .8rem; color: #10b981; text-decoration: none; font-weight: 500; }

/* ── Tables ──────────────────────────────────────────────────────────────── */
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { padding: .65rem 1.25rem; text-align: left; font-size: .73rem; font-weight: 600; color: #94a3b8; letter-spacing: .06em; text-transform: uppercase; background: #f8fafc; border-bottom: 1px solid #f1f5f9; }
.data-table td { padding: .8rem 1.25rem; font-size: .85rem; color: #374151; border-bottom: 1px solid #f8fafc; vertical-align: middle; }
.data-table tr:last-child td { border-bottom: none; }
.data-table tr:hover td { background: #fafafa; }

/* ── Badges ──────────────────────────────────────────────────────────────── */
.badge { display: inline-flex; align-items: center; gap: .3rem; padding: .2rem .6rem; border-radius: 99px; font-size: .72rem; font-weight: 600; }
.badge-critical { background: #fef2f2; color: #dc2626; }
.badge-warning  { background: #fffbeb; color: #d97706; }
.badge-info     { background: #eff6ff; color: #2563eb; }
.badge-green    { background: #f0fdf4; color: #16a34a; }
.badge-gray     { background: #f8fafc; color: #64748b; }

/* ── Fleet status doughnut placeholder ───────────────────────────────────── */
.fleet-ring-wrap { display: flex; align-items: center; gap: 2rem; padding: 1.25rem; }
.fleet-ring { position: relative; width: 130px; height: 130px; flex-shrink: 0; }
.fleet-ring svg { transform: rotate(-90deg); }
.ring-center { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }
.ring-center-num { font-size: 1.5rem; font-weight: 700; color: #0f172a; }
.ring-center-lbl { font-size: .7rem; color: #94a3b8; }
.legend-item { display: flex; align-items: center; gap: .5rem; margin-bottom: .45rem; }
.legend-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.legend-name { font-size: .8rem; color: #374151; flex: 1; }
.legend-val { font-size: .8rem; font-weight: 600; color: #0f172a; }
</style>

{{-- ── En-tête page ─────────────────────────────────────────────────────── --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
    <div>
        <h1 style="font-size:1.35rem;font-weight:700;color:#0f172a;margin:0;">Tableau de bord</h1>
        <p style="color:#64748b;font-size:.85rem;margin:.15rem 0 0;">{{ ucfirst(now()->isoFormat('dddd D MMMM YYYY')) }} · Bienvenue, {{ auth()->user()->name }}</p>
    </div>
    @if($stats['alerts_critical'] > 0)
        <div style="display:flex;align-items:center;gap:.5rem;background:#fef2f2;border:1px solid #fecaca;padding:.55rem 1rem;border-radius:.5rem;">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="#ef4444"/><path d="M12 8v4m0 4h.01" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
            <span style="color:#dc2626;font-size:.82rem;font-weight:600;">{{ $stats['alerts_critical'] }} alerte(s) critique(s) à traiter</span>
        </div>
    @endif
</div>

{{-- ── Statistiques principales ─────────────────────────────────────────── --}}
<div class="stat-grid">

    {{-- Véhicules disponibles --}}
    <div class="stat-card">
        <div class="stat-icon" style="background:#f0fdf4;">
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24"><path d="M3 17h2l1-3h12l1 3h2" stroke="#16a34a" stroke-width="2" stroke-linecap="round"/><circle cx="7.5" cy="18.5" r="1.5" stroke="#16a34a" stroke-width="1.8"/><circle cx="16.5" cy="18.5" r="1.5" stroke="#16a34a" stroke-width="1.8"/><path d="M6.5 9l1-3h9l1 3" stroke="#16a34a" stroke-width="1.5" fill="none"/></svg>
        </div>
        <div>
            <div class="stat-value">{{ $stats['vehicles_available'] }}</div>
            <div class="stat-label">Véhicules disponibles</div>
            <div class="stat-sub">{{ $stats['vehicles_total'] }} au total · {{ $stats['vehicles_mission'] }} en mission</div>
        </div>
    </div>

    {{-- Chauffeurs actifs --}}
    <div class="stat-card">
        <div class="stat-icon" style="background:#eff6ff;">
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4" stroke="#2563eb" stroke-width="1.8"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke="#2563eb" stroke-width="1.8" stroke-linecap="round"/></svg>
        </div>
        <div>
            <div class="stat-value">{{ $stats['drivers_active'] }}</div>
            <div class="stat-label">Chauffeurs actifs</div>
            <div class="stat-sub">{{ $stats['assignments_active'] }} affectation(s) en cours</div>
        </div>
    </div>

    {{-- Alertes --}}
    <div class="stat-card">
        <div class="stat-icon" style="background:{{ $stats['alerts_critical'] > 0 ? '#fef2f2' : '#fffbeb' }};">
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="{{ $stats['alerts_critical'] > 0 ? '#dc2626' : '#d97706' }}" stroke-width="1.8"/><path d="M13.73 21a2 2 0 01-3.46 0" stroke="{{ $stats['alerts_critical'] > 0 ? '#dc2626' : '#d97706' }}" stroke-width="1.8"/></svg>
        </div>
        <div>
            <div class="stat-value" style="color:{{ $stats['alerts_critical'] > 0 ? '#dc2626' : '#0f172a' }};">{{ $stats['alerts_new'] }}</div>
            <div class="stat-label">Alertes non traitées</div>
            <div class="stat-sub">{{ $stats['alerts_critical'] }} critique(s)</div>
        </div>
    </div>

    {{-- Maintenance --}}
    <div class="stat-card">
        <div class="stat-icon" style="background:#faf5ff;">
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.77 3.77z" stroke="#7c3aed" stroke-width="1.8"/></svg>
        </div>
        <div>
            <div class="stat-value">{{ $stats['vehicles_maintenance'] }}</div>
            <div class="stat-label">En maintenance</div>
            <div class="stat-sub">{{ $stats['repairs_in_progress'] }} réparation(s) · {{ $stats['incidents_open'] }} sinistre(s)</div>
        </div>
    </div>

</div>

{{-- ── Ligne principale ─────────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:1fr 320px;gap:1rem;margin-bottom:1rem;">

    {{-- Alertes récentes --}}
    <div class="section-card">
        <div class="section-header">
            <span class="section-title">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:.3rem;"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="#f59e0b" stroke-width="2"/></svg>
                Alertes récentes
            </span>
            <a href="#" class="section-link">Voir tout →</a>
        </div>
        @if($recentAlerts->isEmpty())
            <div style="padding:2rem;text-align:center;color:#94a3b8;font-size:.875rem;">Aucune alerte active</div>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Sévérité</th>
                        <th>Type</th>
                        <th>Message</th>
                        <th>Véhicule</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentAlerts as $alert)
                        <tr>
                            <td>
                                @if($alert->severity === 'critical')
                                    <span class="badge badge-critical">
                                        <span style="width:6px;height:6px;border-radius:50%;background:#dc2626;display:inline-block;"></span>
                                        Critique
                                    </span>
                                @elseif($alert->severity === 'warning')
                                    <span class="badge badge-warning">
                                        <span style="width:6px;height:6px;border-radius:50%;background:#d97706;display:inline-block;"></span>
                                        Avertissement
                                    </span>
                                @else
                                    <span class="badge badge-info">Info</span>
                                @endif
                            </td>
                            <td style="color:#64748b;font-size:.78rem;">{{ str_replace('_', ' ', $alert->type) }}</td>
                            <td style="max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $alert->message }}">
                                {{ $alert->title }}
                            </td>
                            <td style="font-weight:600;">{{ $alert->vehicle?->plate ?? '—' }}</td>
                            <td style="color:#94a3b8;font-size:.78rem;">{{ $alert->created_at->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Statut de la flotte --}}
    <div class="section-card">
        <div class="section-header">
            <span class="section-title">État de la flotte</span>
        </div>
        @php
            $total = max($stats['vehicles_total'], 1);
            $available   = $stats['vehicles_available'];
            $mission     = $stats['vehicles_mission'];
            $maintenance = $stats['vehicles_maintenance'];
            $other       = max(0, $total - $available - $mission - $maintenance);

            // SVG doughnut (rayon 50, circumference ≈ 314.16)
            $r = 50; $c = 2 * pi() * $r;
            $pAvail = $available / $total * $c;
            $pMiss  = $mission  / $total * $c;
            $pMaint = $maintenance / $total * $c;
            $pOther = $other    / $total * $c;
        @endphp
        <div class="fleet-ring-wrap">
            <div class="fleet-ring">
                <svg width="130" height="130" viewBox="0 0 120 120">
                    <circle cx="60" cy="60" r="{{ $r }}" fill="none" stroke="#f1f5f9" stroke-width="14"/>
                    {{-- Available --}}
                    <circle cx="60" cy="60" r="{{ $r }}" fill="none" stroke="#10b981" stroke-width="14"
                        stroke-dasharray="{{ $pAvail }} {{ $c - $pAvail }}"
                        stroke-dashoffset="0"/>
                    {{-- Mission --}}
                    <circle cx="60" cy="60" r="{{ $r }}" fill="none" stroke="#3b82f6" stroke-width="14"
                        stroke-dasharray="{{ $pMiss }} {{ $c - $pMiss }}"
                        stroke-dashoffset="{{ -$pAvail }}"/>
                    {{-- Maintenance --}}
                    <circle cx="60" cy="60" r="{{ $r }}" fill="none" stroke="#f59e0b" stroke-width="14"
                        stroke-dasharray="{{ $pMaint }} {{ $c - $pMaint }}"
                        stroke-dashoffset="{{ -($pAvail + $pMiss) }}"/>
                </svg>
                <div class="ring-center">
                    <span class="ring-center-num">{{ $stats['vehicles_total'] }}</span>
                    <span class="ring-center-lbl">véhicules</span>
                </div>
            </div>
            <div style="flex:1;">
                <div class="legend-item">
                    <div class="legend-dot" style="background:#10b981;"></div>
                    <span class="legend-name">Disponibles</span>
                    <span class="legend-val">{{ $available }}</span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot" style="background:#3b82f6;"></div>
                    <span class="legend-name">En mission</span>
                    <span class="legend-val">{{ $mission }}</span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot" style="background:#f59e0b;"></div>
                    <span class="legend-name">Maintenance</span>
                    <span class="legend-val">{{ $maintenance }}</span>
                </div>
                @if($other > 0)
                    <div class="legend-item">
                        <div class="legend-dot" style="background:#e2e8f0;"></div>
                        <span class="legend-name">Autres</span>
                        <span class="legend-val">{{ $other }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ── Ligne secondaire ─────────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">

    {{-- Affectations en cours --}}
    <div class="section-card">
        <div class="section-header">
            <span class="section-title">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:.3rem;"><rect x="3" y="4" width="18" height="16" rx="2" stroke="#3b82f6" stroke-width="2"/><path d="M8 2v4M16 2v4M3 10h18" stroke="#3b82f6" stroke-width="2" stroke-linecap="round"/></svg>
                Affectations en cours
            </span>
            <a href="#" class="section-link">Tout voir →</a>
        </div>
        @if($activeAssignments->isEmpty())
            <div style="padding:2rem;text-align:center;color:#94a3b8;font-size:.875rem;">Aucune affectation active</div>
        @else
            <table class="data-table">
                <thead><tr><th>Véhicule</th><th>Chauffeur</th><th>Retour prévu</th><th>Statut</th></tr></thead>
                <tbody>
                    @foreach($activeAssignments as $a)
                        <tr>
                            <td style="font-weight:600;">{{ $a->vehicle?->plate ?? '—' }}</td>
                            <td>{{ $a->driver?->full_name ?? '—' }}</td>
                            <td style="color:#64748b;font-size:.8rem;">{{ $a->datetime_end_planned?->format('d/m H:i') ?? '—' }}</td>
                            <td>
                                @if($a->status === 'in_progress')
                                    <span class="badge badge-green">En route</span>
                                @else
                                    <span class="badge badge-info">Confirmée</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Réparations en cours --}}
    <div class="section-card">
        <div class="section-header">
            <span class="section-title">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:.3rem;"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.77 3.77z" stroke="#7c3aed" stroke-width="2"/></svg>
                Réparations en cours
            </span>
            <a href="#" class="section-link">Tout voir →</a>
        </div>
        @if($ongoingRepairs->isEmpty())
            <div style="padding:2rem;text-align:center;color:#94a3b8;font-size:.875rem;">Aucune réparation en cours</div>
        @else
            <table class="data-table">
                <thead><tr><th>Véhicule</th><th>Garage</th><th>Depuis</th><th>Statut</th></tr></thead>
                <tbody>
                    @foreach($ongoingRepairs as $r)
                        @php
                            $days = (int) now()->diffInDays($r->datetime_sent);
                            $overdue = $days > 7;
                        @endphp
                        <tr>
                            <td style="font-weight:600;">{{ $r->vehicle?->plate ?? '—' }}</td>
                            <td style="font-size:.8rem;color:#64748b;">{{ $r->garage?->name ?? 'N/A' }}</td>
                            <td>
                                <span style="font-size:.8rem;color:{{ $overdue ? '#dc2626' : '#64748b' }};font-weight:{{ $overdue ? '600' : '400' }};">
                                    {{ $days }}j {{ $overdue ? '⚠' : '' }}
                                </span>
                            </td>
                            <td>
                                @php $statusLabels = ['sent'=>'Envoyé','diagnosing'=>'Diagnostic','repairing'=>'En réparation','waiting_parts'=>'Attente pièces']; @endphp
                                <span class="badge badge-warning">{{ $statusLabels[$r->status] ?? $r->status }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

</div>

@endsection
