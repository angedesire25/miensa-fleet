@extends('layouts.dashboard')
@section('title', 'Affectation #' . $assignment->id)
@section('page-title', 'Détail affectation')

@section('content')
@php
$statusMap = ['planned'=>['Planifiée','#6366f1','#f0f0ff'],'confirmed'=>['Confirmée','#0891b2','#ecfeff'],'in_progress'=>['En cours','#3b82f6','#eff6ff'],'completed'=>['Terminée','#10b981','#f0fdf4'],'cancelled'=>['Annulée','#64748b','#f8fafc']];
$typeMap   = ['mission'=>'Mission','daily'=>'Journée','permanent'=>'Permanente','replacement'=>'Remplacement','trial'=>'Essai'];
$condMap   = ['good'=>['Bon','#10b981','#f0fdf4'],'fair'=>['Moyen','#d97706','#fffbeb'],'poor'=>['Mauvais','#ef4444','#fef2f2']];
$s = $statusMap[$assignment->status] ?? ['Inconnu','#64748b','#f8fafc'];
@endphp

<style>
.card{background:#fff;border-radius:.75rem;border:1px solid #e2e8f0;overflow:hidden;margin-bottom:1rem;}
.card-head{padding:.85rem 1.25rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:.6rem;}
.card-title{font-size:.875rem;font-weight:700;color:#0f172a;}
.card-body{padding:1.25rem 1.5rem;}
.dl{display:grid;grid-template-columns:160px 1fr;gap:.55rem .75rem;}
.dt{font-size:.775rem;color:#94a3b8;font-weight:500;}
.dd{font-size:.85rem;color:#0f172a;font-weight:500;}
.badge{display:inline-flex;align-items:center;gap:.25rem;padding:.2rem .6rem;border-radius:99px;font-size:.72rem;font-weight:600;}
.btn{padding:.5rem 1rem;border-radius:.45rem;font-size:.82rem;font-weight:600;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;transition:opacity .15s;}
.btn-primary{background:linear-gradient(135deg,#10b981,#059669);color:#fff;}
.btn-blue{background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff;}
.btn-indigo{background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;}
.btn-ghost{background:#f8fafc;color:#374151;border:1.5px solid #e2e8f0;}
.btn-ghost:hover{background:#f1f5f9;}
.btn-danger{background:#fef2f2;color:#dc2626;border:1.5px solid #fecaca;}
.btn-warning{background:#fffbeb;color:#92400e;border:1.5px solid #fde68a;}
.form-input{width:100%;padding:.5rem .75rem;border:1.5px solid #e2e8f0;border-radius:.45rem;font-size:.83rem;background:#fff;color:#0f172a;outline:none;}
.form-input:focus{border-color:#10b981;}
.form-label{font-size:.78rem;font-weight:600;color:#374151;margin-bottom:.3rem;display:block;}
.form-group{margin-bottom:.85rem;}
.section-sep{height:1px;background:#f1f5f9;margin:1rem 0;}
</style>

{{-- Breadcrumb --}}
<div style="display:flex;align-items:center;gap:.5rem;font-size:.825rem;color:#94a3b8;margin-bottom:1.25rem;">
    <a href="{{ route('assignments.index') }}" style="color:#10b981;text-decoration:none;font-weight:500;">Affectations</a>
    <span>›</span>
    <span style="color:#374151;">Affectation #{{ $assignment->id }}</span>
</div>

<div style="display:grid;grid-template-columns:280px 1fr;gap:1.25rem;align-items:start;">

    {{-- ── Colonne gauche ──────────────────────────────────────────────────── --}}
    <div>

        {{-- Statut & identité --}}
        <div class="card">
            <div class="card-body" style="padding:1.5rem;text-align:center;">
                {{-- Icône type --}}
                <div style="width:56px;height:56px;border-radius:.65rem;background:{{ $s[2] }};display:flex;align-items:center;justify-content:center;margin:0 auto .85rem;">
                    <svg width="24" height="24" fill="none" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2" stroke="{{ $s[1] }}" stroke-width="2"/><path d="M8 2v4M16 2v4M3 10h18" stroke="{{ $s[1] }}" stroke-width="2" stroke-linecap="round"/></svg>
                </div>
                <div style="font-size:1.05rem;font-weight:800;color:#0f172a;margin-bottom:.25rem;">Affectation #{{ $assignment->id }}</div>
                <div style="margin-bottom:.6rem;">
                    <span class="badge" style="background:{{ $s[2] }};color:{{ $s[1] }};">
                        <span style="width:6px;height:6px;border-radius:50%;background:{{ $s[1] }};display:inline-block;"></span>
                        {{ $s[0] }}
                    </span>
                </div>
                <span class="badge" style="background:#f1f5f9;color:#374151;">{{ $typeMap[$assignment->type] ?? $assignment->type }}</span>
            </div>
        </div>

        {{-- Véhicule --}}
        <div class="card">
            <div class="card-head">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M3 17h2l1-3h12l1 3h2" stroke="#3b82f6" stroke-width="2" stroke-linecap="round"/><circle cx="7.5" cy="18.5" r="1.5" stroke="#3b82f6" stroke-width="1.5"/><circle cx="16.5" cy="18.5" r="1.5" stroke="#3b82f6" stroke-width="1.5"/></svg>
                <span class="card-title">Véhicule</span>
            </div>
            <div class="card-body">
                @if($assignment->vehicle?->profilePhoto)
                    <img src="{{ Storage::url($assignment->vehicle->profilePhoto->path) }}"
                         style="width:100%;height:100px;object-fit:cover;border-radius:.45rem;margin-bottom:.75rem;" alt="">
                @else
                    <div style="width:100%;height:80px;background:#f1f5f9;border-radius:.45rem;display:flex;align-items:center;justify-content:center;margin-bottom:.75rem;">
                        <svg width="28" height="28" fill="none" viewBox="0 0 24 24"><path d="M3 17h2l1-3h12l1 3h2" stroke="#cbd5e1" stroke-width="2"/><circle cx="7.5" cy="18.5" r="1.5" stroke="#cbd5e1" stroke-width="1.5"/><circle cx="16.5" cy="18.5" r="1.5" stroke="#cbd5e1" stroke-width="1.5"/></svg>
                    </div>
                @endif
                <div style="font-family:monospace;font-size:.9rem;font-weight:700;color:#0f172a;background:#f1f5f9;padding:.2rem .5rem;border-radius:.3rem;display:inline-block;margin-bottom:.3rem;">
                    {{ $assignment->vehicle->plate ?? '—' }}
                </div>
                <div style="font-size:.82rem;color:#374151;font-weight:500;">{{ $assignment->vehicle?->brand }} {{ $assignment->vehicle?->model }}</div>
                <div style="font-size:.76rem;color:#94a3b8;margin-top:.15rem;">{{ $assignment->vehicle?->year }}</div>
                @if($assignment->vehicle)
                    <a href="{{ route('vehicles.show', $assignment->vehicle) }}" class="btn btn-ghost" style="width:100%;justify-content:center;margin-top:.75rem;padding:.4rem .75rem;">
                        Voir la fiche véhicule
                    </a>
                @endif
            </div>
        </div>

        {{-- Chauffeur --}}
        <div class="card">
            <div class="card-head">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4" stroke="#10b981" stroke-width="2"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke="#10b981" stroke-width="2" stroke-linecap="round"/></svg>
                <span class="card-title">Chauffeur</span>
            </div>
            <div class="card-body">
                <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.75rem;">
                    @if($assignment->driver?->avatar)
                        <img src="{{ Storage::url($assignment->driver->avatar) }}"
                             style="width:44px;height:44px;border-radius:50%;object-fit:cover;flex-shrink:0;" alt="">
                    @else
                        <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#10b981,#059669);display:flex;align-items:center;justify-content:center;font-size:.9rem;font-weight:700;color:#fff;flex-shrink:0;">
                            {{ strtoupper(substr($assignment->driver->full_name ?? 'D', 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <div style="font-weight:600;font-size:.88rem;color:#0f172a;">{{ $assignment->driver->full_name ?? '—' }}</div>
                        <div style="font-family:monospace;font-size:.75rem;color:#64748b;">{{ $assignment->driver->matricule ?? '' }}</div>
                    </div>
                </div>
                @if($assignment->driver)
                    <a href="{{ route('drivers.show', $assignment->driver) }}" class="btn btn-ghost" style="width:100%;justify-content:center;padding:.4rem .75rem;">
                        Voir la fiche chauffeur
                    </a>
                @endif
            </div>
        </div>

        {{-- Créé / Validé par --}}
        <div class="card">
            <div class="card-body" style="font-size:.8rem;color:#64748b;">
                <div style="margin-bottom:.4rem;"><span style="font-weight:600;color:#374151;">Créée par :</span> {{ $assignment->createdBy?->name ?? '—' }}</div>
                <div style="margin-bottom:.4rem;"><span style="font-weight:600;color:#374151;">Créée le :</span> {{ $assignment->created_at->isoFormat('D MMM YYYY, H:mm') }}</div>
                @if($assignment->validated_at)
                    <div style="margin-bottom:.4rem;"><span style="font-weight:600;color:#374151;">Confirmée par :</span> {{ $assignment->validatedBy?->name ?? '—' }}</div>
                    <div><span style="font-weight:600;color:#374151;">Confirmée le :</span> {{ $assignment->validated_at->isoFormat('D MMM YYYY, H:mm') }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Colonne droite ───────────────────────────────────────────────────── --}}
    <div>

        {{-- Actions selon statut --}}
        @if(!in_array($assignment->status, ['completed', 'cancelled']))
        <div class="card" style="border-left:4px solid {{ $s[1] }};">
            <div class="card-head" style="justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:.6rem;">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" stroke="{{ $s[1] }}" stroke-width="2" stroke-linecap="round"/></svg>
                    <span class="card-title">Actions disponibles</span>
                </div>
                <span class="badge" style="background:{{ $s[2] }};color:{{ $s[1] }};">{{ $s[0] }}</span>
            </div>
            <div class="card-body">

                {{-- planned → confirmed --}}
                @if($assignment->status === 'planned')
                @can('assignments.edit')
                <div style="display:flex;flex-wrap:wrap;gap:.65rem;align-items:flex-start;">
                    <form method="POST" action="{{ route('assignments.confirm', $assignment) }}"
                          data-confirm="Confirmer cette affectation ? Le bon de sortie sera prêt."
                          data-title="Confirmer l'affectation"
                          data-btn-text="Oui, confirmer"
                          data-btn-color="#6366f1"
                          style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-indigo">
                            <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                            Confirmer l'affectation
                        </button>
                    </form>
                @endcan
                @endif

                {{-- planned|confirmed → in_progress --}}
                @if(in_array($assignment->status, ['planned', 'confirmed']))
                @can('assignments.edit')
                    <button type="button" class="btn btn-blue" onclick="document.getElementById('modal-start').style.display='flex'">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                        Enregistrer le départ
                    </button>
                @endcan
                @endif

                {{-- in_progress → completed --}}
                @if($assignment->status === 'in_progress')
                @can('assignments.edit')
                <div style="display:flex;flex-wrap:wrap;gap:.65rem;">
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('modal-complete').style.display='flex'">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/></svg>
                        Enregistrer le retour
                    </button>
                @endcan
                @endif

                {{-- Cancel --}}
                @can('assignments.edit')
                @if(!isset($startFormOpen))
                    <button type="button" class="btn btn-danger" onclick="document.getElementById('modal-cancel').style.display='flex'">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                        Annuler l'affectation
                    </button>
                @endif
                @endcan

                @if(in_array($assignment->status, ['planned', 'confirmed']))
                </div>
                @elseif($assignment->status === 'in_progress')
                </div>
                @endif

            </div>
        </div>
        @endif

        {{-- Informations générales --}}
        <div class="card">
            <div class="card-head">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2" stroke="#10b981" stroke-width="2"/><path d="M8 2v4M16 2v4M3 10h18" stroke="#10b981" stroke-width="2" stroke-linecap="round"/></svg>
                <span class="card-title">Informations générales</span>
                @can('assignments.edit')
                @if(!in_array($assignment->status, ['completed','cancelled']))
                    <a href="{{ route('assignments.edit', $assignment) }}" class="btn btn-ghost" style="margin-left:auto;padding:.3rem .65rem;font-size:.75rem;">
                        <svg width="11" height="11" fill="none" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="2"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2"/></svg>
                        Modifier
                    </a>
                @endif
                @endcan
            </div>
            <div class="card-body">
                <div class="dl">
                    <div class="dt">Type</div>
                    <div class="dd"><span class="badge" style="background:#f1f5f9;color:#374151;">{{ $typeMap[$assignment->type] ?? $assignment->type }}</span></div>

                    <div class="dt">Date de départ</div>
                    <div class="dd">{{ $assignment->datetime_start->isoFormat('dddd D MMMM YYYY') }} à {{ $assignment->datetime_start->format('H:i') }}</div>

                    <div class="dt">Retour prévu</div>
                    <div class="dd">{{ $assignment->datetime_end_planned->isoFormat('dddd D MMMM YYYY') }} à {{ $assignment->datetime_end_planned->format('H:i') }}</div>

                    @if($assignment->datetime_end_actual)
                    <div class="dt">Retour effectif</div>
                    <div class="dd" style="color:#10b981;font-weight:600;">{{ $assignment->datetime_end_actual->isoFormat('D MMM YYYY, H:mm') }}</div>
                    @endif

                    @if($assignment->destination)
                    <div class="dt">Destination</div>
                    <div class="dd">{{ $assignment->destination }}</div>
                    @endif

                    @if($assignment->mission)
                    <div class="dt">Mission / objet</div>
                    <div class="dd">{{ $assignment->mission }}</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Kilométrage --}}
        <div class="card">
            <div class="card-head">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke="#3b82f6" stroke-width="2"/><path d="M12 7v5l3 3" stroke="#3b82f6" stroke-width="2" stroke-linecap="round"/></svg>
                <span class="card-title">Kilométrage</span>
            </div>
            <div class="card-body">
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;text-align:center;">
                    <div style="padding:.85rem;background:#f8fafc;border-radius:.5rem;">
                        <div style="font-size:1.1rem;font-weight:800;color:#0f172a;">{{ $assignment->km_start ? number_format($assignment->km_start) : '—' }}</div>
                        <div style="font-size:.72rem;color:#64748b;margin-top:.15rem;">Km départ</div>
                    </div>
                    <div style="padding:.85rem;background:#f8fafc;border-radius:.5rem;">
                        <div style="font-size:1.1rem;font-weight:800;color:#0f172a;">{{ $assignment->km_end ? number_format($assignment->km_end) : '—' }}</div>
                        <div style="font-size:.72rem;color:#64748b;margin-top:.15rem;">Km retour</div>
                    </div>
                    <div style="padding:.85rem;background:{{ $assignment->km_total ? '#f0fdf4' : '#f8fafc' }};border-radius:.5rem;">
                        <div style="font-size:1.1rem;font-weight:800;color:{{ $assignment->km_total ? '#10b981' : '#0f172a' }};">{{ $assignment->km_total ? number_format($assignment->km_total) : '—' }}</div>
                        <div style="font-size:.72rem;color:#64748b;margin-top:.15rem;">Km parcourus</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- État du véhicule --}}
        @if($assignment->condition_start || $assignment->condition_end)
        <div class="card">
            <div class="card-head">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke="#d97706" stroke-width="2"/></svg>
                <span class="card-title">État du véhicule</span>
            </div>
            <div class="card-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    @if($assignment->condition_start)
                    @php $cs = $condMap[$assignment->condition_start] ?? ['—','#64748b','#f8fafc']; @endphp
                    <div>
                        <div style="font-size:.75rem;font-weight:600;color:#94a3b8;margin-bottom:.4rem;text-transform:uppercase;letter-spacing:.05em;">Au départ</div>
                        <span class="badge" style="background:{{ $cs[2] }};color:{{ $cs[1] }};margin-bottom:.4rem;">{{ $cs[0] }}</span>
                        @if($assignment->condition_start_notes)
                            <div style="font-size:.8rem;color:#64748b;font-style:italic;">{{ $assignment->condition_start_notes }}</div>
                        @endif
                    </div>
                    @endif
                    @if($assignment->condition_end)
                    @php $ce = $condMap[$assignment->condition_end] ?? ['—','#64748b','#f8fafc']; @endphp
                    <div>
                        <div style="font-size:.75rem;font-weight:600;color:#94a3b8;margin-bottom:.4rem;text-transform:uppercase;letter-spacing:.05em;">Au retour</div>
                        <span class="badge" style="background:{{ $ce[2] }};color:{{ $ce[1] }};margin-bottom:.4rem;">{{ $ce[0] }}</span>
                        @if($assignment->condition_end_notes)
                            <div style="font-size:.8rem;color:#64748b;font-style:italic;">{{ $assignment->condition_end_notes }}</div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Annulation --}}
        @if($assignment->status === 'cancelled' && $assignment->cancellation_reason)
        <div style="padding:.85rem 1.1rem;background:#fef2f2;border:1px solid #fecaca;border-radius:.65rem;margin-bottom:1rem;display:flex;gap:.65rem;align-items:flex-start;">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:.1rem;"><circle cx="12" cy="12" r="9" stroke="#ef4444" stroke-width="2"/><path d="M15 9l-6 6M9 9l6 6" stroke="#ef4444" stroke-width="2" stroke-linecap="round"/></svg>
            <div>
                <div style="font-size:.8rem;font-weight:700;color:#dc2626;margin-bottom:.2rem;">Motif d'annulation</div>
                <div style="font-size:.83rem;color:#7f1d1d;">{{ $assignment->cancellation_reason }}</div>
            </div>
        </div>
        @endif

        {{-- Actions de suppression --}}
        @if($assignment->status !== 'in_progress')
        @can('assignments.delete')
        <div class="card">
            <div class="card-body" style="padding:.85rem 1.25rem;">
                <form method="POST" action="{{ route('assignments.destroy', $assignment) }}"
                      data-confirm="Supprimer définitivement cette affectation ?"
                      data-title="Supprimer l'affectation"
                      data-btn-text="Supprimer"
                      data-btn-color="#ef4444">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger" style="font-size:.78rem;padding:.38rem .75rem;">
                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24"><polyline points="3,6 5,6 21,6" stroke="currentColor" stroke-width="2"/><path d="M19 6l-1 14H6L5 6" stroke="currentColor" stroke-width="2"/><path d="M10 11v6M14 11v6M9 6V4h6v2" stroke="currentColor" stroke-width="2"/></svg>
                        Supprimer l'affectation
                    </button>
                </form>
            </div>
        </div>
        @endcan
        @endif

    </div>
</div>

{{-- ── MODAL : Enregistrer le départ ─────────────────────────────────────── --}}
@can('assignments.edit')
@if(in_array($assignment->status, ['planned', 'confirmed']))
<div id="modal-start" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.45);z-index:100;align-items:center;justify-content:center;" onclick="if(event.target===this)this.style.display='none'">
    <div style="background:#fff;border-radius:.85rem;width:460px;max-width:94vw;box-shadow:0 20px 60px rgba(0,0,0,.2);">
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
            <div style="font-size:.95rem;font-weight:700;color:#0f172a;">Enregistrer le départ</div>
            <button onclick="document.getElementById('modal-start').style.display='none'" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:1.3rem;line-height:1;">×</button>
        </div>
        <form method="POST" action="{{ route('assignments.start', $assignment) }}" style="padding:1.25rem 1.5rem;">
            @csrf
            <div class="form-group">
                <label class="form-label">Kilométrage au départ <span style="color:#ef4444;">*</span></label>
                <input type="number" name="km_start" class="form-input" placeholder="ex: 45800" min="0" required value="{{ old('km_start', $assignment->vehicle?->km_current) }}">
            </div>
            <div class="form-group">
                <label class="form-label">État du véhicule au départ <span style="color:#ef4444;">*</span></label>
                <select name="condition_start" class="form-input" required>
                    <option value="">— Sélectionner —</option>
                    <option value="good" @selected(old('condition_start')==='good')>Bon état</option>
                    <option value="fair" @selected(old('condition_start')==='fair')>État moyen</option>
                    <option value="poor" @selected(old('condition_start')==='poor')>Mauvais état</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:1.25rem;">
                <label class="form-label">Observations (optionnel)</label>
                <textarea name="condition_start_notes" class="form-input" rows="2" placeholder="Rayures, bosses, carburant…">{{ old('condition_start_notes') }}</textarea>
            </div>
            <div style="display:flex;gap:.65rem;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('modal-start').style.display='none'" class="btn btn-ghost">Annuler</button>
                <button type="submit" class="btn btn-blue">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                    Confirmer le départ
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endcan

{{-- ── MODAL : Enregistrer le retour ─────────────────────────────────────── --}}
@can('assignments.edit')
@if($assignment->status === 'in_progress')
<div id="modal-complete" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.45);z-index:100;align-items:center;justify-content:center;" onclick="if(event.target===this)this.style.display='none'">
    <div style="background:#fff;border-radius:.85rem;width:460px;max-width:94vw;box-shadow:0 20px 60px rgba(0,0,0,.2);">
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
            <div style="font-size:.95rem;font-weight:700;color:#0f172a;">Enregistrer le retour</div>
            <button onclick="document.getElementById('modal-complete').style.display='none'" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:1.3rem;line-height:1;">×</button>
        </div>
        <form method="POST" action="{{ route('assignments.complete', $assignment) }}" style="padding:1.25rem 1.5rem;">
            @csrf
            @if($assignment->km_start)
            <div style="padding:.6rem .85rem;background:#eff6ff;border-radius:.45rem;font-size:.8rem;color:#1d4ed8;margin-bottom:.85rem;">
                Km au départ : <strong>{{ number_format($assignment->km_start) }} km</strong>
            </div>
            @endif
            <div class="form-group">
                <label class="form-label">Kilométrage au retour <span style="color:#ef4444;">*</span></label>
                <input type="number" name="km_end" class="form-input" placeholder="ex: 46250" min="{{ $assignment->km_start ?? 0 }}" required value="{{ old('km_end') }}">
            </div>
            <div class="form-group">
                <label class="form-label">État du véhicule au retour <span style="color:#ef4444;">*</span></label>
                <select name="condition_end" class="form-input" required>
                    <option value="">— Sélectionner —</option>
                    <option value="good" @selected(old('condition_end')==='good')>Bon état</option>
                    <option value="fair" @selected(old('condition_end')==='fair')>État moyen</option>
                    <option value="poor" @selected(old('condition_end')==='poor')>Mauvais état</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:1.25rem;">
                <label class="form-label">Observations (optionnel)</label>
                <textarea name="condition_end_notes" class="form-input" rows="2" placeholder="Dommages constatés, carburant…">{{ old('condition_end_notes') }}</textarea>
            </div>
            <div style="display:flex;gap:.65rem;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('modal-complete').style.display='none'" class="btn btn-ghost">Annuler</button>
                <button type="submit" class="btn btn-primary">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/></svg>
                    Valider le retour
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endcan

{{-- ── MODAL : Annuler l'affectation ──────────────────────────────────────── --}}
@can('assignments.edit')
<div id="modal-cancel" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.45);z-index:100;align-items:center;justify-content:center;" onclick="if(event.target===this)this.style.display='none'">
    <div style="background:#fff;border-radius:.85rem;width:440px;max-width:94vw;box-shadow:0 20px 60px rgba(0,0,0,.2);">
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
            <div style="font-size:.95rem;font-weight:700;color:#dc2626;">Annuler l'affectation</div>
            <button onclick="document.getElementById('modal-cancel').style.display='none'" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:1.3rem;line-height:1;">×</button>
        </div>
        <form method="POST" action="{{ route('assignments.cancel', $assignment) }}" style="padding:1.25rem 1.5rem;">
            @csrf
            <div class="form-group" style="margin-bottom:1.25rem;">
                <label class="form-label">Motif d'annulation (optionnel)</label>
                <textarea name="cancellation_reason" class="form-input" rows="3" placeholder="Raison de l'annulation…">{{ old('cancellation_reason') }}</textarea>
            </div>
            <div style="display:flex;gap:.65rem;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('modal-cancel').style.display='none'" class="btn btn-ghost">Retour</button>
                <button type="submit" class="btn btn-danger">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                    Confirmer l'annulation
                </button>
            </div>
        </form>
    </div>
</div>
@endcan

{{-- Rouvrir les modals en cas d'erreur de validation --}}
@if($errors->has('km_start') || $errors->has('condition_start'))
<script>document.addEventListener('DOMContentLoaded',()=>document.getElementById('modal-start').style.display='flex');</script>
@endif
@if($errors->has('km_end') || $errors->has('condition_end'))
<script>document.addEventListener('DOMContentLoaded',()=>document.getElementById('modal-complete').style.display='flex');</script>
@endif

@endsection
