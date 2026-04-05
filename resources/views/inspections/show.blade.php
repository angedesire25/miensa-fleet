@extends('layouts.dashboard')
@section('title', 'Fiche de contrôle #' . $inspection->id)
@section('page-title', 'Fiche de contrôle')

@section('content')
@php
$typeMap   = ['departure'=>['Départ','#3b82f6','#eff6ff'],'return'=>['Retour','#10b981','#f0fdf4'],'routine'=>['Routine','#6366f1','#f0f0ff']];
$statusMap = ['draft'=>['Brouillon','#94a3b8','#f8fafc'],'submitted'=>['Soumise','#d97706','#fffbeb'],'validated'=>['Validée','#10b981','#f0fdf4'],'rejected'=>['À corriger','#ef4444','#fef2f2']];
$levelMap  = ['low'=>['Faible','#ef4444','#fef2f2'],'medium'=>['Normal','#d97706','#fffbeb'],'high'=>['Plein / OK','#10b981','#f0fdf4']];
$psiMap    = ['low'=>['Basse','#ef4444','#fef2f2'],'medium'=>['Moyenne','#d97706','#fffbeb'],'ok'=>['Correcte','#10b981','#f0fdf4']];
$condMap   = ['ok'=>['OK','#10b981','#f0fdf4'],'minor_issue'=>['Anomalie','#d97706','#fffbeb'],'critical'=>['Critique','#ef4444','#fef2f2']];
$docMap    = ['present'=>['Présent / À jour','#10b981','#f0fdf4'],'absent'=>['Absent','#ef4444','#fef2f2'],'expired'=>['Expiré','#ef4444','#fef2f2']];
$oilChgMap = ['ok'=>['OK','#10b981','#f0fdf4'],'due_soon'=>['Bientôt','#d97706','#fffbeb'],'overdue'=>['Dépassée','#ef4444','#fef2f2']];
$t  = $typeMap[$inspection->inspection_type]   ?? ['—','#64748b','#f8fafc'];
$st = $statusMap[$inspection->status]           ?? ['—','#94a3b8','#f8fafc'];
$fuelColor = $inspection->fuel_level_pct >= 50 ? '#10b981' : ($inspection->fuel_level_pct >= 25 ? '#d97706' : '#ef4444');
@endphp

