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
$isAdmin = auth()->user()->hasAnyRole(['super_admin','admin']);
$isManager = auth()->user()->hasAnyRole(['super_admin','admin','fleet_manager','controller','director']);
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
.btn-primary:hover{opacity:.9;}
.btn-ghost{background:#f8fafc;color:#374151;border:1.5px solid #e2e8f0;}
.btn-ghost:hover{background:#f1f5f9;}
.btn-warning{background:#fef3c7;color:#b45309;border:1.5px solid #fde68a;}
.btn-warning:hover{background:#fde68a;}
.btn-danger{background:#fee2e2;color:#dc2626;border:1.5px solid #fca5a5;}
.btn-danger:hover{background:#fecaca;}
.filter-input{padding:.45rem .75rem;border:1.5px solid #e2e8f0;border-radius:.45rem;font-size:.825rem;background:#fff;color:#0f172a;outline:none;}
.filter-input:focus{border-color:#10b981;}
table{width:100%;border-collapse:collapse;}
th{font-size:.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;padding:.6rem 1rem;border-bottom:1.5px solid #f1f5f9;text-align:left;white-space:nowrap;}
td{padding:.65rem 1rem;border-bottom:1px solid #f8fafc;font-size:.84rem;color:#374151;vertical-align:middle;}
tr:hover td{background:#f8fafc;}
tr.archived-row td{opacity:.65;}
</style>

{{-- Stats --}}
<div style="display:grid;grid-template-columns:repeat(6,1fr);gap:1rem;margin-bottom:1.5rem;">
    <div class="stat-card">
        <div class="stat-icon" style="background:#f8fafc;"><svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4" stroke="#64748b" stroke-width="2" stroke-linecap="round"/><path d="M20 12c0 4.418-3.582 8-8 8s-8-3.582-8-8 3.582-8 8-8 8 3.582 8 8z" stroke="#64748b" stroke-width="2"/></svg></div>
        <div><div class="stat-val">{{ $stats['total'] }}</div><div class="stat-lbl">Total</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fffbeb;"><svg width="18" height="18" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke="#d97706" stroke-width="2"/><path d="M12 7v5l3 3" stroke="#d97706" stroke-width="2" stroke-linecap="round"/></svg></div>
        <div><div class="stat-val" style="color:#d97706;">{{ $stats['pending'] }}</div><div class="stat-lbl">En attente</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#eff6ff;"><svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7" stroke="#3b82f6" stroke-width="2" stroke-linecap="round"/></svg></div>
        <div><div class="stat-val" style="color:#3b82f6;">{{ $stats['active'] }}</div><div class="stat-lbl">En cours</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#f0fdf4;"><svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4" stroke="#10b981" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="#10b981" stroke-width="2"/></svg></div>
        <div><div class="stat-val" style="color:#10b981;">{{ $stats['completed'] }}</div><div class="stat-lbl">Terminées</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fff7ed;"><svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M12 9v4M12 17h.01" stroke="#f97316" stroke-width="2" stroke-linecap="round"/><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="#f97316" stroke-width="2"/></svg></div>
        <div><div class="stat-val" style="color:#f97316;">{{ $stats['urgent'] }}</div><div class="stat-lbl">Urgentes</div></div>
    </div>
    @if($isManager)
    <div class="stat-card" style="{{ $showArchived ? 'border-color:#f59e0b;' : '' }}">
        <div class="stat-icon" style="background:#fffbeb;"><svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M5 8h14M5 8a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v.01a2 2 0 01-2 2M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8" stroke="#f59e0b" stroke-width="2" stroke-linecap="round"/></svg></div>
        <div><div class="stat-val" style="color:#f59e0b;">{{ $stats['archived'] }}</div><div class="stat-lbl">Archivées</div></div>
    </div>
    @endif
</div>

<div class="card">
    <div class="card-head" style="justify-content:space-between;">
        <div style="display:flex;align-items:center;gap:.6rem;">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4" stroke="#10b981" stroke-width="2" stroke-linecap="round"/><path d="M20 12c0 4.418-3.582 8-8 8s-8-3.582-8-8 3.582-8 8-8 8 3.582 8 8z" stroke="#10b981" stroke-width="2"/></svg>
            <span class="card-title">{{ $showArchived ? 'Archives des demandes' : 'Liste des demandes' }}</span>
            <span style="background:#f1f5f9;color:#64748b;font-size:.72rem;font-weight:600;padding:.1rem .5rem;border-radius:99px;">{{ $requests->total() }}</span>
            @if($showArchived)
            <span style="background:#fef3c7;color:#b45309;font-size:.72rem;font-weight:600;padding:.15rem .55rem;border-radius:99px;">Archives</span>
            @endif
        </div>
        @if(!$showArchived)
        @can('vehicle_requests.create')
        <a href="{{ route('requests.create') }}" class="btn btn-primary">
            <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
            Nouvelle demande
        </a>
        @endcan
        @endif
    </div>

    {{-- Filtres --}}
    <div style="padding:.85rem 1.25rem;border-bottom:1px solid #f1f5f9;background:#fafafa;">
        <form method="GET" style="display:flex;gap:.65rem;flex-wrap:wrap;align-items:flex-end;">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Demandeur, destination, véhicule…" class="filter-input" style="width:220px;">
            @if(!$showArchived)
            <select name="status" class="filter-input">
                <option value="all">Tous statuts</option>
                @foreach($statusMap as $v => [$l])
                    <option value="{{ $v }}" @selected(request('status')===$v)>{{ $l }}</option>
                @endforeach
            </select>
            <label style="display:flex;align-items:center;gap:.4rem;font-size:.82rem;color:#374151;cursor:pointer;">
                <input type="checkbox" name="urgent_only" value="1" @checked(request()->boolean('urgent_only'))> Urgentes uniquement
            </label>
            @endif
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="filter-input" title="À partir du">

            {{-- Toggle archives (managers seulement) --}}
            @if($isManager)
            <label style="display:flex;align-items:center;gap:.4rem;font-size:.82rem;color:#64748b;cursor:pointer;padding:.45rem .6rem;border:1.5px solid #e2e8f0;border-radius:.45rem;background:#fff;{{ $showArchived ? 'border-color:#f59e0b;color:#b45309;background:#fffbeb;' : '' }}">
                <input type="checkbox" name="archived" value="1" onchange="this.form.submit()" {{ $showArchived ? 'checked' : '' }} style="width:14px;height:14px;accent-color:#f59e0b;">
                Archives
            </label>
            @endif

            <button type="submit" class="btn btn-primary" style="padding:.45rem .8rem;">Filtrer</button>
            @if(request()->anyFilled(['q','status','urgent_only','date_from']) || $showArchived)
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
                <tr class="{{ $showArchived ? 'archived-row' : '' }}">
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
                        @if($showArchived)
                        <div style="font-size:.68rem;color:#f59e0b;margin-top:.2rem;">Archivée le {{ $r->deleted_at->isoFormat('D MMM YYYY') }}</div>
                        @endif
                    </td>
                    <td style="text-align:right;">
                        @if($showArchived)
                            @if($isAdmin)
                            <div style="display:flex;gap:.4rem;justify-content:flex-end;">
                                <form method="POST" action="{{ route('requests.restore', $r->id) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-warning" style="padding:.32rem .6rem;font-size:.78rem;" title="Restaurer">
                                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke="currentColor" stroke-width="2"/><polyline points="9 22 9 12 15 12 15 22" stroke="currentColor" stroke-width="2"/></svg>
                                        Restaurer
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('requests.force-destroy', $r->id) }}" style="display:inline;"
                                      data-confirm="Supprimer définitivement la demande #{{ $r->id }} ? Cette action est irréversible."
                                      data-title="Suppression définitive"
                                      data-btn-text="Supprimer"
                                      data-btn-color="#dc2626">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" style="padding:.32rem .6rem;font-size:.78rem;" title="Supprimer définitivement">
                                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M19 6l-1 14H6L5 6M10 11v6M14 11v6M9 6V4h6v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                        Supprimer
                                    </button>
                                </form>
                            </div>
                            @endif
                        @else
                            <div style="display:flex;gap:.4rem;justify-content:flex-end;">
                                <a href="{{ route('requests.show', $r) }}" class="btn btn-ghost" style="padding:.32rem .6rem;" title="Détail">
                                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
                                </a>
                                @if($isAdmin && in_array($r->status, ['completed','rejected','cancelled']))
                                <form method="POST" action="{{ route('requests.destroy', $r) }}" style="display:inline;"
                                      data-confirm="Archiver la demande #{{ $r->id }} ?"
                                      data-title="Archiver la demande"
                                      data-btn-text="Archiver"
                                      data-btn-color="#64748b">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-ghost" style="padding:.32rem .6rem;font-size:.78rem;color:#64748b;" title="Archiver">
                                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24"><path d="M21 8v13H3V8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M1 3h22v5H1z" stroke="currentColor" stroke-width="2"/><path d="M10 12h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" style="text-align:center;padding:2.5rem;color:#94a3b8;">{{ $showArchived ? 'Aucune demande archivée.' : 'Aucune demande trouvée.' }}</td></tr>
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
