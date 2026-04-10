@extends('layouts.dashboard')

@section('title', 'Nettoyages')
@section('page-title', 'Entretien & Nettoyage')

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
.filter-input{padding:.45rem .75rem;border:1.5px solid #e2e8f0;border-radius:.45rem;font-size:.825rem;background:#fff;color:#0f172a;outline:none;}
.filter-input:focus{border-color:#10b981;}
.filters-bar{display:flex;gap:.65rem;flex-wrap:wrap;align-items:flex-end;}
.table-wrap{overflow-x:auto;}
table{width:100%;border-collapse:collapse;}
th{font-size:.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;padding:.6rem 1rem;border-bottom:1.5px solid #f1f5f9;text-align:left;white-space:nowrap;}
td{padding:.7rem 1rem;border-bottom:1px solid #f8fafc;font-size:.855rem;color:#374151;vertical-align:middle;}
tr:hover td{background:#f8fafc;}
.vehicle-cell{display:flex;align-items:center;gap:.65rem;}
.vehicle-thumb{width:38px;height:32px;border-radius:.3rem;object-fit:cover;background:#f1f5f9;flex-shrink:0;}
.vehicle-thumb-ph{width:38px;height:32px;border-radius:.3rem;background:linear-gradient(135deg,#e2e8f0,#cbd5e1);display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.pagination-wrap{display:flex;justify-content:space-between;align-items:center;padding:.75rem 1.25rem;font-size:.82rem;color:#64748b;}
</style>

{{-- ── Stats ──────────────────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(6,1fr);gap:1rem;margin-bottom:1.5rem;">
    <div class="stat-card">
        <div class="stat-icon" style="background:#f0fdf4;">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6" stroke="#10b981" stroke-width="1.8" stroke-linecap="round"/></svg>
        </div>
        <div><div class="stat-val">{{ $stats['total'] }}</div><div class="stat-lbl">Total</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#eff6ff;">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="#3b82f6" stroke-width="1.8"/><path d="M12 8v4l3 3" stroke="#3b82f6" stroke-width="2" stroke-linecap="round"/></svg>
        </div>
        <div><div class="stat-val" style="color:#3b82f6;">{{ $stats['scheduled'] }}</div><div class="stat-lbl">Planifiés</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fffbeb;">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4" stroke="#f59e0b" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="#f59e0b" stroke-width="1.8"/></svg>
        </div>
        <div><div class="stat-val" style="color:#f59e0b;">{{ $stats['confirmed'] }}</div><div class="stat-lbl">Confirmés</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#f0fdf4;">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4" stroke="#10b981" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="#10b981" stroke-width="1.8"/></svg>
        </div>
        <div><div class="stat-val" style="color:#10b981;">{{ $stats['completed'] }}</div><div class="stat-lbl">Effectués</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fef2f2;">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M12 9v4M12 17h.01" stroke="#ef4444" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="#ef4444" stroke-width="1.8"/></svg>
        </div>
        <div><div class="stat-val" style="color:#ef4444;">{{ $stats['missed'] }}</div><div class="stat-lbl">Manqués</div></div>
    </div>
    <a href="{{ route('cleanings.index', ['archived'=>1]) }}" style="text-decoration:none;">
    <div class="stat-card" style="{{ $showArchived ? 'border-color:#94a3b8;background:#f8fafc;' : '' }}">
        <div class="stat-icon" style="background:#f1f5f9;">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M21 8v13H3V8M1 3h22v5H1zM10 12h4" stroke="#64748b" stroke-width="1.8" stroke-linecap="round"/></svg>
        </div>
        <div><div class="stat-val" style="color:#64748b;">{{ $stats['archived'] }}</div><div class="stat-lbl">Archivés</div></div>
    </div>
    </a>
</div>

{{-- ── Tableau ─────────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-head" style="justify-content:space-between;">
        <div style="display:flex;align-items:center;gap:.6rem;">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6" stroke="#10b981" stroke-width="2" stroke-linecap="round"/></svg>
            <span class="card-title">Planning de nettoyage</span>
            <span style="background:#f1f5f9;color:#64748b;font-size:.72rem;font-weight:600;padding:.1rem .5rem;border-radius:99px;">{{ $cleanings->total() }}</span>
        </div>
        @if($showArchived)
            <span style="background:#f1f5f9;color:#64748b;font-size:.78rem;font-weight:600;padding:.3rem .75rem;border-radius:99px;display:flex;align-items:center;gap:.35rem;">
                <svg width="12" height="12" fill="none" viewBox="0 0 24 24"><path d="M21 8v13H3V8M1 3h22v5H1z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Mode archives
            </span>
        @else
        @can('cleanings.create')
        <a href="{{ route('cleanings.create') }}" class="btn btn-primary">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
            Planifier un nettoyage
        </a>
        @endcan
        @endif
    </div>

    {{-- Filtres --}}
    <div style="padding:.85rem 1.25rem;border-bottom:1px solid #f1f5f9;background:#fafafa;">
        <form method="GET" class="filters-bar">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Immat, marque, modèle…" class="filter-input" style="width:200px;">

            <select name="status" class="filter-input">
                <option value="all" @selected(!request('status') || request('status')=='all')>Tous les statuts</option>
                <option value="scheduled" @selected(request('status')=='scheduled')>Planifié</option>
                <option value="confirmed" @selected(request('status')=='confirmed')>Confirmé</option>
                <option value="completed" @selected(request('status')=='completed')>Effectué</option>
                <option value="missed"    @selected(request('status')=='missed')>Manqué</option>
                <option value="cancelled" @selected(request('status')=='cancelled')>Annulé</option>
            </select>

            <select name="type" class="filter-input">
                <option value="all" @selected(!request('type') || request('type')=='all')>Tous types</option>
                <option value="exterior" @selected(request('type')=='exterior')>Extérieur</option>
                <option value="interior" @selected(request('type')=='interior')>Intérieur</option>
                <option value="full"     @selected(request('type')=='full')>Complet</option>
            </select>

            <input type="week" name="week" value="{{ request('week') }}" class="filter-input" title="Filtrer par semaine">

            <label style="display:flex;align-items:center;gap:.4rem;font-size:.82rem;color:#64748b;cursor:pointer;white-space:nowrap;">
                <input type="checkbox" name="archived" value="1" @checked(request()->boolean('archived'))
                       onchange="this.form.submit()">
                Archives
            </label>

            <button type="submit" class="btn btn-primary" style="padding:.45rem .85rem;">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Filtrer
            </button>
            @if(request()->anyFilled(['q','status','type','week','archived']))
            <a href="{{ route('cleanings.index') }}" class="btn btn-ghost" style="padding:.45rem .8rem;">Réinitialiser</a>
            @endif
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Véhicule</th>
                    <th>Date prévue</th>
                    <th>Heure</th>
                    <th>Type</th>
                    <th>Responsable</th>
                    <th>Statut</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cleanings as $cleaning)
                @php
                    $sc = $cleaning->getStatusColor();
                    $typeColors = ['exterior'=>['#3b82f6','#eff6ff'],'interior'=>['#8b5cf6','#faf5ff'],'full'=>['#10b981','#f0fdf4']];
                    $tc = $typeColors[$cleaning->cleaning_type] ?? ['#64748b','#f8fafc'];
                @endphp
                <tr style="{{ $showArchived ? 'opacity:.6;' : '' }}">
                    <td>
                        <div class="vehicle-cell">
                            @if($cleaning->vehicle?->profilePhoto)
                                <img src="{{ Storage::url($cleaning->vehicle->profilePhoto->file_path) }}" class="vehicle-thumb" alt="">
                            @else
                                <div class="vehicle-thumb-ph">
                                    <svg width="16" height="13" fill="none" viewBox="0 0 24 18"><path d="M3 13h2l1-3h12l1 3h2" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/><circle cx="7.5" cy="14.5" r="1.5" stroke="#94a3b8" stroke-width="1.5"/><circle cx="16.5" cy="14.5" r="1.5" stroke="#94a3b8" stroke-width="1.5"/></svg>
                                </div>
                            @endif
                            <div>
                                <div style="font-weight:600;color:#0f172a;font-size:.85rem;">{{ $cleaning->vehicle?->brand }} {{ $cleaning->vehicle?->model }}</div>
                                <div style="font-size:.72rem;color:#94a3b8;font-family:monospace;">
                                    {{ $cleaning->vehicle?->plate }}
                                    @if($showArchived)
                                        <span class="badge" style="background:#f1f5f9;color:#64748b;margin-left:.3rem;">Archivé</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="font-weight:600;font-size:.85rem;">{{ $cleaning->scheduled_date->translatedFormat('D d M Y') }}</div>
                        @if($cleaning->scheduled_date->isToday())
                            <span class="badge" style="background:#fef3c7;color:#d97706;">Aujourd'hui</span>
                        @elseif($cleaning->scheduled_date->isTomorrow())
                            <span class="badge" style="background:#eff6ff;color:#3b82f6;">Demain</span>
                        @elseif($cleaning->scheduled_date->isPast() && !in_array($cleaning->status, ['completed','cancelled']))
                            <span class="badge" style="background:#fef2f2;color:#ef4444;">Dépassé</span>
                        @endif
                    </td>
                    <td style="font-family:monospace;font-size:.85rem;">{{ $cleaning->scheduled_time }}</td>
                    <td>
                        <span class="badge" style="background:{{ $tc[1] }};color:{{ $tc[0] }};">
                            {{ $cleaning->getTypeLabel() }}
                        </span>
                    </td>
                    <td>
                        <div style="font-size:.85rem;font-weight:500;">{{ $cleaning->getResponsibleName() }}</div>
                        @if($cleaning->driver)
                            <span class="badge" style="background:#f0fdf4;color:#16a34a;">Chauffeur</span>
                        @elseif($cleaning->responsible)
                            <span class="badge" style="background:#f5f3ff;color:#7c3aed;">Collaborateur</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge" style="background:{{ $sc[1] }};color:{{ $sc[0] }};">
                            <span style="width:5px;height:5px;border-radius:50%;background:{{ $sc[0] }};display:inline-block;"></span>
                            {{ $cleaning->getStatusLabel() }}
                        </span>
                    </td>
                    <td style="text-align:right;">
                        <div style="display:flex;justify-content:flex-end;gap:.35rem;">
                            @if(!$showArchived)
                            <a href="{{ route('cleanings.show', $cleaning) }}" class="btn btn-ghost" style="padding:.35rem .6rem;" title="Détail">
                                <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
                            </a>
                            @can('cleanings.edit')
                            @if(!in_array($cleaning->status, ['completed','cancelled']))
                            <a href="{{ route('cleanings.edit', $cleaning) }}" class="btn btn-ghost" style="padding:.35rem .6rem;" title="Modifier">
                                <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            </a>
                            @endif
                            @endcan
                            @can('cleanings.delete')
                            <form method="POST" action="{{ route('cleanings.destroy', $cleaning) }}"
                                  data-confirm="Archiver ce nettoyage ?"
                                  data-title="Archiver" data-btn-text="Archiver">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-ghost" style="padding:.35rem .6rem;color:#64748b;" title="Archiver">
                                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M21 8v13H3V8M1 3h22v5H1zM10 12h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                </button>
                            </form>
                            @endcan
                            @else
                            {{-- Mode archives : bouton restaurer --}}
                            @can('cleanings.delete')
                            <form method="POST" action="{{ route('cleanings.restore', $cleaning->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-ghost" style="padding:.35rem .7rem;color:#10b981;font-size:.78rem;" title="Restaurer">
                                    Restaurer
                                </button>
                            </form>
                            @endcan
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;padding:2.5rem;color:#94a3b8;">Aucun nettoyage trouvé.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($cleanings->hasPages())
    <div class="pagination-wrap">
        <span>{{ $cleanings->firstItem() }}–{{ $cleanings->lastItem() }} sur {{ $cleanings->total() }}</span>
        {{ $cleanings->links() }}
    </div>
    @endif
</div>
@endsection