<style>
.card{background:#fff;border-radius:.75rem;border:1px solid #e2e8f0;overflow:hidden;margin-bottom:1rem;}
.card-head{padding:.85rem 1.25rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:.6rem;}
.card-title{font-size:.875rem;font-weight:700;color:#0f172a;}
.card-body{padding:1.1rem 1.5rem;}
.badge{display:inline-flex;align-items:center;gap:.25rem;padding:.2rem .6rem;border-radius:99px;font-size:.72rem;font-weight:600;}
.btn{padding:.5rem 1rem;border-radius:.45rem;font-size:.82rem;font-weight:600;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;transition:opacity .15s;}
.btn-primary{background:linear-gradient(135deg,#10b981,#059669);color:#fff;}
.btn-ghost{background:#f8fafc;color:#374151;border:1.5px solid #e2e8f0;}
.btn-ghost:hover{background:#f1f5f9;}
.btn-danger{background:#fef2f2;color:#dc2626;border:1.5px solid #fecaca;}
.btn-warning{background:#fffbeb;color:#92400e;border:1.5px solid #fde68a;}
.form-input{width:100%;padding:.5rem .75rem;border:1.5px solid #e2e8f0;border-radius:.45rem;font-size:.83rem;background:#fff;color:#0f172a;outline:none;}
.form-input:focus{border-color:#10b981;}
.form-label{font-size:.78rem;font-weight:600;color:#374151;margin-bottom:.3rem;display:block;}
.ctrl-row{display:flex;align-items:center;justify-content:space-between;padding:.55rem 0;border-bottom:1px solid #f8fafc;}
.ctrl-row:last-child{border-bottom:none;}
.ctrl-name{font-size:.82rem;color:#374151;font-weight:500;}
.ctrl-note-txt{font-size:.75rem;color:#64748b;margin-top:.15rem;font-style:italic;}
</style>

{{-- Breadcrumb --}}
<div style="display:flex;align-items:center;gap:.5rem;font-size:.825rem;color:#94a3b8;margin-bottom:1.25rem;">
    <a href="{{ route('inspections.index') }}" style="color:#10b981;text-decoration:none;font-weight:500;">Contrôles</a>
    <span>›</span>
    <span style="color:#374151;">Fiche #{{ $inspection->id }}</span>
</div>

{{-- Bandeau archivage --}}
@if($inspection->isArchived())
<div style="padding:.75rem 1.1rem;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:.65rem;margin-bottom:1.1rem;display:flex;gap:.65rem;align-items:center;">
    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" style="flex-shrink:0;"><path d="M21 8v13H3V8M1 3h22v5H1zM10 12h4" stroke="#64748b" stroke-width="2" stroke-linecap="round"/></svg>
    <div style="font-size:.83rem;color:#64748b;">
        Cette fiche est <strong>archivée</strong> depuis le {{ $inspection->archived_at->isoFormat('D MMMM YYYY') }} — elle est masquée des listes actives.
    </div>
</div>
@endif

@if($inspection->has_critical_issue)
<div style="padding:.85rem 1.1rem;background:#fef2f2;border:1px solid #fca5a5;border-radius:.65rem;margin-bottom:1.25rem;display:flex;gap:.65rem;align-items:center;">
    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" style="flex-shrink:0;"><path d="M12 9v4M12 17h.01" stroke="#dc2626" stroke-width="2" stroke-linecap="round"/><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="#dc2626" stroke-width="2"/></svg>
    <div>
        <div style="font-size:.85rem;font-weight:700;color:#dc2626;">⚠ Anomalie critique détectée</div>
        <div style="font-size:.8rem;color:#7f1d1d;margin-top:.1rem;">
            Ce véhicule présente au moins un point de contrôle critique.
            @if($inspection->oil_level === 'low') Huile moteur faible. @endif
            @if($inspection->brakes_status === 'critical') Freinage critique. @endif
            @if($inspection->lights_status === 'critical') Éclairage critique. @endif
        </div>
    </div>
</div>
@endif

<div style="display:grid;grid-template-columns:270px 1fr;gap:1.25rem;align-items:start;">

    {{-- ── Colonne gauche ──────────────────────────────────────────────────── --}}
    <div>
        {{-- Identité de la fiche --}}
        <div class="card">
            <div class="card-body" style="padding:1.5rem;text-align:center;">
                <div style="width:52px;height:52px;border-radius:.65rem;background:{{ $st[2] }};display:flex;align-items:center;justify-content:center;margin:0 auto .75rem;">
                    <svg width="22" height="22" fill="none" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke="{{ $st[1] }}" stroke-width="2" stroke-linecap="round"/><rect x="9" y="3" width="6" height="4" rx="1" stroke="{{ $st[1] }}" stroke-width="2"/><path d="M9 12l2 2 4-4" stroke="{{ $st[1] }}" stroke-width="2.5" stroke-linecap="round"/></svg>
                </div>
                <div style="font-size:.95rem;font-weight:800;color:#0f172a;margin-bottom:.3rem;">Fiche #{{ $inspection->id }}</div>
                <div style="margin-bottom:.35rem;">
                    <span class="badge" style="background:{{ $st[2] }};color:{{ $st[1] }};">
                        <span style="width:6px;height:6px;border-radius:50%;background:{{ $st[1] }};display:inline-block;"></span>
                        {{ $st[0] }}
                    </span>
                </div>
                <span class="badge" style="background:{{ $t[2] }};color:{{ $t[1] }};margin-top:.2rem;">{{ $t[0] }}</span>

                @if($inspection->has_critical_issue)
                    <div style="margin-top:.5rem;"><span class="badge" style="background:#fef2f2;color:#dc2626;">⚠ Critique</span></div>
                @endif
            </div>
        </div>

        {{-- Véhicule --}}
        <div class="card">
            <div class="card-head">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M3 17h2l1-3h12l1 3h2" stroke="#3b82f6" stroke-width="2" stroke-linecap="round"/><circle cx="7.5" cy="18.5" r="1.5" stroke="#3b82f6" stroke-width="1.5"/><circle cx="16.5" cy="18.5" r="1.5" stroke="#3b82f6" stroke-width="1.5"/></svg>
                <span class="card-title">Véhicule</span>
            </div>
            <div class="card-body">
                @if($inspection->vehicle?->profilePhoto)
                    <img src="{{ Storage::url($inspection->vehicle->profilePhoto->path) }}"
                         style="width:100%;height:80px;object-fit:cover;border-radius:.4rem;margin-bottom:.65rem;" alt="">
                @endif
                <div style="font-family:monospace;font-size:.9rem;font-weight:700;background:#f1f5f9;padding:.2rem .5rem;border-radius:.3rem;display:inline-block;margin-bottom:.3rem;">
                    {{ $inspection->vehicle?->plate ?? '—' }}
                </div>
                <div style="font-size:.82rem;color:#374151;">{{ $inspection->vehicle?->brand }} {{ $inspection->vehicle?->model }}</div>
                <a href="{{ route('vehicles.show', $inspection->vehicle) }}" class="btn btn-ghost" style="width:100%;justify-content:center;margin-top:.65rem;padding:.4rem;">Voir la fiche</a>
            </div>
        </div>

        {{-- Inspecteur & Chauffeur --}}
        <div class="card">
            <div class="card-head">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4" stroke="#10b981" stroke-width="2"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke="#10b981" stroke-width="2" stroke-linecap="round"/></svg>
                <span class="card-title">Personnes</span>
            </div>
            <div class="card-body" style="font-size:.82rem;">
                <div style="margin-bottom:.6rem;">
                    <div style="font-size:.73rem;color:#94a3b8;font-weight:500;margin-bottom:.15rem;">CONTRÔLEUR</div>
                    <div style="font-weight:600;color:#0f172a;">{{ $inspection->inspector?->name ?? '—' }}</div>
                </div>
                @if($inspection->driver)
                <div>
                    <div style="font-size:.73rem;color:#94a3b8;font-weight:500;margin-bottom:.15rem;">CHAUFFEUR</div>
                    <div style="font-weight:600;color:#0f172a;">{{ $inspection->driver->full_name }}</div>
                    <div style="color:#64748b;font-size:.76rem;">{{ $inspection->driver->matricule }}</div>
                </div>
                @endif
            </div>
        </div>

        {{-- Validation : réservée au fleet_manager, admin et super_admin --}}
        @if($inspection->status === 'submitted')
        @can('inspections.validate')
        <div class="card">
            <div class="card-head">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" stroke="#d97706" stroke-width="2" stroke-linecap="round"/></svg>
                <span class="card-title">Validation</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('inspections.validate', $inspection) }}"
                      data-confirm="Valider cette fiche de contrôle ?"
                      data-title="Valider la fiche"
                      data-btn-text="Valider"
                      data-btn-color="#10b981">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-bottom:.5rem;">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                        Valider la fiche
                    </button>
                </form>
                <button type="button" class="btn btn-warning" style="width:100%;justify-content:center;"
                        onclick="document.getElementById('modal-reject').style.display='flex'">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                    Renvoyer pour correction
                </button>
            </div>
        </div>
        @endcan

        {{-- Message pour contrôleur et autres : fiche en attente de validation --}}
        @cannot('inspections.validate')
        <div style="padding:.65rem 1rem;background:#fffbeb;border:1px solid #fde68a;border-radius:.65rem;font-size:.8rem;color:#92400e;text-align:center;">
            ⏳ Fiche soumise — en attente de validation par le responsable
        </div>
        @endcannot
        @endif

        @if($inspection->canEdit())
        @can('inspections.edit')
        <div class="card">
            <div class="card-body" style="padding:.85rem 1.25rem;display:flex;gap:.5rem;flex-wrap:wrap;">
                <a href="{{ route('inspections.edit', $inspection) }}" class="btn btn-ghost" style="flex:1;justify-content:center;">
                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="2"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2"/></svg>
                    Modifier
                </a>
                <form method="POST" action="{{ route('inspections.destroy', $inspection) }}"
                      data-confirm="Supprimer cette fiche ?"
                      data-btn-text="Supprimer"
                      data-btn-color="#ef4444">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger" style="padding:.45rem .65rem;">
                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24"><polyline points="3,6 5,6 21,6" stroke="currentColor" stroke-width="2"/><path d="M19 6l-1 14H6L5 6" stroke="currentColor" stroke-width="2"/></svg>
                    </button>
                </form>
            </div>
        </div>
        @endcan
        @endif

        @if($inspection->isValidated())
        <div style="padding:.65rem 1rem;background:#f0fdf4;border:1px solid #86efac;border-radius:.65rem;font-size:.8rem;color:#166534;text-align:center;margin-bottom:.6rem;">
            ✓ Fiche validée par <strong>{{ $inspection->validatedBy?->name }}</strong><br>
            <span style="font-size:.75rem;color:#15803d;">{{ $inspection->validated_at?->isoFormat('D MMM YYYY, H:mm') }}</span>
        </div>
        @endif

        {{-- Archivage : réservé aux gestionnaires (fleet_manager, admin+) --}}
        @can('inspections.validate')
        @if($inspection->isValidated() || $inspection->isArchived())
        <div class="card">
            <div class="card-body" style="padding:.85rem 1.25rem;">
                @if($inspection->isArchived())
                    {{-- Bandeau archivée --}}
                    <div style="padding:.5rem .75rem;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:.45rem;font-size:.78rem;color:#64748b;margin-bottom:.6rem;display:flex;align-items:center;gap:.4rem;">
                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24"><path d="M21 8v13H3V8M1 3h22v5H1zM10 12h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        Archivée le {{ $inspection->archived_at->isoFormat('D MMM YYYY') }}
                    </div>
                    <form method="POST" action="{{ route('inspections.unarchive', $inspection) }}"
                          data-confirm="Désarchiver cette fiche ?"
                          data-btn-text="Désarchiver"
                          data-btn-color="#10b981">
                        @csrf
                        <button type="submit" class="btn btn-ghost" style="width:100%;justify-content:center;">
                            <svg width="12" height="12" fill="none" viewBox="0 0 24 24"><path d="M21 8v13H3V8M1 3h22v5H1zM10 12h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            Désarchiver
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('inspections.archive', $inspection) }}"
                          data-confirm="Archiver cette fiche ? Elle sera masquée des listes."
                          data-btn-text="Archiver"
                          data-btn-color="#64748b">
                        @csrf
                        <button type="submit" class="btn btn-ghost" style="width:100%;justify-content:center;">
                            <svg width="12" height="12" fill="none" viewBox="0 0 24 24"><path d="M21 8v13H3V8M1 3h22v5H1zM10 12h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            Archiver la fiche
                        </button>
                    </form>
                @endif
            </div>
        </div>
        @endif
        @endcan
    </div>

    {{-- ── Colonne droite ───────────────────────────────────────────────────── --}}
    <div>

        {{-- En-tête récapitulatif --}}
        <div class="card">
            <div class="card-body" style="padding:1rem 1.5rem;">
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;text-align:center;">
                    <div style="padding:.65rem;background:#f8fafc;border-radius:.5rem;">
                        <div style="font-size:1rem;font-weight:800;color:#0f172a;">{{ $inspection->inspected_at->isoFormat('D MMM') }}</div>
                        <div style="font-size:.72rem;color:#64748b;">{{ $inspection->inspected_at->format('H:i') }}</div>
                    </div>
                    <div style="padding:.65rem;background:#f8fafc;border-radius:.5rem;">
                        <div style="font-size:1rem;font-weight:800;color:#0f172a;">{{ $inspection->km ? number_format($inspection->km) : '—' }}</div>
                        <div style="font-size:.72rem;color:#64748b;">km relevés</div>
                    </div>
                    <div style="padding:.65rem;background:{{ $fuelColor }}0d;border-radius:.5rem;">
                        @if(!is_null($inspection->fuel_level_pct))
                            <div style="font-size:1rem;font-weight:800;color:{{ $fuelColor }};">{{ $inspection->fuel_level_pct }}%</div>
                        @else
                            <div style="font-size:1rem;font-weight:800;color:#94a3b8;">—</div>
                        @endif
                        <div style="font-size:.72rem;color:#64748b;">Carburant</div>
                    </div>
                    <div style="padding:.65rem;background:{{ $inspection->has_critical_issue ? '#fef2f2' : '#f0fdf4' }};border-radius:.5rem;">
                        <div style="font-size:1rem;font-weight:800;color:{{ $inspection->has_critical_issue ? '#dc2626' : '#10b981' }};">
                            {{ $inspection->completionScore() }}%
                        </div>
                        <div style="font-size:.72rem;color:#64748b;">Complétion</div>
                    </div>
                </div>
                @if($inspection->location)
                    <div style="margin-top:.75rem;font-size:.8rem;color:#64748b;">
                        📍 {{ $inspection->location }}
                    </div>
                @endif
            </div>
        </div>

        {{-- ── FLUIDES MOTEUR ──────────────────────────────────────────────── --}}
        <div class="card">
            <div class="card-head">
                <div style="width:24px;height:24px;border-radius:.35rem;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke="#10b981" stroke-width="2"/><path d="M12 7v5l3 3" stroke="#10b981" stroke-width="2" stroke-linecap="round"/></svg>
                </div>
                <span class="card-title">Fluides moteur</span>
            </div>
            <div class="card-body">
                @php
                $fluidRows = [
                    ['Huile moteur',   $inspection->oil_level,         $levelMap, $inspection->oil_notes],
                    ['Refroidissement',$inspection->coolant_level,      $levelMap, null],
                    ['Liquide de frein',$inspection->brake_fluid_level, $levelMap, null],
                    ['État vidange',   $inspection->oil_change_status,  $oilChgMap, null],
                ];
                @endphp
                @foreach($fluidRows as [$label, $val, $map, $note])
                <div class="ctrl-row">
                    <div>
                        <div class="ctrl-name">{{ $label }}</div>
                        @if($note)<div class="ctrl-note-txt">{{ $note }}</div>@endif
                    </div>
                    @php $b = $map[$val] ?? null; @endphp
                    @if($b)
                        <span class="badge" style="background:{{ $b[2] }};color:{{ $b[1] }};">{{ $b[0] }}</span>
                    @else
                        <span style="color:#cbd5e1;font-size:.8rem;">—</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- ── PNEUS + ÉCLAIRAGE + FREINS ──────────────────────────────────── --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            {{-- Pneus --}}
            <div class="card">
                <div class="card-head">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke="#374151" stroke-width="2"/><circle cx="12" cy="12" r="4" stroke="#374151" stroke-width="2"/></svg>
                    <span class="card-title">Pneus</span>
                </div>
                <div class="card-body">
                    @php $tp = $psiMap[$inspection->tire_pressure] ?? null; @endphp
                    <div class="ctrl-row" style="border:none;">
                        <div class="ctrl-name">Pression</div>
                        @if($tp)<span class="badge" style="background:{{ $tp[2] }};color:{{ $tp[1] }};">{{ $tp[0] }}</span>
                        @else<span style="color:#cbd5e1;font-size:.8rem;">—</span>@endif
                    </div>
                    @if($inspection->tire_notes)
                        <div style="font-size:.78rem;color:#64748b;font-style:italic;margin-top:.4rem;">{{ $inspection->tire_notes }}</div>
                    @endif
                </div>
            </div>
            {{-- Carburant --}}
            <div class="card">
                <div class="card-head">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M3 22V10l4-8h10l4 8v12" stroke="#f97316" stroke-width="2" stroke-linecap="round"/></svg>
                    <span class="card-title">Carburant</span>
                </div>
                <div class="card-body">
                    @if(!is_null($inspection->fuel_level_pct))
                    <div style="font-size:1.5rem;font-weight:800;color:{{ $fuelColor }};margin-bottom:.4rem;">{{ $inspection->fuel_level_pct }}%</div>
                    <div style="height:10px;background:#f1f5f9;border-radius:99px;overflow:hidden;">
                        <div style="height:100%;width:{{ $inspection->fuel_level_pct }}%;background:{{ $fuelColor }};border-radius:99px;"></div>
                    </div>
                    @else
                    <span style="color:#cbd5e1;">Non renseigné</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── ÉCLAIRAGE & FREINAGE ─────────────────────────────────────────── --}}
        <div class="card">
            <div class="card-head">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5" stroke="#ca8a04" stroke-width="2"/><path d="M12 2v2M12 20v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M2 12h2M20 12h2" stroke="#ca8a04" stroke-width="2" stroke-linecap="round"/></svg>
                <span class="card-title">Éclairage & Freinage</span>
            </div>
            <div class="card-body">
                @foreach([['Éclairage',$inspection->lights_status,$inspection->lights_notes],['Freinage',$inspection->brakes_status,$inspection->brakes_notes]] as [$lbl,$val,$note])
                @php $c = $condMap[$val] ?? null; @endphp
                <div class="ctrl-row">
                    <div>
                        <div class="ctrl-name">{{ $lbl }}</div>
                        @if($note)<div class="ctrl-note-txt">{{ $note }}</div>@endif
                    </div>
                    @if($c)<span class="badge" style="background:{{ $c[2] }};color:{{ $c[1] }};">{{ $c[0] }}</span>
                    @else<span style="color:#cbd5e1;font-size:.8rem;">—</span>@endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- ── DOCUMENTS ────────────────────────────────────────────────────── --}}
        <div class="card">
            <div class="card-head">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="#6366f1" stroke-width="2"/><polyline points="14,2 14,8 20,8" stroke="#6366f1" stroke-width="2"/></svg>
                <span class="card-title">Documents & Conformité</span>
            </div>
            <div class="card-body">
                @php
                $docs = [
                    ['Carte grise', $inspection->registration_present ? 'present' : ($inspection->registration_present === false ? 'absent' : null), $docMap, null],
                    ['Assurance',   $inspection->insurance_status,          $docMap, $inspection->insurance_expiry?->format('d/m/Y')],
                    ['Visite technique', $inspection->technical_control_status, $docMap, $inspection->technical_control_expiry?->format('d/m/Y')],
                ];
                @endphp
                @foreach($docs as [$lbl, $val, $map, $extra])
                @php $d = $map[$val] ?? null; @endphp
                <div class="ctrl-row">
                    <div>
                        <div class="ctrl-name">{{ $lbl }}</div>
                        @if($extra)<div class="ctrl-note-txt">Exp. {{ $extra }}</div>@endif
                    </div>
                    @if($d)<span class="badge" style="background:{{ $d[2] }};color:{{ $d[1] }};">{{ $d[0] }}</span>
                    @else<span style="color:#cbd5e1;font-size:.8rem;">—</span>@endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- ── OBSERVATIONS ─────────────────────────────────────────────────── --}}
        @if($inspection->body_notes || $inspection->general_observations || !empty($inspection->body_photos))
        <div class="card">
            <div class="card-head">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" stroke="#64748b" stroke-width="2"/></svg>
                <span class="card-title">Observations & Photos carrosserie</span>
            </div>
            <div class="card-body" style="font-size:.83rem;color:#374151;">
                @if($inspection->body_notes)
                    <div style="margin-bottom:.65rem;">
                        <div style="font-size:.73rem;font-weight:600;color:#94a3b8;text-transform:uppercase;margin-bottom:.25rem;">Carrosserie</div>
                        <div>{{ $inspection->body_notes }}</div>
                    </div>
                @endif

                {{-- Photos carrosserie --}}
                @if(!empty($inspection->body_photos))
                <div style="margin-bottom:.75rem;">
                    <div style="font-size:.73rem;font-weight:600;color:#94a3b8;text-transform:uppercase;margin-bottom:.5rem;">
                        Photos ({{ count($inspection->body_photos) }})
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:.6rem;">
                        @foreach($inspection->body_photos as $photo)
                        <a href="{{ Storage::url($photo) }}" target="_blank"
                           style="display:block;width:100px;height:100px;border-radius:.5rem;overflow:hidden;border:1.5px solid #e2e8f0;flex-shrink:0;position:relative;">
                            <img src="{{ Storage::url($photo) }}" alt="Photo carrosserie"
                                 style="width:100%;height:100%;object-fit:cover;">
                            {{-- Icône loupe au survol --}}
                            <div style="position:absolute;inset:0;background:rgba(0,0,0,0);display:flex;align-items:center;justify-content:center;transition:background .2s;"
                                 onmouseover="this.style.background='rgba(0,0,0,.35)'"
                                 onmouseout="this.style.background='rgba(0,0,0,0)'">
                                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" style="color:#fff;opacity:0;" class="photo-zoom-icon">
                                    <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                                    <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M11 8v6M8 11h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($inspection->general_observations)
                    <div>
                        <div style="font-size:.73rem;font-weight:600;color:#94a3b8;text-transform:uppercase;margin-bottom:.25rem;">Observations générales</div>
                        <div>{{ $inspection->general_observations }}</div>
                    </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Motif de renvoi --}}
        @if($inspection->isRejected() && $inspection->rejection_reason)
        <div style="padding:.85rem 1.1rem;background:#fffbeb;border:1px solid #fcd34d;border-radius:.65rem;margin-bottom:1rem;font-size:.83rem;color:#78350f;">
            <strong>Motif de renvoi :</strong> {{ $inspection->rejection_reason }}
        </div>
        @endif
    </div>
</div>

{{-- Modal rejet — accessible seulement aux validateurs (fleet_manager, admin+) --}}
@can('inspections.validate')
<div id="modal-reject" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.45);z-index:100;align-items:center;justify-content:center;" onclick="if(event.target===this)this.style.display='none'">
    <div style="background:#fff;border-radius:.85rem;width:440px;max-width:94vw;box-shadow:0 20px 60px rgba(0,0,0,.2);">
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
            <div style="font-size:.95rem;font-weight:700;color:#d97706;">Renvoyer pour correction</div>
            <button onclick="document.getElementById('modal-reject').style.display='none'" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:1.3rem;line-height:1;">×</button>
        </div>
        <form method="POST" action="{{ route('inspections.reject', $inspection) }}" style="padding:1.25rem 1.5rem;">
            @csrf
            <div style="margin-bottom:1rem;">
                <label class="form-label">Motif (optionnel)</label>
                <textarea name="rejection_reason" class="form-input" rows="3" placeholder="Indiquer les corrections à apporter…"></textarea>
            </div>
            <div style="display:flex;gap:.65rem;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('modal-reject').style.display='none'" class="btn btn-ghost">Annuler</button>
                <button type="submit" class="btn btn-warning">Renvoyer</button>
            </div>
        </form>
    </div>
</div>
@endcan

@endsection
