@extends('layouts.dashboard')
@section('title', 'Demandes de véhicule')
@section('page-title', 'Demandes de véhicule')

@section('content')
@php
$statusMap = [
    'pending'     => ['En attente','#d97706','#fffbeb'],
    'approved'    => ['Approuvée','#0891b2','#ecfeff'],
    'confirmed'   => ['Confirmée','#6366f1','#f0f0ff'],
    'in_progress' => ['En cours','#3b82f6','#eff6ff'],
    'completed'   => ['Terminée','#10b981','#f0fdf4'],
    'rejected'    => ['Rejetée','#ef4444','#fef2f2'],
    'cancelled'   => ['Annulée','#64748b','#f8fafc'],
];
$typePrefs = ['any'=>'Indifférent','city'=>'Citadine','sedan'=>'Berline','suv'=>'SUV','pickup'=>'Pickup','van'=>'Fourgon','truck'=>'Camion'];
@endphp

<style>
.card{background:#fff;border-radius:.75rem;border:1px solid #e2e8f0;overflow:hidden;margin-bottom:1.25rem;}
.card-head{padding:.85rem 1.25rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:.6rem;}
.card-title{font-size:.9rem;font-weight:700;color:#0f172a;}
.stat-card{background:#fff;border-radius:.75rem;border:1px solid #e2e8f0;padding:1rem 1.25rem;display:flex;align-items:center;gap:.85rem;}
.stat-icon{width:40px;height:40px;border-radius:.55rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.stat-val{font-size:1.4rem;font-weight:800;color:#0f172a;line-height:1;}
.stat-lbl{font-size:.73rem;color:#64748b;margin-top:.15rem;}
.badge{display:inline-flex;align-items:center;gap:.22rem;padding:.18rem .55rem;border-radius:99px;font-size:.7rem;font-weight:600;}
.btn{padding:.45rem .9rem;border-radius:.45rem;font-size:.82rem;font-weight:600;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;}
.btn-primary{background:linear-gradient(135deg,#10b981,#059669);color:#fff;}
.btn-ghost{background:#f8fafc;color:#374151;border:1.5px solid #e2e8f0;}
.btn-ghost:hover{background:#f1f5f9;}
.filter-input{padding:.45rem .75rem;border:1.5px solid #e2e8f0;border-radius:.45rem;font-size:.825rem;background:#fff;color:#0f172a;outline:none;}
.filter-input:focus{border-color:#10b981;}
table{width:100%;border-collapse:collapse;}
th{font-size:.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;padding:.6rem 1rem;border-bottom:1.5px solid #f1f5f9;text-align:left;white-space:nowrap;}
td{padding:.65rem 1rem;border-bottom:1px solid #f8fafc;font-size:.84rem;color:#374151;vertical-align:middle;}
tr:hover td{background:#f8fafc;}
</style>

{{-- Stats --}}
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:1rem;margin-bottom:1.5rem;">
    <div class="stat-card"><div class="stat-icon" style="background:#f8fafc;"><svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4" stroke="#64748b" stroke-width="2" stroke-linecap="round"/><path d="M20 12c0 4.418-3.582 8-8 8s-8-3.582-8-8 3.582-8 8-8 8 3.582 8 8z" stroke="#64748b" stroke-width="2"/></svg></div><div><div class="stat-val">{{ $stats['total'] }}</div><div class="stat-lbl">Total</div></div></div>
    <div class="stat-card"><div class="stat-icon" style="background:#fffbeb;"><svg width="18" height="18" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke="#d97706" stroke-width="2"/><path d="M12 7v5l3 3" stroke="#d97706" stroke-width="2" stroke-linecap="round"/></svg></div><div><div class="stat-val" style="color:#d97706;">{{ $stats['pending'] }}</div><div class="stat-lbl">En attente</div></div></div>
    <div class="stat-card"><div class="stat-icon" style="background:#eff6ff;"><svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7" stroke="#3b82f6" stroke-width="2" stroke-linecap="round"/></svg></div><div><div class="stat-val" style="color:#3b82f6;">{{ $stats['active'] }}</div><div class="stat-lbl">En cours</div></div></div>
    <div class="stat-card"><div class="stat-icon" style="background:#f0fdf4;"><svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4" stroke="#10b981" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="#10b981" stroke-width="2"/></svg></div><div><div class="stat-val" style="color:#10b981;">{{ $stats['completed'] }}</div><div class="stat-lbl">Terminées</div></div></div>
    <div class="stat-card"><div class="stat-icon" style="background:#fff7ed;"><svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M12 9v4M12 17h.01" stroke="#f97316" stroke-width="2" stroke-linecap="round"/><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="#f97316" stroke-width="2"/></svg></div><div><div class="stat-val" style="color:#f97316;">{{ $stats['urgent'] }}</div><div class="stat-lbl">Urgentes</div></div></div>
</div>

<div class="card">
    <div class="card-head" style="justify-content:space-between;">
        <div style="display:flex;align-items:center;gap:.6rem;">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4" stroke="#10b981" stroke-width="2" stroke-linecap="round"/><path d="M20 12c0 4.418-3.582 8-8 8s-8-3.582-8-8 3.582-8 8-8 8 3.582 8 8z" stroke="#10b981" stroke-width="2"/></svg>
            <span class="card-title">Liste des demandes</span>
            <span style="background:#f1f5f9;color:#64748b;font-size:.72rem;font-weight:600;padding:.1rem .5rem;border-radius:99px;">{{ $requests->total() }}</span>
        </div>
        @can('vehicle_requests.create')
        <a href="{{ route('requests.create') }}" class="btn btn-primary">
            <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
            Nouvelle demande
        </a>
        @endcan
    </div>

    {{-- Filtres --}}
    <div style="padding:.85rem 1.25rem;border-bottom:1px solid #f1f5f9;background:#fafafa;">
        <form method="GET" style="display:flex;gap:.65rem;flex-wrap:wrap;align-items:flex-end;">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Demandeur, destination, véhicule…" class="filter-input" style="width:220px;">
            <select name="status" class="filter-input">
                <option value="all">Tous statuts</option>
                @foreach($statusMap as $v => [$l])
                    <option value="{{ $v }}" @selected(request('status')===$v)>{{ $l }}</option>
                @endforeach
            </select>
            <label style="display:flex;align-items:center;gap:.4rem;font-size:.82rem;color:#374151;cursor:pointer;">
                <input type="checkbox" name="urgent_only" value="1" @checked(request()->boolean('urgent_only'))> Urgentes uniquement
            </label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="filter-input" title="À partir du">
            <button type="submit" class="btn btn-primary" style="padding:.45rem .8rem;">Filtrer</button>
            @if(request()->anyFilled(['q','status','urgent_only','date_from']))
                <a href="{{ route('requests.index') }}" class="btn btn-ghost" style="padding:.45rem .8rem;">Réinit.</a>
            @endif
        </form>
    </div>

    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Demandeur</th>
                    <th>Destination</th>
                    <th>Véhicule attribué</th>
                    <th>Départ</th>
                    <th>Retour prévu</th>
                    <th>Passagers</th>
                    <th>Statut</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $r)
                @php $s = $statusMap[$r->status] ?? ['—','#64748b','#f8fafc']; @endphp
                <tr>
                    <td style="font-family:monospace;font-size:.78rem;color:#94a3b8;">
                        #{{ $r->id }}
                        @if($r->is_urgent)
                            <span title="Urgente" style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#ef4444;margin-left:.25rem;vertical-align:middle;"></span>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight:600;">{{ $r->requester?->name ?? '—' }}</div>
                        <div style="font-size:.73rem;color:#94a3b8;">{{ $r->requester?->email ?? '' }}</div>
                    </td>
                    <td>
                        <div style="font-weight:500;">{{ Str::limit($r->destination, 30) }}</div>
                        <div style="font-size:.73rem;color:#94a3b8;">{{ Str::limit($r->purpose, 30) }}</div>
                    </td>
                    <td>
                        @if($r->vehicle)
                            <span style="font-family:monospace;font-size:.82rem;background:#f1f5f9;padding:.15rem .45rem;border-radius:.3rem;">{{ $r->vehicle->plate }}</span>
                            <div style="font-size:.73rem;color:#94a3b8;">{{ $r->vehicle->brand }} {{ $r->vehicle->model }}</div>
                        @else
                            <span style="color:#94a3b8;font-size:.8rem;">Non attribué</span>
                        @endif
                    </td>
                    <td style="font-size:.8rem;">
                        {{ $r->datetime_start->isoFormat('D MMM YYYY') }}<br>
                        <span style="color:#94a3b8;">{{ $r->datetime_start->format('H:i') }}</span>
                    </td>
                    <td style="font-size:.8rem;">
                        {{ $r->datetime_end_planned->isoFormat('D MMM YYYY') }}<br>
                        <span style="color:#94a3b8;">{{ $r->datetime_end_planned->format('H:i') }}</span>
                    </td>
                    <td style="font-size:.82rem;text-align:center;">{{ $r->passengers }}</td>
                    <td>
                        <span class="badge" style="background:{{ $s[2] }};color:{{ $s[1] }};">
                            <span style="width:5px;height:5px;border-radius:50%;background:{{ $s[1] }};display:inline-block;"></span>
                            {{ $s[0] }}
                        </span>
                    </td>
                    <td style="text-align:right;">
                        <a href="{{ route('requests.show', $r) }}" class="btn btn-ghost" style="padding:.32rem .6rem;" title="Détail">
                            <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" style="text-align:center;padding:2.5rem;color:#94a3b8;">Aucune demande trouvée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($requests->hasPages())
    <div style="display:flex;justify-content:space-between;align-items:center;padding:.75rem 1.25rem;font-size:.82rem;color:#64748b;">
        <span>{{ $requests->firstItem() }}–{{ $requests->lastItem() }} sur {{ $requests->total() }}</span>
        {{ $requests->links() }}
    </div>
    @endif
</div>
@endsection
