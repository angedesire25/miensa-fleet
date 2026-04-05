@extends('layouts.dashboard')
@section('title', 'Fiches de contrôle')
@section('page-title', 'Contrôles véhicules')

@section('content')
@php
$typeMap   = ['departure'=>['Départ','#3b82f6','#eff6ff'],'return'=>['Retour','#10b981','#f0fdf4'],'routine'=>['Routine','#6366f1','#f0f0ff']];
$statusMap = ['draft'=>['Brouillon','#94a3b8','#f8fafc'],'submitted'=>['Soumise','#d97706','#fffbeb'],'validated'=>['Validée','#10b981','#f0fdf4'],'rejected'=>['À corriger','#ef4444','#fef2f2']];
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
.dot{width:7px;height:7px;border-radius:50%;display:inline-block;}
.fuel-bar{width:60px;height:7px;background:#f1f5f9;border-radius:99px;overflow:hidden;display:inline-block;vertical-align:middle;}
.fuel-fill{height:100%;border-radius:99px;}
</style>

{{-- Stats --}}
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:1rem;margin-bottom:1.5rem;">
    <div class="stat-card"><div class="stat-icon" style="background:#f1f5f9;"><svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke="#64748b" stroke-width="2" stroke-linecap="round"/><rect x="9" y="3" width="6" height="4" rx="1" stroke="#64748b" stroke-width="2"/><path d="M9 12l2 2 4-4" stroke="#64748b" stroke-width="2" stroke-linecap="round"/></svg></div><div><div class="stat-val">{{ $stats['total'] }}</div><div class="stat-lbl">Total actives</div></div></div>
    <div class="stat-card"><div class="stat-icon" style="background:#eff6ff;"><svg width="18" height="18" fill="none" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2" stroke="#3b82f6" stroke-width="2"/><path d="M8 2v4M16 2v4M3 10h18" stroke="#3b82f6" stroke-width="2" stroke-linecap="round"/></svg></div><div><div class="stat-val" style="color:#3b82f6;">{{ $stats['today'] }}</div><div class="stat-lbl">Aujourd'hui</div></div></div>
    <div class="stat-card"><div class="stat-icon" style="background:#fffbeb;"><svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M12 9v4M12 17h.01" stroke="#d97706" stroke-width="2" stroke-linecap="round"/><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="#d97706" stroke-width="2"/></svg></div><div><div class="stat-val" style="color:#d97706;">{{ $stats['submitted'] }}</div><div class="stat-lbl">À valider</div></div></div>
    <div class="stat-card"><div class="stat-icon" style="background:#fef2f2;"><svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="#ef4444" stroke-width="2"/><path d="M12 9v4M12 17h.01" stroke="#ef4444" stroke-width="2" stroke-linecap="round"/></svg></div><div><div class="stat-val" style="color:#ef4444;">{{ $stats['critical'] }}</div><div class="stat-lbl">Critiques (7j)</div></div></div>
    {{-- Carte archives : cliquable pour voir les fiches archivées --}}
    <a href="{{ route('inspections.index', ['show_archived'=>1]) }}" class="stat-card" style="text-decoration:none;cursor:pointer;transition:border-color .15s;" onmouseover="this.style.borderColor='#64748b'" onmouseout="this.style.borderColor='#e2e8f0'">
        <div class="stat-icon" style="background:#f8fafc;">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M21 8v13H3V8M1 3h22v5H1zM10 12h4" stroke="#64748b" stroke-width="2" stroke-linecap="round"/></svg>
        </div>
        <div>
            <div class="stat-val" style="color:#64748b;">{{ $stats['archived'] }}</div>
            <div class="stat-lbl">Archivées</div>
        </div>
    </a>
</div>

{{-- Bandeau si on affiche les archives --}}
@if(request()->boolean('show_archived'))
<div style="padding:.7rem 1rem;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:.65rem;margin-bottom:1rem;display:flex;align-items:center;justify-content:space-between;font-size:.83rem;color:#64748b;">
    <div style="display:flex;align-items:center;gap:.5rem;">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M21 8v13H3V8M1 3h22v5H1zM10 12h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        Affichage des fiches <strong>archivées</strong>
    </div>
    <a href="{{ route('inspections.index') }}" style="color:#10b981;font-weight:600;text-decoration:none;">← Revenir aux fiches actives</a>
</div>
@endif

<div class="card">
    <div class="card-head" style="justify-content:space-between;">
        <div style="display:flex;align-items:center;gap:.6rem;">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke="#10b981" stroke-width="2" stroke-linecap="round"/><rect x="9" y="3" width="6" height="4" rx="1" stroke="#10b981" stroke-width="2"/><path d="M9 12l2 2 4-4" stroke="#10b981" stroke-width="2" stroke-linecap="round"/></svg>
            <span class="card-title">Fiches de contrôle</span>
            <span style="background:#f1f5f9;color:#64748b;font-size:.72rem;font-weight:600;padding:.1rem .5rem;border-radius:99px;">{{ $inspections->total() }}</span>
        </div>
        @can('inspections.create')
        <a href="{{ route('inspections.create') }}" class="btn btn-primary">
            <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
            Nouvelle fiche
        </a>
        @endcan
    </div>

    {{-- Filtres --}}
    <div style="padding:.85rem 1.25rem;border-bottom:1px solid #f1f5f9;background:#fafafa;">
        <form method="GET" style="display:flex;gap:.65rem;flex-wrap:wrap;align-items:flex-end;">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Plaque, marque, lieu…" class="filter-input" style="width:200px;">
            <select name="vehicle_id" class="filter-input">
                <option value="">Tous véhicules</option>
                @foreach($vehicles as $v)
                    <option value="{{ $v->id }}" @selected(request('vehicle_id') == $v->id)>{{ $v->plate }} — {{ $v->brand }} {{ $v->model }}</option>
                @endforeach
            </select>
            <select name="type" class="filter-input">
                <option value="">Tous types</option>
                @foreach($typeMap as $val=>[$lbl])
                    <option value="{{ $val }}" @selected(request('type')===$val)>{{ $lbl }}</option>
                @endforeach
            </select>
            <select name="status" class="filter-input">
                <option value="">Tous statuts</option>
                @foreach($statusMap as $val=>[$lbl])
                    <option value="{{ $val }}" @selected(request('status')===$val)>{{ $lbl }}</option>
                @endforeach
            </select>
            <label style="display:flex;align-items:center;gap:.4rem;font-size:.82rem;color:#374151;cursor:pointer;">
                <input type="checkbox" name="critical_only" value="1" @checked(request()->boolean('critical_only'))> Critiques uniquement
            </label>
            <label style="display:flex;align-items:center;gap:.4rem;font-size:.82rem;color:#64748b;cursor:pointer;">
                <input type="checkbox" name="show_archived" value="1" @checked(request()->boolean('show_archived'))> Archives
            </label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="filter-input" title="Du">
            <input type="date" name="date_to"   value="{{ request('date_to') }}"   class="filter-input" title="Au">
            <button type="submit" class="btn btn-primary" style="padding:.45rem .8rem;">Filtrer</button>
            @if(request()->anyFilled(['q','vehicle_id','type','status','critical_only','show_archived','date_from','date_to']))
                <a href="{{ route('inspections.index') }}" class="btn btn-ghost" style="padding:.45rem .8rem;">Réinit.</a>
            @endif
        </form>
    </div>

    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Véhicule</th>
                    <th>Type</th>
                    <th>Date & Heure</th>
                    <th>Km</th>
                    <th>Carburant</th>
                    <th>Points clés</th>
                    <th>Inspecteur</th>
                    <th>Statut</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inspections as $i)
                @php
                    $t  = $typeMap[$i->inspection_type]   ?? ['—','#64748b','#f8fafc'];
                    $st = $statusMap[$i->status]           ?? ['—','#94a3b8','#f8fafc'];
                    $fuelColor = $i->fuel_level_pct >= 50 ? '#10b981' : ($i->fuel_level_pct >= 25 ? '#d97706' : '#ef4444');
                @endphp
                <tr>
                    <td style="font-family:monospace;font-size:.78rem;color:#94a3b8;">#{{ $i->id }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:.55rem;">
                            @if($i->vehicle?->profilePhoto)
                                <img src="{{ Storage::url($i->vehicle->profilePhoto->path) }}"
                                     style="width:36px;height:26px;border-radius:.3rem;object-fit:cover;flex-shrink:0;" alt="">
                            @else
                                <div style="width:36px;height:26px;background:#f1f5f9;border-radius:.3rem;flex-shrink:0;"></div>
                            @endif
                            <div>
                                <div style="font-family:monospace;font-size:.82rem;font-weight:700;">{{ $i->vehicle?->plate ?? '—' }}</div>
                                <div style="font-size:.73rem;color:#94a3b8;">{{ $i->vehicle?->brand }} {{ $i->vehicle?->model }}</div>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge" style="background:{{ $t[2] }};color:{{ $t[1] }};">{{ $t[0] }}</span></td>
                    <td style="font-size:.8rem;">
                        {{ $i->inspected_at->isoFormat('D MMM YYYY') }}<br>
                        <span style="color:#94a3b8;">{{ $i->inspected_at->format('H:i') }}</span>
                    </td>
                    <td style="font-size:.82rem;">{{ $i->km ? number_format($i->km) . ' km' : '—' }}</td>
                    <td>
                        @if(!is_null($i->fuel_level_pct))
                            <div style="display:flex;align-items:center;gap:.4rem;">
                                <div class="fuel-bar"><div class="fuel-fill" style="width:{{ $i->fuel_level_pct }}%;background:{{ $fuelColor }};"></div></div>
                                <span style="font-size:.78rem;color:{{ $fuelColor }};font-weight:600;">{{ $i->fuel_level_pct }}%</span>
                            </div>
                        @else <span style="color:#cbd5e1;">—</span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex;gap:.3rem;flex-wrap:wrap;">
                            @if($i->has_critical_issue)
                                <span class="badge" style="background:#fef2f2;color:#dc2626;">⚠ Critique</span>
                            @else
                                @if($i->oil_level === 'low')    <span class="badge" style="background:#fef2f2;color:#dc2626;">Huile</span> @endif
                                @if($i->brakes_status === 'minor_issue') <span class="badge" style="background:#fffbeb;color:#92400e;">Freins</span> @endif
                                @if($i->lights_status === 'minor_issue') <span class="badge" style="background:#fffbeb;color:#92400e;">Feux</span> @endif
                                @if(!$i->has_critical_issue && $i->oil_level !== 'low' && $i->brakes_status !== 'minor_issue' && $i->lights_status !== 'minor_issue')
                                    <span class="badge" style="background:#f0fdf4;color:#059669;">✓ RAS</span>
                                @endif
                            @endif
                        </div>
                    </td>
                    <td style="font-size:.8rem;">
                        <div>{{ $i->inspector?->name ?? '—' }}</div>
                        @if($i->driver)
                            <div style="font-size:.73rem;color:#94a3b8;">Chauffeur : {{ $i->driver->full_name }}</div>
                        @endif
                    </td>
                    <td>
                        <span class="badge" style="background:{{ $st[2] }};color:{{ $st[1] }};">
                            <span class="dot" style="background:{{ $st[1] }};width:5px;height:5px;"></span>
                            {{ $st[0] }}
                        </span>
                    </td>
                    <td style="text-align:right;">
                        <a href="{{ route('inspections.show', $i) }}" class="btn btn-ghost" style="padding:.32rem .6rem;" title="Détail">
                            <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" style="text-align:center;padding:2.5rem;color:#94a3b8;">Aucune fiche de contrôle trouvée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($inspections->hasPages())
    <div style="display:flex;justify-content:space-between;align-items:center;padding:.75rem 1.25rem;font-size:.82rem;color:#64748b;">
        <span>{{ $inspections->firstItem() }}–{{ $inspections->lastItem() }} sur {{ $inspections->total() }}</span>
        {{ $inspections->links() }}
    </div>
    @endif
</div>
@endsection
