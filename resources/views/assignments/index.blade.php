@extends('layouts.dashboard')
@section('title','Affectations')
@section('page-title','Gestion des Affectations')

@section('content')
@php
$statusMap=['planned'=>['Planifiée','#6366f1','#f0f0ff'],'confirmed'=>['Confirmée','#0891b2','#ecfeff'],'in_progress'=>['En cours','#3b82f6','#eff6ff'],'completed'=>['Terminée','#10b981','#f0fdf4'],'cancelled'=>['Annulée','#64748b','#f8fafc']];
$typeMap=['mission'=>'Mission','daily'=>'Journée','courses'=>'Courses','permanent'=>'Permanente','replacement'=>'Remplacement','trial'=>'Essai'];
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
.btn-warning{background:#fef3c7;color:#b45309;border:1.5px solid #fde68a;}
.btn-warning:hover{background:#fde68a;}
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
    <div class="stat-card"><div class="stat-icon" style="background:#eff6ff;"><svg width="18" height="18" fill="none" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2" stroke="#3b82f6" stroke-width="2"/><path d="M8 2v4M16 2v4M3 10h18" stroke="#3b82f6" stroke-width="2" stroke-linecap="round"/></svg></div><div><div class="stat-val">{{ $stats['total'] }}</div><div class="stat-lbl">Total</div></div></div>
    <div class="stat-card"><div class="stat-icon" style="background:#f0f0ff;"><svg width="18" height="18" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke="#6366f1" stroke-width="2"/><path d="M12 7v5l3 3" stroke="#6366f1" stroke-width="2" stroke-linecap="round"/></svg></div><div><div class="stat-val" style="color:#6366f1;">{{ $stats['planned'] }}</div><div class="stat-lbl">Planifiées</div></div></div>
    <div class="stat-card"><div class="stat-icon" style="background:#eff6ff;"><svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7" stroke="#3b82f6" stroke-width="2" stroke-linecap="round"/></svg></div><div><div class="stat-val" style="color:#3b82f6;">{{ $stats['in_progress'] }}</div><div class="stat-lbl">En cours</div></div></div>
    <div class="stat-card"><div class="stat-icon" style="background:#f0fdf4;"><svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4" stroke="#10b981" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="#10b981" stroke-width="2"/></svg></div><div><div class="stat-val" style="color:#10b981;">{{ $stats['completed'] }}</div><div class="stat-lbl">Terminées</div></div></div>
    <div class="stat-card"><div class="stat-icon" style="background:#f8fafc;"><svg width="18" height="18" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke="#64748b" stroke-width="2"/><path d="M15 9l-6 6M9 9l6 6" stroke="#64748b" stroke-width="2" stroke-linecap="round"/></svg></div><div><div class="stat-val" style="color:#64748b;">{{ $stats['cancelled'] }}</div><div class="stat-lbl">Annulées</div></div></div>
    <div class="stat-card" style="{{ $showArchived ? 'border-color:#f59e0b;' : '' }}"><div class="stat-icon" style="background:#fffbeb;"><svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M5 8h14M5 8a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v.01a2 2 0 01-2 2M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8" stroke="#f59e0b" stroke-width="2" stroke-linecap="round"/></svg></div><div><div class="stat-val" style="color:#f59e0b;">{{ $stats['archived'] }}</div><div class="stat-lbl">Archivées</div></div></div>
</div>

<div class="card">
    <div class="card-head" style="justify-content:space-between;">
        <div style="display:flex;align-items:center;gap:.6rem;">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2" stroke="#10b981" stroke-width="2"/><path d="M8 2v4M16 2v4M3 10h18" stroke="#10b981" stroke-width="2" stroke-linecap="round"/></svg>
            <span class="card-title">{{ $showArchived ? 'Archives des affectations' : 'Liste des affectations' }}</span>
            <span style="background:#f1f5f9;color:#64748b;font-size:.72rem;font-weight:600;padding:.1rem .5rem;border-radius:99px;">{{ $assignments->total() }}</span>
            @if($showArchived)
            <span style="background:#fef3c7;color:#b45309;font-size:.72rem;font-weight:600;padding:.15rem .55rem;border-radius:99px;">Archives</span>
            @endif
        </div>
        @can('assignments.create')
        @if(!$showArchived)
        <a href="{{ route('assignments.create') }}" class="btn btn-primary">
            <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
            Nouvelle affectation
        </a>
        @endif
        @endcan
    </div>

    {{-- Filtres --}}
    <div style="padding:.85rem 1.25rem;border-bottom:1px solid #f1f5f9;background:#fafafa;">
        <form method="GET" style="display:flex;gap:.65rem;flex-wrap:wrap;align-items:flex-end;">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Chauffeur, véhicule, destination…" class="filter-input" style="width:220px;">
            @if(!$showArchived)
            <select name="status" class="filter-input">
                <option value="all">Tous statuts</option>
                @foreach(['planned'=>'Planifiée','confirmed'=>'Confirmée','in_progress'=>'En cours','completed'=>'Terminée','cancelled'=>'Annulée'] as $v=>$l)
                <option value="{{ $v }}" @selected(request('status')===$v)>{{ $l }}</option>
                @endforeach
            </select>
            @endif
            <select name="type" class="filter-input">
                <option value="">Tous types</option>
                @foreach($typeMap as $v=>$l)
                <option value="{{ $v }}" @selected(request('type')===$v)>{{ $l }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="filter-input" title="Du">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="filter-input" title="Au">

            {{-- Toggle archives --}}
            @can('assignments.delete')
            <label style="display:flex;align-items:center;gap:.4rem;font-size:.82rem;color:#64748b;cursor:pointer;padding:.45rem .6rem;border:1.5px solid #e2e8f0;border-radius:.45rem;background:#fff;{{ $showArchived ? 'border-color:#f59e0b;color:#b45309;background:#fffbeb;' : '' }}">
                <input type="checkbox" name="archived" value="1" onchange="this.form.submit()" {{ $showArchived ? 'checked' : '' }} style="width:14px;height:14px;accent-color:#f59e0b;">
                Archives
            </label>
            @endcan

            <button type="submit" class="btn btn-primary" style="padding:.45rem .8rem;">Filtrer</button>
            @if(request()->anyFilled(['q','status','type','date_from','date_to']) || $showArchived)
            <a href="{{ route('assignments.index') }}" class="btn btn-ghost" style="padding:.45rem .8rem;">Réinit.</a>
            @endif
        </form>
    </div>

    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Chauffeur</th>
                    <th>Véhicule</th>
                    <th>Type</th>
                    <th>Départ</th>
                    <th>Retour prévu</th>
                    <th>Km parcourus</th>
                    <th>Statut</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $a)
                @php $s = $statusMap[$a->status] ?? ['—','#64748b','#f8fafc']; @endphp
                <tr class="{{ $showArchived ? 'archived-row' : '' }}">
                    <td style="font-family:monospace;font-size:.78rem;color:#94a3b8;">#{{ $a->id }}</td>
                    <td>
                        @if($a->driver)
                            <div style="font-weight:600;">{{ $a->driver->full_name }}</div>
                            <div style="font-size:.73rem;color:#94a3b8;">{{ $a->driver->matricule }}</div>
                        @elseif($a->collaborator)
                            <div style="font-weight:600;">{{ $a->collaborator->name }}</div>
                            <div style="font-size:.73rem;">
                                <span style="background:#ede9fe;color:#5b21b6;padding:.1rem .35rem;border-radius:99px;font-weight:600;">Collaborateur</span>
                            </div>
                        @else
                            <span style="color:#94a3b8;">—</span>
                        @endif
                    </td>
                    <td>
                        <span style="font-family:monospace;font-size:.82rem;background:#f1f5f9;padding:.15rem .45rem;border-radius:.3rem;">{{ $a->vehicle->plate ?? '—' }}</span>
                        <div style="font-size:.73rem;color:#94a3b8;">{{ $a->vehicle?->brand }} {{ $a->vehicle?->model }}</div>
                    </td>
                    <td><span class="badge" style="background:#f1f5f9;color:#374151;">{{ $typeMap[$a->type] ?? $a->type }}</span></td>
                    <td style="font-size:.8rem;">
                        {{ $a->datetime_start->isoFormat('D MMM YYYY') }}<br>
                        <span style="color:#94a3b8;">{{ $a->datetime_start->format('H:i') }}</span>
                    </td>
                    <td style="font-size:.8rem;">
                        {{ $a->datetime_end_planned->isoFormat('D MMM YYYY') }}<br>
                        <span style="color:#94a3b8;">{{ $a->datetime_end_planned->format('H:i') }}</span>
                    </td>
                    <td style="font-size:.82rem;">
                        @if($a->status === 'completed' && $a->km_total)
                            <span style="font-weight:600;color:#10b981;">{{ number_format($a->km_total) }} km</span>
                        @elseif($a->km_start)
                            <span style="color:#94a3b8;">{{ number_format($a->km_start) }} km (départ)</span>
                        @else —
                        @endif
                    </td>
                    <td>
                        <span class="badge" style="background:{{ $s[2] }};color:{{ $s[1] }};">
                            <span style="width:5px;height:5px;border-radius:50%;background:{{ $s[1] }};display:inline-block;"></span>
                            {{ $s[0] }}
                        </span>
                        @if($showArchived)
                        <div style="font-size:.68rem;color:#f59e0b;margin-top:.2rem;">Archivée le {{ $a->deleted_at->isoFormat('D MMM YYYY') }}</div>
                        @endif
                    </td>
                    <td style="text-align:right;">
                        @if($showArchived)
                            @can('assignments.delete')
                            <div style="display:flex;gap:.4rem;justify-content:flex-end;">
                                <form method="POST" action="{{ route('assignments.restore', $a->id) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-warning" style="padding:.32rem .6rem;" title="Restaurer dans la liste active">
                                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke="currentColor" stroke-width="2"/><polyline points="9 22 9 12 15 12 15 22" stroke="currentColor" stroke-width="2"/></svg>
                                        Restaurer
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('assignments.force-destroy', $a->id) }}" style="display:inline;"
                                      data-confirm="Supprimer définitivement l'affectation #{{ $a->id }} ? Cette action est irréversible."
                                      data-title="Suppression définitive"
                                      data-icon="warning"
                                      data-btn-text="Supprimer"
                                      data-btn-color="#dc2626">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn" style="padding:.32rem .6rem;background:#fee2e2;color:#dc2626;border:1.5px solid #fca5a5;" title="Supprimer définitivement">
                                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M19 6l-1 14H6L5 6M10 11v6M14 11v6M9 6V4h6v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                        Supprimer
                                    </button>
                                </form>
                            </div>
                            @endcan
                        @else
                            <a href="{{ route('assignments.show', $a) }}" class="btn btn-ghost" style="padding:.32rem .6rem;" title="Détail">
                                <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
                            </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" style="text-align:center;padding:2.5rem;color:#94a3b8;">{{ $showArchived ? 'Aucune affectation archivée.' : 'Aucune affectation trouvée.' }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($assignments->hasPages())
    <div style="display:flex;justify-content:space-between;align-items:center;padding:.75rem 1.25rem;font-size:.82rem;color:#64748b;">
        <span>{{ $assignments->firstItem() }}–{{ $assignments->lastItem() }} sur {{ $assignments->total() }}</span>
        {{ $assignments->links() }}
    </div>
    @endif
</div>
@endsection
