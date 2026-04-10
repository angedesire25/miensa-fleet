@extends('layouts.dashboard')

@section('title', 'Chauffeurs')
@section('page-title', 'Gestion des Chauffeurs')

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
tr.archived-row td{opacity:.65;}
.driver-cell{display:flex;align-items:center;gap:.75rem;}
</style>

{{-- ── Stats ──────────────────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(6,1fr);gap:1rem;margin-bottom:1.5rem;">
    <div class="stat-card">
        <div class="stat-icon" style="background:#eff6ff;">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4" stroke="#3b82f6" stroke-width="1.8"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke="#3b82f6" stroke-width="1.8" stroke-linecap="round"/></svg>
        </div>
        <div><div class="stat-val">{{ $stats['total'] }}</div><div class="stat-lbl">Total</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#f0fdf4;">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4" stroke="#10b981" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="#10b981" stroke-width="1.8"/></svg>
        </div>
        <div><div class="stat-val" style="color:#10b981;">{{ $stats['active'] }}</div><div class="stat-lbl">Actifs</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fffbeb;">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="#d97706" stroke-width="1.8"/><path d="M10 15V9M14 15V9" stroke="#d97706" stroke-width="2" stroke-linecap="round"/></svg>
        </div>
        <div><div class="stat-val" style="color:#d97706;">{{ $stats['suspended'] }}</div><div class="stat-lbl">Suspendus</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#f8fafc;">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="#64748b" stroke-width="1.8"/><circle cx="9" cy="7" r="4" stroke="#64748b" stroke-width="1.8"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke="#64748b" stroke-width="1.8"/></svg>
        </div>
        <div><div class="stat-val" style="color:#64748b;">{{ $stats['on_leave'] }}</div><div class="stat-lbl">En congé</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fef2f2;">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="#ef4444" stroke-width="1.8"/><path d="M12 9v4M12 17h.01" stroke="#ef4444" stroke-width="2" stroke-linecap="round"/></svg>
        </div>
        <div><div class="stat-val" style="color:#ef4444;">{{ $stats['license_expiring'] }}</div><div class="stat-lbl">Permis expirant</div></div>
    </div>
    <div class="stat-card" style="{{ $showArchived ? 'border-color:#f59e0b;background:#fffbeb;' : '' }}">
        <div class="stat-icon" style="background:{{ $showArchived ? '#fef3c7' : '#fff7ed' }};">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M21 8v13H3V8" stroke="#f59e0b" stroke-width="1.8" stroke-linecap="round"/><path d="M23 3H1v5h22V3z" stroke="#f59e0b" stroke-width="1.8"/><path d="M10 12h4" stroke="#f59e0b" stroke-width="2" stroke-linecap="round"/></svg>
        </div>
        <div><div class="stat-val" style="color:#d97706;">{{ $stats['archived'] }}</div><div class="stat-lbl">Archivés</div></div>
    </div>
</div>

{{-- ── Tableau ─────────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-head" style="justify-content:space-between;">
        <div style="display:flex;align-items:center;gap:.6rem;">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4" stroke="#10b981" stroke-width="2"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke="#10b981" stroke-width="2" stroke-linecap="round"/></svg>
            <span class="card-title">{{ $showArchived ? 'Chauffeurs archivés' : 'Liste des chauffeurs' }}</span>
            <span style="background:#f1f5f9;color:#64748b;font-size:.72rem;font-weight:600;padding:.1rem .5rem;border-radius:99px;">{{ $drivers->total() }}</span>
        </div>
        @if(!$showArchived)
        @can('drivers.create')
        <a href="{{ route('drivers.create') }}" class="btn btn-primary">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
            Nouveau chauffeur
        </a>
        @endcan
        @endif
    </div>

    {{-- Filtres --}}
    <div style="padding:.85rem 1.25rem;border-bottom:1px solid #f1f5f9;background:#fafafa;">
        <form method="GET" class="filters-bar">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Nom, matricule, téléphone…" class="filter-input" style="width:220px;">

            @if(!$showArchived)
            <select name="status" class="filter-input">
                <option value="all" @selected(!request('status') || request('status')=='all')>Tous les statuts</option>
                <option value="active"     @selected(request('status')=='active')>Actif</option>
                <option value="suspended"  @selected(request('status')=='suspended')>Suspendu</option>
                <option value="on_leave"   @selected(request('status')=='on_leave')>En congé</option>
                <option value="terminated" @selected(request('status')=='terminated')>Licencié</option>
            </select>
            @endif

            <select name="license" class="filter-input">
                <option value="">Toutes catégories permis</option>
                @foreach(['A','B','C','D','E','BE','CE'] as $cat)
                <option value="{{ $cat }}" @selected(request('license')===$cat)>Permis {{ $cat }}</option>
                @endforeach
            </select>

            <select name="contract" class="filter-input">
                <option value="">Tous contrats</option>
                <option value="permanent"   @selected(request('contract')=='permanent')>CDI</option>
                <option value="fixed_term"  @selected(request('contract')=='fixed_term')>CDD</option>
                <option value="interim"     @selected(request('contract')=='interim')>Intérim</option>
                <option value="contractor"  @selected(request('contract')=='contractor')>Prestataire</option>
            </select>

            <label style="display:flex;align-items:center;gap:.4rem;font-size:.82rem;cursor:pointer;padding:.4rem .75rem;border-radius:.45rem;border:1.5px solid {{ $showArchived ? '#f59e0b' : '#e2e8f0' }};color:{{ $showArchived ? '#b45309' : '#64748b' }};background:{{ $showArchived ? '#fffbeb' : '#fff' }};">
                <input type="checkbox" name="archived" value="1" onchange="this.form.submit()" {{ $showArchived ? 'checked' : '' }} style="accent-color:#f59e0b;">
                Archives
            </label>

            <button type="submit" class="btn btn-primary" style="padding:.45rem .85rem;">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Filtrer
            </button>
            @if(request()->anyFilled(['q','status','license','contract','archived']))
            <a href="{{ route('drivers.index') }}" class="btn btn-ghost" style="padding:.45rem .8rem;">Réinitialiser</a>
            @endif
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Chauffeur</th>
                    <th>Matricule</th>
                    <th>Contrat</th>
                    <th>Permis</th>
                    <th>Expiration permis</th>
                    <th>Affectation</th>
                    <th>Statut</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($drivers as $driver)
                @php
                    $statusMap = [
                        'active'     => ['Actif',     '#10b981','#f0fdf4'],
                        'suspended'  => ['Suspendu',  '#d97706','#fffbeb'],
                        'on_leave'   => ['En congé',  '#64748b','#f8fafc'],
                        'terminated' => ['Licencié',  '#ef4444','#fef2f2'],
                    ];
                    $contractMap = ['permanent'=>'CDI','fixed_term'=>'CDD','interim'=>'Intérim','contractor'=>'Prestataire'];
                    $s = $statusMap[$driver->status] ?? ['Inconnu','#64748b','#f8fafc'];
                    $licenseExpired  = $driver->license_expiry_date && $driver->license_expiry_date->isPast();
                    $licenseExpiring = !$licenseExpired && $driver->license_expiry_date && $driver->license_expiry_date->diffInDays(now()) <= 30;
                    $isAdmin = auth()->user()->hasAnyRole(['super_admin', 'admin']);
                @endphp
                <tr class="{{ $showArchived ? 'archived-row' : '' }}">
                    <td>
                        <div class="driver-cell">
                            @if($driver->avatar)
                                <img src="{{ Storage::url($driver->avatar) }}" style="width:36px;height:36px;border-radius:50%;object-fit:cover;flex-shrink:0;" alt="">
                            @else
                                <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#10b981,#059669);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.75rem;font-weight:700;flex-shrink:0;">
                                    {{ strtoupper(substr($driver->full_name, 0, 2)) }}
                                </div>
                            @endif
                            <div>
                                <div style="font-weight:600;color:#0f172a;">{{ $driver->full_name }}</div>
                                <div style="font-size:.73rem;color:#94a3b8;">{{ $driver->phone }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span style="font-family:monospace;font-size:.8rem;background:#f1f5f9;padding:.15rem .45rem;border-radius:.3rem;">{{ $driver->matricule }}</span>
                    </td>
                    <td style="font-size:.82rem;">{{ $contractMap[$driver->contract_type] ?? $driver->contract_type }}</td>
                    <td>
                        @foreach($driver->license_categories ?? [] as $cat)
                        <span class="badge" style="background:#eff6ff;color:#1d4ed8;margin-right:.2rem;">{{ $cat }}</span>
                        @endforeach
                    </td>
                    <td>
                        @if($driver->license_expiry_date)
                            <span style="font-size:.8rem;color:{{ $licenseExpired ? '#dc2626' : ($licenseExpiring ? '#d97706' : '#374151') }};font-weight:{{ ($licenseExpired || $licenseExpiring) ? '600' : '400' }};">
                                {{ $driver->license_expiry_date->isoFormat('D MMM YYYY') }}
                            </span>
                            @if($licenseExpired)
                                <span class="badge" style="background:#fef2f2;color:#dc2626;margin-left:.25rem;">Expiré</span>
                            @elseif($licenseExpiring)
                                <span class="badge" style="background:#fffbeb;color:#d97706;margin-left:.25rem;">Bientôt</span>
                            @endif
                        @else —
                        @endif
                    </td>
                    <td>
                        @if(!$showArchived && $driver->activeAssignment)
                            <div style="font-size:.8rem;font-weight:500;">{{ $driver->activeAssignment->vehicle->plate ?? '—' }}</div>
                            <div style="font-size:.73rem;color:#3b82f6;">En mission</div>
                        @else
                            <span style="color:#94a3b8;font-size:.8rem;">—</span>
                        @endif
                    </td>
                    <td>
                        @if($showArchived)
                            <div>
                                <span class="badge" style="background:#fef3c7;color:#b45309;">
                                    <svg width="10" height="10" fill="none" viewBox="0 0 24 24"><path d="M21 8v13H3V8" stroke="currentColor" stroke-width="2"/><path d="M23 3H1v5h22V3z" stroke="currentColor" stroke-width="2"/></svg>
                                    Archivé
                                </span>
                                @if($driver->deleted_at)
                                <div style="font-size:.7rem;color:#94a3b8;margin-top:.2rem;">{{ $driver->deleted_at->isoFormat('D MMM YYYY') }}</div>
                                @endif
                            </div>
                        @else
                            <span class="badge" style="background:{{ $s[2] }};color:{{ $s[1] }};">
                                <span style="width:5px;height:5px;border-radius:50%;background:{{ $s[1] }};display:inline-block;"></span>
                                {{ $s[0] }}
                            </span>
                        @endif
                    </td>
                    <td style="text-align:right;">
                        <div style="display:flex;justify-content:flex-end;gap:.4rem;flex-wrap:wrap;">
                            @if($showArchived)
                                {{-- Mode archives : restaurer + supprimer définitivement --}}
                                @can('drivers.delete')
                                <form method="POST" action="{{ route('drivers.restore', $driver->id) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-ghost" style="padding:.35rem .7rem;color:#d97706;border-color:#f59e0b;font-size:.78rem;">
                                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24"><path d="M3 12a9 9 0 109-9 9 9 0 00-9.26 9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M3 3v5h5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        Restaurer
                                    </button>
                                </form>
                                @endcan
                                @if($isAdmin)
                                <form method="POST" action="{{ route('drivers.force-destroy', $driver->id) }}" style="display:inline;"
                                      data-confirm="Supprimer définitivement {{ addslashes($driver->full_name) }} ? Cette action est irréversible."
                                      data-title="Suppression définitive" data-btn-text="Supprimer" data-btn-color="#dc2626">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-ghost" style="padding:.35rem .7rem;color:#dc2626;border-color:#fca5a5;font-size:.78rem;">
                                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24"><polyline points="3,6 5,6 21,6" stroke="currentColor" stroke-width="2"/><path d="M19 6l-1 14H6L5 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M10 11v6M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                        Supprimer
                                    </button>
                                </form>
                                @endif
                            @else
                                {{-- Mode normal : voir + modifier + archiver --}}
                                <a href="{{ route('drivers.show', $driver) }}" class="btn btn-ghost" style="padding:.35rem .6rem;" title="Détail">
                                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
                                </a>
                                @can('drivers.edit')
                                <a href="{{ route('drivers.edit', $driver) }}" class="btn btn-ghost" style="padding:.35rem .6rem;" title="Modifier">
                                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                </a>
                                @endcan
                                @can('drivers.delete')
                                <form method="POST" action="{{ route('drivers.destroy', $driver) }}" style="display:inline;"
                                      data-confirm="Le chauffeur {{ addslashes($driver->full_name) }} sera archivé."
                                      data-title="Archiver ce chauffeur ?" data-btn-text="Archiver">
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
                <tr><td colspan="8" style="text-align:center;padding:2.5rem;color:#94a3b8;">
                    {{ $showArchived ? 'Aucun chauffeur archivé.' : 'Aucun chauffeur trouvé.' }}
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($drivers->hasPages())
    <div style="display:flex;justify-content:space-between;align-items:center;padding:.75rem 1.25rem;font-size:.82rem;color:#64748b;">
        <span>{{ $drivers->firstItem() }}–{{ $drivers->lastItem() }} sur {{ $drivers->total() }}</span>
        {{ $drivers->links() }}
    </div>
    @endif
</div>
@endsection
