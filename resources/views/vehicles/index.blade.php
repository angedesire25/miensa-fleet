@extends('layouts.dashboard')

@section('title', 'Véhicules')
@section('page-title', 'Parc Véhicules')

@section('content')
<style>
.card{background:#fff;border-radius:.75rem;border:1px solid #e2e8f0;overflow:hidden;margin-bottom:1.25rem;}
.card-head{padding:.85rem 1.25rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:.6rem;}
.card-title{font-size:.9rem;font-weight:700;color:#0f172a;}
.stat-card{background:#fff;border-radius:.75rem;border:1px solid #e2e8f0;padding:1.1rem 1.25rem;display:flex;align-items:center;gap:1rem;}
.stat-icon{width:42px;height:42px;border-radius:.6rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.stat-val{font-size:1.5rem;font-weight:800;color:#0f172a;line-height:1;}
.stat-lbl{font-size:.75rem;color:#64748b;margin-top:.2rem;}
.badge{display:inline-flex;align-items:center;gap:.25rem;padding:.18rem .55rem;border-radius:99px;font-size:.7rem;font-weight:600;}
.btn{padding:.45rem .9rem;border-radius:.45rem;font-size:.82rem;font-weight:600;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;transition:opacity .15s;}
.btn-primary{background:linear-gradient(135deg,#10b981,#059669);color:#fff;}
.btn-primary:hover{opacity:.9;}
.btn-ghost{background:#f8fafc;color:#374151;border:1.5px solid #e2e8f0;}
.btn-ghost:hover{background:#f1f5f9;}
.btn-warning{background:#fef3c7;color:#b45309;border:1.5px solid #fde68a;}
.btn-warning:hover{background:#fde68a;}
.btn-danger-sm{background:#fee2e2;color:#dc2626;border:1.5px solid #fca5a5;padding:.32rem .6rem;border-radius:.45rem;font-size:.78rem;font-weight:600;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:.35rem;}
.btn-danger-sm:hover{background:#fecaca;}
.filters-bar{display:flex;gap:.65rem;flex-wrap:wrap;align-items:flex-end;}
.filter-input{padding:.45rem .75rem;border:1.5px solid #e2e8f0;border-radius:.45rem;font-size:.825rem;background:#fff;color:#0f172a;outline:none;}
.filter-input:focus{border-color:#10b981;}
.table-wrap{overflow-x:auto;}
table{width:100%;border-collapse:collapse;}
th{font-size:.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;padding:.6rem 1rem;border-bottom:1.5px solid #f1f5f9;text-align:left;white-space:nowrap;}
td{padding:.7rem 1rem;border-bottom:1px solid #f8fafc;font-size:.855rem;color:#374151;vertical-align:middle;}
tr:hover td{background:#f8fafc;}
tr.archived-row td{opacity:.65;}
.vehicle-cell{display:flex;align-items:center;gap:.75rem;}
.vehicle-photo{width:44px;height:36px;border-radius:.35rem;object-fit:cover;background:#f1f5f9;flex-shrink:0;}
.vehicle-photo-placeholder{width:44px;height:36px;border-radius:.35rem;background:linear-gradient(135deg,#e2e8f0,#cbd5e1);display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.pagination-wrap{display:flex;justify-content:space-between;align-items:center;padding:.75rem 0;font-size:.82rem;color:#64748b;}
</style>

{{-- ── Stats ──────────────────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(6,1fr);gap:1rem;margin-bottom:1.5rem;">
    <div class="stat-card">
        <div class="stat-icon" style="background:#eff6ff;">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M3 17h2l1-3h12l1 3h2" stroke="#3b82f6" stroke-width="1.8" stroke-linecap="round"/><circle cx="7.5" cy="18.5" r="1.5" stroke="#3b82f6" stroke-width="1.5"/><circle cx="16.5" cy="18.5" r="1.5" stroke="#3b82f6" stroke-width="1.5"/></svg>
        </div>
        <div><div class="stat-val">{{ $stats['total'] }}</div><div class="stat-lbl">Total</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#f0fdf4;">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4" stroke="#10b981" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="#10b981" stroke-width="1.8"/></svg>
        </div>
        <div><div class="stat-val" style="color:#10b981;">{{ $stats['available'] }}</div><div class="stat-lbl">Disponibles</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#eff6ff;">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7" stroke="#3b82f6" stroke-width="2" stroke-linecap="round"/></svg>
        </div>
        <div><div class="stat-val" style="color:#3b82f6;">{{ $stats['on_mission'] }}</div><div class="stat-lbl">En mission</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fffbeb;">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.77 3.77z" stroke="#d97706" stroke-width="1.8"/></svg>
        </div>
        <div><div class="stat-val" style="color:#d97706;">{{ $stats['maintenance'] }}</div><div class="stat-lbl">Maintenance</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fef2f2;">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M12 9v4M12 17h.01" stroke="#ef4444" stroke-width="2" stroke-linecap="round"/><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="#ef4444" stroke-width="1.8"/></svg>
        </div>
        <div><div class="stat-val" style="color:#ef4444;">{{ $stats['breakdown'] }}</div><div class="stat-lbl">En panne</div></div>
    </div>
    <div class="stat-card" style="{{ $showArchived ? 'border-color:#f59e0b;' : '' }}">
        <div class="stat-icon" style="background:#fffbeb;">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M5 8h14M5 8a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v.01a2 2 0 01-2 2M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8" stroke="#f59e0b" stroke-width="1.8" stroke-linecap="round"/></svg>
        </div>
        <div><div class="stat-val" style="color:#f59e0b;">{{ $stats['archived'] }}</div><div class="stat-lbl">Archivés</div></div>
    </div>
</div>

{{-- ── Tableau ─────────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-head" style="justify-content:space-between;">
        <div style="display:flex;align-items:center;gap:.6rem;">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M3 17h2l1-3h12l1 3h2" stroke="#10b981" stroke-width="2" stroke-linecap="round"/><circle cx="7.5" cy="18.5" r="1.5" stroke="#10b981" stroke-width="1.5"/><circle cx="16.5" cy="18.5" r="1.5" stroke="#10b981" stroke-width="1.5"/></svg>
            <span class="card-title">{{ $showArchived ? 'Archives des véhicules' : 'Liste des véhicules' }}</span>
            <span style="background:#f1f5f9;color:#64748b;font-size:.72rem;font-weight:600;padding:.1rem .5rem;border-radius:99px;">{{ $vehicles->total() }}</span>
            @if($showArchived)
            <span style="background:#fef3c7;color:#b45309;font-size:.72rem;font-weight:600;padding:.15rem .55rem;border-radius:99px;">Archives</span>
            @endif
        </div>
        @if(!$showArchived)
        @can('vehicles.create')
        <a href="{{ route('vehicles.create') }}" class="btn btn-primary">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
            Nouveau véhicule
        </a>
        @endcan
        @endif
    </div>

    {{-- Filtres --}}
    <div style="padding:.85rem 1.25rem;border-bottom:1px solid #f1f5f9;background:#fafafa;">
        <form method="GET" class="filters-bar">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher (immat, marque, modèle…)" class="filter-input" style="width:220px;">

            @if(!$showArchived)
            <select name="status" class="filter-input">
                <option value="all" @selected(!request('status') || request('status')=='all')>Tous les statuts</option>
                <option value="available"   @selected(request('status')=='available')>Disponible</option>
                <option value="on_mission"  @selected(request('status')=='on_mission')>En mission</option>
                <option value="maintenance" @selected(request('status')=='maintenance')>Maintenance</option>
                <option value="breakdown"   @selected(request('status')=='breakdown')>En panne</option>
                <option value="sold"        @selected(request('status')=='sold')>Vendu</option>
                <option value="retired"     @selected(request('status')=='retired')>Hors service</option>
            </select>
            @endif

            <select name="type" class="filter-input">
                <option value="">Tous types</option>
                <option value="sedan"       @selected(request('type')=='sedan')>Berline</option>
                <option value="suv"         @selected(request('type')=='suv')>SUV</option>
                <option value="van"         @selected(request('type')=='van')>Van / Utilitaire</option>
                <option value="pickup"      @selected(request('type')=='pickup')>Pick-up</option>
                <option value="truck"       @selected(request('type')=='truck')>Camion</option>
                <option value="city"        @selected(request('type')=='city')>Citadine</option>
                <option value="motorcycle"  @selected(request('type')=='motorcycle')>Moto</option>
            </select>

            <select name="fuel" class="filter-input">
                <option value="">Tous carburants</option>
                <option value="diesel"   @selected(request('fuel')=='diesel')>Diesel</option>
                <option value="gasoline" @selected(request('fuel')=='gasoline')>Essence</option>
                <option value="hybrid"   @selected(request('fuel')=='hybrid')>Hybride</option>
                <option value="electric" @selected(request('fuel')=='electric')>Électrique</option>
                <option value="lpg"      @selected(request('fuel')=='lpg')>GPL</option>
            </select>

            <label style="display:flex;align-items:center;gap:.4rem;font-size:.82rem;color:#64748b;cursor:pointer;padding:.45rem .6rem;border:1.5px solid #e2e8f0;border-radius:.45rem;background:#fff;{{ $showArchived ? 'border-color:#f59e0b;color:#b45309;background:#fffbeb;' : '' }}">
                <input type="checkbox" name="archived" value="1" onchange="this.form.submit()" {{ $showArchived ? 'checked' : '' }} style="width:14px;height:14px;accent-color:#f59e0b;">
                Archives
            </label>

            <button type="submit" class="btn btn-primary" style="padding:.45rem .85rem;">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Filtrer
            </button>
            @if(request()->anyFilled(['q','status','type','fuel']) || $showArchived)
            <a href="{{ route('vehicles.index') }}" class="btn btn-ghost" style="padding:.45rem .8rem;">Réinitialiser</a>
            @endif
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Véhicule</th>
                    <th>Immatriculation</th>
                    <th>Type / Carburant</th>
                    <th>Kilométrage</th>
                    <th>Chauffeur</th>
                    <th>Statut</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vehicles as $vehicle)
                @php
                    $statusMap = [
                        'available'  => ['Disponible',   '#10b981','#f0fdf4'],
                        'on_mission' => ['En mission',   '#3b82f6','#eff6ff'],
                        'maintenance'=> ['Maintenance',  '#d97706','#fffbeb'],
                        'breakdown'  => ['En panne',     '#ef4444','#fef2f2'],
                        'sold'       => ['Vendu',        '#8b5cf6','#faf5ff'],
                        'retired'    => ['Hors service', '#64748b','#f8fafc'],
                    ];
                    $typeMap = ['sedan'=>'Berline','suv'=>'SUV','van'=>'Van','pickup'=>'Pick-up','truck'=>'Camion','city'=>'Citadine','motorcycle'=>'Moto'];
                    $fuelMap = ['diesel'=>'Diesel','gasoline'=>'Essence','hybrid'=>'Hybride','electric'=>'Électrique','lpg'=>'GPL'];
                    $isPermanent = !$showArchived && $vehicle->status === 'available' && $vehicle->permanentAssignment !== null;
                    $s = $isPermanent ? ['Affecté','#6366f1','#ede9fe'] : ($statusMap[$vehicle->status] ?? ['Inconnu','#64748b','#f8fafc']);
                    $isAdmin = auth()->user()->hasAnyRole(['super_admin','admin']);
                @endphp
                <tr class="{{ $showArchived ? 'archived-row' : '' }}">
                    <td>
                        <div class="vehicle-cell">
                            @if($vehicle->profilePhoto)
                                <img src="{{ Storage::url($vehicle->profilePhoto->file_path) }}" class="vehicle-photo" alt="">
                            @else
                                <div class="vehicle-photo-placeholder">
                                    <svg width="18" height="14" fill="none" viewBox="0 0 24 18"><path d="M3 13h2l1-3h12l1 3h2" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/><circle cx="7.5" cy="14.5" r="1.5" stroke="#94a3b8" stroke-width="1.5"/><circle cx="16.5" cy="14.5" r="1.5" stroke="#94a3b8" stroke-width="1.5"/></svg>
                                </div>
                            @endif
                            <div>
                                <div style="font-weight:600;color:#0f172a;">{{ $vehicle->brand }} {{ $vehicle->model }}</div>
                                <div style="font-size:.75rem;color:#94a3b8;">{{ $vehicle->year }} · {{ $vehicle->color ?? '—' }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span style="font-family:monospace;font-size:.82rem;font-weight:600;background:#f1f5f9;padding:.2rem .5rem;border-radius:.3rem;">{{ $vehicle->plate }}</span>
                    </td>
                    <td>
                        <div style="font-size:.8rem;">{{ $typeMap[$vehicle->vehicle_type] ?? $vehicle->vehicle_type }}</div>
                        <div style="font-size:.73rem;color:#94a3b8;">{{ $fuelMap[$vehicle->fuel_type] ?? $vehicle->fuel_type }}</div>
                    </td>
                    <td>
                        <div style="font-size:.85rem;font-weight:600;">{{ number_format($vehicle->km_current) }} km</div>
                        @if($vehicle->km_next_service)
                        <div style="font-size:.73rem;color:{{ $vehicle->needs_service ? '#ef4444' : '#94a3b8' }};">
                            Entretien : {{ number_format($vehicle->km_next_service) }} km
                        </div>
                        @endif
                    </td>
                    <td>
                        @if($vehicle->currentDriver)
                            <div style="font-size:.83rem;font-weight:500;">{{ $vehicle->currentDriver->full_name }}</div>
                            <div style="font-size:.73rem;color:#94a3b8;">{{ $vehicle->currentDriver->matricule }}</div>
                        @else
                            <span style="color:#94a3b8;font-size:.8rem;">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge" style="background:{{ $s[2] }};color:{{ $s[1] }};">
                            <span style="width:5px;height:5px;border-radius:50%;background:{{ $s[1] }};display:inline-block;"></span>
                            {{ $s[0] }}
                        </span>
                        @if($showArchived)
                        <div style="font-size:.68rem;color:#f59e0b;margin-top:.2rem;">Archivé le {{ $vehicle->deleted_at->isoFormat('D MMM YYYY') }}</div>
                        @endif
                    </td>
                    <td style="text-align:right;">
                        <div style="display:flex;justify-content:flex-end;gap:.4rem;">
                            @if($showArchived)
                                @can('vehicles.delete')
                                <form method="POST" action="{{ route('vehicles.restore', $vehicle->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-warning" style="padding:.32rem .6rem;font-size:.78rem;" title="Restaurer">
                                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke="currentColor" stroke-width="2"/><polyline points="9 22 9 12 15 12 15 22" stroke="currentColor" stroke-width="2"/></svg>
                                        Restaurer
                                    </button>
                                </form>
                                @endcan
                                @if($isAdmin)
                                <form method="POST" action="{{ route('vehicles.force-destroy', $vehicle->id) }}"
                                      data-confirm="Supprimer définitivement le véhicule {{ addslashes($vehicle->plate) }} ? Cette action est irréversible."
                                      data-title="Suppression définitive"
                                      data-btn-text="Supprimer"
                                      data-btn-color="#dc2626">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-danger-sm" title="Supprimer définitivement">
                                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M19 6l-1 14H6L5 6M10 11v6M14 11v6M9 6V4h6v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                        Supprimer
                                    </button>
                                </form>
                                @endif
                            @else
                                <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-ghost" style="padding:.35rem .6rem;" title="Détail">
                                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
                                </a>
                                @can('vehicles.edit')
                                <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-ghost" style="padding:.35rem .6rem;" title="Modifier">
                                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                </a>
                                @endcan
                                @can('vehicles.delete')
                                <form method="POST" action="{{ route('vehicles.destroy', $vehicle) }}"
                                      data-confirm="Le véhicule {{ addslashes($vehicle->plate) }} sera archivé."
                                      data-title="Archiver ce véhicule ?" data-btn-text="Archiver">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-ghost" style="padding:.35rem .6rem;color:#dc2626;" title="Archiver">
                                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><polyline points="3,6 5,6 21,6" stroke="currentColor" stroke-width="2"/><path d="M19 6l-1 14H6L5 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                    </button>
                                </form>
                                @endcan
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;padding:2.5rem;color:#94a3b8;">{{ $showArchived ? 'Aucun véhicule archivé.' : 'Aucun véhicule trouvé.' }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($vehicles->hasPages())
    <div class="pagination-wrap" style="padding:.75rem 1.25rem;">
        <span>{{ $vehicles->firstItem() }}–{{ $vehicles->lastItem() }} sur {{ $vehicles->total() }}</span>
        {{ $vehicles->links() }}
    </div>
    @endif
</div>

@endsection
