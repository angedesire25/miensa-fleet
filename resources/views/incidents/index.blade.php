@extends('layouts.dashboard')

@section('title', 'Sinistres')
@section('page-title', 'Sinistres & Incidents')

@section('content')
<style>
.card{background:#fff;border-radius:.75rem;border:1px solid #e2e8f0;overflow:hidden;margin-bottom:1.25rem;}
.card-head{padding:.85rem 1.25rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;gap:.6rem;}
.card-title{font-size:.9rem;font-weight:700;color:#0f172a;}
.stat-card{background:#fff;border-radius:.75rem;border:1px solid #e2e8f0;padding:1.1rem 1.25rem;display:flex;align-items:center;gap:1rem;}
.stat-icon{width:42px;height:42px;border-radius:.6rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.stat-val{font-size:1.5rem;font-weight:800;color:#0f172a;line-height:1;}
.stat-lbl{font-size:.75rem;color:#64748b;margin-top:.2rem;}
.badge{display:inline-flex;align-items:center;gap:.25rem;padding:.18rem .55rem;border-radius:99px;font-size:.7rem;font-weight:600;}
.btn{padding:.45rem .9rem;border-radius:.45rem;font-size:.82rem;font-weight:600;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;transition:opacity .15s;}
.btn-primary{background:linear-gradient(135deg,#10b981,#059669);color:#fff;}
.btn-ghost{background:#f8fafc;color:#374151;border:1.5px solid #e2e8f0;}
.btn-ghost:hover{background:#f1f5f9;}
.filters-bar{display:flex;gap:.65rem;flex-wrap:wrap;align-items:flex-end;}
.filter-input{padding:.45rem .75rem;border:1.5px solid #e2e8f0;border-radius:.45rem;font-size:.825rem;background:#fff;color:#0f172a;outline:none;}
.filter-input:focus{border-color:#10b981;}
.table-wrap{overflow-x:auto;}
table{width:100%;border-collapse:collapse;}
th{font-size:.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;padding:.6rem 1rem;border-bottom:1.5px solid #f1f5f9;text-align:left;white-space:nowrap;}
td{padding:.7rem 1rem;border-bottom:1px solid #f8fafc;font-size:.855rem;color:#374151;vertical-align:middle;}
tr:hover td{background:#f8fafc;}
.pagination-wrap{display:flex;justify-content:space-between;align-items:center;padding:.75rem 0;font-size:.82rem;color:#64748b;}
</style>

{{-- ── Statistiques ─────────────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:1rem;margin-bottom:1.5rem;">
    <div class="stat-card">
        <div class="stat-icon" style="background:#eff6ff;">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="#3b82f6" stroke-width="1.8"/></svg>
        </div>
        <div><div class="stat-val">{{ $stats['total'] }}</div><div class="stat-lbl">Total</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fef3c7;">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke="#f59e0b" stroke-width="1.8"/><path d="M12 8v4M12 16h.01" stroke="#f59e0b" stroke-width="2" stroke-linecap="round"/></svg>
        </div>
        <div><div class="stat-val" style="color:#f59e0b;">{{ $stats['ouverts'] }}</div><div class="stat-lbl">En cours</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#ede9fe;">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.77 3.77z" stroke="#8b5cf6" stroke-width="1.8"/></svg>
        </div>
        <div><div class="stat-val" style="color:#8b5cf6;">{{ $stats['au_garage'] }}</div><div class="stat-lbl">Au garage</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#f0fdf4;">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4" stroke="#10b981" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="#10b981" stroke-width="1.8"/></svg>
        </div>
        <div><div class="stat-val" style="color:#10b981;">{{ $stats['repares'] }}</div><div class="stat-lbl">Réparés</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fef2f2;">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="#ef4444" stroke-width="1.8"/><path d="M12 9v4M12 17h.01" stroke="#ef4444" stroke-width="2" stroke-linecap="round"/></svg>
        </div>
        <div><div class="stat-val" style="color:#ef4444;">{{ $stats['graves'] }}</div><div class="stat-lbl">Graves</div></div>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <span class="card-title">Liste des sinistres</span>
        @can('incidents.create')
        <a href="{{ route('incidents.create') }}" class="btn btn-primary">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
            Déclarer un sinistre
        </a>
        @endcan
    </div>

    {{-- Filtres --}}
    <div style="padding:1rem 1.25rem;border-bottom:1px solid #f1f5f9;">
        <form method="GET" class="filters-bar">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Véhicule, lieu, description…" class="filter-input" style="min-width:200px;">
            <select name="status" class="filter-input">
                <option value="all">Tous les statuts</option>
                <option value="open" @selected(request('status')==='open')>Ouvert</option>
                <option value="at_garage" @selected(request('status')==='at_garage')>Au garage</option>
                <option value="repaired" @selected(request('status')==='repaired')>Réparé</option>
                <option value="total_loss" @selected(request('status')==='total_loss')>Perte totale</option>
                <option value="closed" @selected(request('status')==='closed')>Clôturé</option>
            </select>
            <select name="severity" class="filter-input">
                <option value="all">Toutes sévérités</option>
                <option value="minor" @selected(request('severity')==='minor')>Mineur</option>
                <option value="moderate" @selected(request('severity')==='moderate')>Modéré</option>
                <option value="major" @selected(request('severity')==='major')>Majeur</option>
                <option value="total_loss" @selected(request('severity')==='total_loss')>Perte totale</option>
            </select>
            <select name="type" class="filter-input">
                <option value="all">Tous types</option>
                <option value="accident" @selected(request('type')==='accident')>Accident</option>
                <option value="breakdown" @selected(request('type')==='breakdown')>Panne</option>
                <option value="flat_tire" @selected(request('type')==='flat_tire')>Crevaison</option>
                <option value="body_damage" @selected(request('type')==='body_damage')>Carrosserie</option>
                <option value="theft" @selected(request('type')==='theft')>Vol</option>
                <option value="vandalism" @selected(request('type')==='vandalism')>Vandalisme</option>
                <option value="other" @selected(request('type')==='other')>Autre</option>
            </select>
            <button type="submit" class="btn btn-ghost">Filtrer</button>
            @if(request()->anyFilled(['q','status','severity','type']))
                <a href="{{ route('incidents.index') }}" class="btn btn-ghost" style="color:#ef4444;">Effacer</a>
            @endif
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Véhicule</th>
                    <th>Type</th>
                    <th>Sévérité</th>
                    <th>Date</th>
                    <th>Lieu</th>
                    <th>Statut</th>
                    <th>Garage</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($incidents as $incident)
                @php
                    $statusColors = [
                        'open'       => ['#fef3c7','#92400e'],
                        'at_garage'  => ['#ede9fe','#5b21b6'],
                        'repaired'   => ['#f0fdf4','#166534'],
                        'total_loss' => ['#fee2e2','#991b1b'],
                        'closed'     => ['#f8fafc','#64748b'],
                    ];
                    $severityColors = [
                        'minor'      => ['#f0fdf4','#166534'],
                        'moderate'   => ['#fef3c7','#92400e'],
                        'major'      => ['#fff7ed','#9a3412'],
                        'total_loss' => ['#fee2e2','#991b1b'],
                    ];
                    $typeLabels = [
                        'accident'        => 'Accident',
                        'breakdown'       => 'Panne',
                        'flat_tire'       => 'Crevaison',
                        'electrical_fault'=> 'Électrique',
                        'body_damage'     => 'Carrosserie',
                        'theft_attempt'   => 'Tentative vol',
                        'theft'           => 'Vol',
                        'flood_damage'    => 'Inondation',
                        'fire'            => 'Incendie',
                        'vandalism'       => 'Vandalisme',
                        'other'           => 'Autre',
                    ];
                    $statusLabels = [
                        'open'       => 'Ouvert',
                        'at_garage'  => 'Au garage',
                        'repaired'   => 'Réparé',
                        'total_loss' => 'Perte totale',
                        'closed'     => 'Clôturé',
                    ];
                    $severityLabels = [
                        'minor'      => 'Mineur',
                        'moderate'   => 'Modéré',
                        'major'      => 'Majeur',
                        'total_loss' => 'Perte totale',
                    ];
                    [$sBg,$sFg] = $statusColors[$incident->status]   ?? ['#f8fafc','#64748b'];
                    [$svBg,$svFg] = $severityColors[$incident->severity] ?? ['#f8fafc','#64748b'];
                @endphp
                <tr>
                    <td style="font-weight:600;color:#64748b;">#{{ $incident->id }}</td>
                    <td>
                        @if($incident->vehicle)
                            <a href="{{ route('vehicles.show', $incident->vehicle) }}" style="font-weight:600;color:#0f172a;text-decoration:none;">
                                {{ $incident->vehicle->plate }}
                            </a>
                            <div style="font-size:.75rem;color:#64748b;">{{ $incident->vehicle->brand }} {{ $incident->vehicle->model }}</div>
                        @else
                            <span style="color:#94a3b8;">—</span>
                        @endif
                    </td>
                    <td>
                        <span style="font-size:.8rem;">{{ $typeLabels[$incident->type] ?? $incident->type }}</span>
                    </td>
                    <td>
                        <span class="badge" style="background:{{ $svBg }};color:{{ $svFg }};">
                            {{ $severityLabels[$incident->severity] ?? $incident->severity }}
                        </span>
                    </td>
                    <td style="font-size:.8rem;white-space:nowrap;">
                        {{ $incident->datetime_occurred?->format('d/m/Y') }}<br>
                        <span style="color:#94a3b8;">{{ $incident->datetime_occurred?->format('H:i') }}</span>
                    </td>
                    <td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.8rem;">
                        {{ $incident->location ?? '—' }}
                    </td>
                    <td>
                        <span class="badge" style="background:{{ $sBg }};color:{{ $sFg }};">
                            {{ $statusLabels[$incident->status] ?? $incident->status }}
                        </span>
                    </td>
                    <td style="font-size:.8rem;">
                        {{ $incident->latestRepair?->garage?->name ?? '—' }}
                    </td>
                    <td>
                        <a href="{{ route('incidents.show', $incident) }}" class="btn btn-ghost" style="padding:.3rem .65rem;font-size:.78rem;">
                            Voir
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align:center;padding:2.5rem;color:#94a3b8;">
                        Aucun sinistre trouvé.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($incidents->hasPages())
    <div style="padding:.75rem 1.25rem;border-top:1px solid #f1f5f9;" class="pagination-wrap">
        <span>{{ $incidents->firstItem() }}–{{ $incidents->lastItem() }} sur {{ $incidents->total() }}</span>
        {{ $incidents->links() }}
    </div>
    @endif
</div>
@endsection
