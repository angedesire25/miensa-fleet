@extends('layouts.dashboard')

@section('title', $vehicle->plate . ' — ' . $vehicle->brand . ' ' . $vehicle->model)
@section('page-title', 'Fiche Véhicule')

@section('content')
@php
$statusMap = [
    'available'  => ['Disponible',   '#10b981','#f0fdf4'],
    'on_mission' => ['En mission',   '#3b82f6','#eff6ff'],
    'maintenance'=> ['Maintenance',  '#d97706','#fffbeb'],
    'breakdown'  => ['En panne',     '#ef4444','#fef2f2'],
    'sold'       => ['Vendu',        '#8b5cf6','#faf5ff'],
    'retired'    => ['Hors service', '#64748b','#f8fafc'],
];
$typeMap  = ['sedan'=>'Berline','suv'=>'SUV','van'=>'Van','pickup'=>'Pick-up','truck'=>'Camion','city'=>'Citadine','motorcycle'=>'Moto'];
$fuelMap  = ['diesel'=>'Diesel','gasoline'=>'Essence','hybrid'=>'Hybride','electric'=>'Électrique','lpg'=>'GPL'];
$licMap   = ['insurance'=>'Assurance','technical_control'=>'Visite technique','registration'=>'Carte grise','transport_permit'=>'Autorisation transport','other'=>'Autre'];
$docStatusMap = ['valid'=>['Valide','#10b981','#f0fdf4'],'expiring_soon'=>['Expire bientôt','#d97706','#fffbeb'],'expired'=>['Expiré','#ef4444','#fef2f2'],'missing'=>['Manquant','#64748b','#f8fafc']];
$assignStatusMap = ['planned'=>['Planifiée','#6366f1','#f0f0ff'],'confirmed'=>['Confirmée','#0891b2','#ecfeff'],'in_progress'=>['En cours','#3b82f6','#eff6ff'],'completed'=>['Terminée','#10b981','#f0fdf4'],'cancelled'=>['Annulée','#64748b','#f8fafc']];
$repairStatusMap = ['sent'=>['Envoyé','#3b82f6','#eff6ff'],'diagnosing'=>['Diagnostic','#d97706','#fffbeb'],'repairing'=>['En réparation','#f97316','#fff7ed'],'waiting_parts'=>['Attente pièces','#8b5cf6','#faf5ff'],'returned'=>['Retourné','#10b981','#f0fdf4'],'returned_with_issue'=>['Retourné (pb)','#ef4444','#fef2f2']];
$s = $statusMap[$vehicle->status] ?? ['Inconnu','#64748b','#f8fafc'];
@endphp

<style>
.card{background:#fff;border-radius:.75rem;border:1px solid #e2e8f0;overflow:hidden;margin-bottom:1rem;}
.card-head{padding:.85rem 1.25rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:.6rem;}
.card-title{font-size:.875rem;font-weight:700;color:#0f172a;}
.card-body{padding:1.1rem 1.5rem;}
.dl{display:grid;grid-template-columns:150px 1fr;gap:.5rem .75rem;}
.dt{font-size:.78rem;color:#94a3b8;font-weight:500;display:flex;align-items:center;}
.dd{font-size:.855rem;color:#0f172a;font-weight:500;}
.badge{display:inline-flex;align-items:center;gap:.25rem;padding:.2rem .6rem;border-radius:99px;font-size:.72rem;font-weight:600;}
.btn{padding:.5rem 1rem;border-radius:.45rem;font-size:.82rem;font-weight:600;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;transition:opacity .15s;}
.btn-primary{background:linear-gradient(135deg,#10b981,#059669);color:#fff;}
.btn-ghost{background:#f8fafc;color:#374151;border:1.5px solid #e2e8f0;}
.btn-ghost:hover{background:#f1f5f9;}
.btn-orange{background:#fffbeb;color:#92400e;border:1.5px solid #fde68a;}
.btn-danger{background:#fef2f2;color:#dc2626;border:1.5px solid #fecaca;}
.table-mini table{width:100%;border-collapse:collapse;font-size:.82rem;}
.table-mini th{color:#94a3b8;font-size:.7rem;text-transform:uppercase;letter-spacing:.04em;padding:.5rem .75rem;border-bottom:1.5px solid #f1f5f9;text-align:left;}
.table-mini td{padding:.55rem .75rem;border-bottom:1px solid #f8fafc;color:#374151;}
.table-mini tr:last-child td{border-bottom:none;}
.alert-flash{padding:.75rem 1rem;border-radius:.45rem;margin-bottom:1.25rem;display:flex;align-items:flex-start;gap:.6rem;font-size:.875rem;}
.alert-success{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;}
</style>

<div style="display:flex;align-items:center;gap:.5rem;font-size:.825rem;color:#94a3b8;margin-bottom:1.25rem;">
    <a href="{{ route('vehicles.index') }}" style="color:#10b981;text-decoration:none;font-weight:500;">Véhicules</a>
    <span>›</span>
    <span style="color:#374151;">{{ $vehicle->plate }}</span>
</div>



<div style="display:grid;grid-template-columns:280px 1fr;gap:1.25rem;align-items:start;">

    {{-- ── Colonne gauche ──────────────────────────────────────────────── --}}
    <div>
        <div class="card">
            <div class="card-body" style="padding:1.5rem;text-align:center;">
                {{-- Photo --}}
                @if($vehicle->profilePhoto)
                    <img src="{{ Storage::url($vehicle->profilePhoto->file_path) }}"
                         style="width:100%;height:160px;object-fit:cover;border-radius:.5rem;margin-bottom:.75rem;"
                         alt="{{ $vehicle->plate }}">
                @else
                    <div style="width:100%;height:160px;border-radius:.5rem;background:linear-gradient(135deg,#f1f5f9,#e2e8f0);display:flex;align-items:center;justify-content:center;margin-bottom:.75rem;">
                        <svg width="48" height="36" fill="none" viewBox="0 0 24 18"><path d="M3 13h2l1-3h12l1 3h2" stroke="#cbd5e1" stroke-width="1.8" stroke-linecap="round"/><circle cx="7.5" cy="14.5" r="1.5" stroke="#cbd5e1" stroke-width="1.5"/><circle cx="16.5" cy="14.5" r="1.5" stroke="#cbd5e1" stroke-width="1.5"/><path d="M6.5 5l1-3h9l1 3" stroke="#cbd5e1" stroke-width="1.5" fill="none"/></svg>
                    </div>
                @endif

                <div style="font-size:1.1rem;font-weight:800;color:#0f172a;">{{ $vehicle->brand }} {{ $vehicle->model }}</div>
                <div style="font-family:monospace;font-size:.9rem;font-weight:600;color:#64748b;background:#f1f5f9;padding:.2rem .6rem;border-radius:.3rem;display:inline-block;margin:.25rem 0 .6rem;">{{ $vehicle->plate }}</div>

                <div>
                    <span class="badge" style="background:{{ $s[2] }};color:{{ $s[1] }};">
                        <span style="width:6px;height:6px;border-radius:50%;background:{{ $s[1] }};display:inline-block;"></span>
                        {{ $s[0] }}
                    </span>
                </div>

                @if($vehicle->needs_service)
                <div style="margin-top:.6rem;background:#fef2f2;border:1px solid #fecaca;border-radius:.35rem;padding:.4rem .65rem;font-size:.75rem;color:#dc2626;font-weight:600;">
                    ⚠ Entretien dépassé
                </div>
                @endif
            </div>

            @if(!$vehicle->trashed())
            <div style="padding:1rem 1.25rem;border-top:1px solid #f1f5f9;display:flex;flex-direction:column;gap:.5rem;">
                @can('vehicles.edit')
                <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-primary" style="justify-content:center;">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    Modifier
                </a>

                {{-- Changer statut rapide --}}
                <div x-data="{ open: false }" style="position:relative;">
                    <button type="button" class="btn btn-ghost" style="width:100%;justify-content:center;" onclick="this.parentElement.querySelector('.status-menu').classList.toggle('hidden')">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        Changer statut
                    </button>
                    <div class="status-menu hidden" style="position:absolute;bottom:110%;left:0;right:0;background:#fff;border:1.5px solid #e2e8f0;border-radius:.5rem;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,.08);z-index:10;">
                        @foreach(['available'=>'Disponible','on_mission'=>'En mission','maintenance'=>'Maintenance','breakdown'=>'En panne','sold'=>'Vendu','retired'=>'Hors service'] as $val=>$lbl)
                        @if($val !== $vehicle->status)
                        <form method="POST" action="{{ route('vehicles.toggle-status', $vehicle) }}">
                            @csrf
                            <input type="hidden" name="status" value="{{ $val }}">
                            <button type="submit" style="width:100%;text-align:left;padding:.55rem .85rem;font-size:.8rem;border:none;background:none;cursor:pointer;color:#374151;transition:background .1s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='none'">
                                {{ $lbl }}
                            </button>
                        </form>
                        @endif
                        @endforeach
                    </div>
                </div>
                @endcan

                @can('vehicles.delete')
                <form method="POST" action="{{ route('vehicles.destroy', $vehicle) }}"
                      data-confirm="Ce véhicule sera archivé. Il pourra être restauré."
                      data-title="Archiver {{ $vehicle->plate }} ?" data-btn-text="Archiver">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger" style="width:100%;justify-content:center;">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><polyline points="3,6 5,6 21,6" stroke="currentColor" stroke-width="2"/><path d="M19 6l-1 14H6L5 6M10 11v6M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        Archiver
                    </button>
                </form>
                @endcan

                @if(auth()->user()->hasAnyRole(['super_admin', 'admin']))
                <form method="POST" action="{{ route('vehicles.force-destroy', $vehicle->id) }}"
                      data-confirm="Cette suppression est IRRÉVERSIBLE. Toutes les données et fichiers seront effacés définitivement."
                      data-title="Supprimer {{ $vehicle->plate }} définitivement ?"
                      data-btn-text="Supprimer définitivement" data-btn-color="#dc2626" data-icon="warning">
                    @csrf @method('DELETE')
                    <button type="submit" style="width:100%;justify-content:center;display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;background:#7f1d1d;color:#fca5a5;border:1.5px solid #dc2626;border-radius:.45rem;font-size:.8rem;font-weight:700;cursor:pointer;">
                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24"><polyline points="3,6 5,6 21,6" stroke="currentColor" stroke-width="2"/><path d="M19 6l-1 14H6L5 6M10 11v6M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        Supprimer définitivement
                    </button>
                </form>
                @endif
            </div>
            @else
            <div style="padding:1rem 1.25rem;border-top:1px solid #f1f5f9;display:flex;flex-direction:column;gap:.5rem;">
                @can('vehicles.delete')
                <form method="POST" action="{{ route('vehicles.restore', $vehicle->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M3 12a9 9 0 109-9 9 9 0 00-6.3 2.6L3 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M3 3v5h5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        Restaurer
                    </button>
                </form>
                @endcan

                @if(auth()->user()->hasAnyRole(['super_admin', 'admin']))
                <form method="POST" action="{{ route('vehicles.force-destroy', $vehicle->id) }}"
                      data-confirm="Cette suppression est IRRÉVERSIBLE. Toutes les données et fichiers seront effacés définitivement."
                      data-title="Supprimer {{ $vehicle->plate }} définitivement ?"
                      data-btn-text="Supprimer définitivement" data-btn-color="#dc2626" data-icon="warning">
                    @csrf @method('DELETE')
                    <button type="submit" style="width:100%;justify-content:center;display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;background:#7f1d1d;color:#fca5a5;border:1.5px solid #dc2626;border-radius:.45rem;font-size:.8rem;font-weight:700;cursor:pointer;">
                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24"><polyline points="3,6 5,6 21,6" stroke="currentColor" stroke-width="2"/><path d="M19 6l-1 14H6L5 6M10 11v6M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        Supprimer définitivement
                    </button>
                </form>
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- ── Colonne droite ──────────────────────────────────────────────── --}}
    <div>

        {{-- Informations générales --}}
        <div class="card">
            <div class="card-head">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3" stroke="#6366f1" stroke-width="2"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z" stroke="#6366f1" stroke-width="2"/></svg>
                <span class="card-title">Informations techniques</span>
            </div>
            <div class="card-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem .75rem;" class="dl">
                    <span class="dt">Marque / Modèle</span>
                    <span class="dd">{{ $vehicle->brand }} {{ $vehicle->model }}</span>
                    <span class="dt">Année</span>
                    <span class="dd">{{ $vehicle->year }}</span>
                    <span class="dt">Couleur</span>
                    <span class="dd">{{ $vehicle->color ?? '—' }}</span>
                    <span class="dt">N° châssis</span>
                    <span class="dd" style="font-family:monospace;font-size:.82rem;">{{ $vehicle->vin ?? '—' }}</span>
                    <span class="dt">Type</span>
                    <span class="dd">{{ $typeMap[$vehicle->vehicle_type] ?? $vehicle->vehicle_type }}</span>
                    <span class="dt">Carburant</span>
                    <span class="dd">{{ $fuelMap[$vehicle->fuel_type] ?? $vehicle->fuel_type }}</span>
                    <span class="dt">Permis requis</span>
                    <span class="dd">Permis {{ $vehicle->license_category }}</span>
                    <span class="dt">Places</span>
                    <span class="dd">{{ $vehicle->seats }}</span>
                    <span class="dt">Kilométrage</span>
                    <span class="dd" style="{{ $vehicle->needs_service ? 'color:#dc2626;font-weight:700;' : '' }}">
                        {{ number_format($vehicle->km_current) }} km
                        @if($vehicle->km_next_service)
                        <span style="font-size:.75rem;color:#94a3b8;font-weight:400;"> / prochain {{ number_format($vehicle->km_next_service) }} km</span>
                        @endif
                    </span>
                    <span class="dt">Acquisition</span>
                    <span class="dd">
                        @if($vehicle->purchase_price)
                            {{ number_format($vehicle->purchase_price, 0, ',', ' ') }} FCFA
                            @if($vehicle->purchase_date) · {{ $vehicle->purchase_date->isoFormat('D MMM YYYY') }} @endif
                        @else —
                        @endif
                    </span>
                    <span class="dt">Assurance</span>
                    <span class="dd">
                        @if($vehicle->insurance_company)
                            {{ $vehicle->insurance_company }}
                            @if($vehicle->insurance_policy_number)
                            <span style="font-family:monospace;font-size:.78rem;color:#64748b;"> · {{ $vehicle->insurance_policy_number }}</span>
                            @endif
                        @else —
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- Chauffeur actuel --}}
        @if($vehicle->currentDriver)
        <div class="card">
            <div class="card-head">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4" stroke="#10b981" stroke-width="2"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke="#10b981" stroke-width="2" stroke-linecap="round"/></svg>
                <span class="card-title">Chauffeur actuel</span>
            </div>
            <div class="card-body">
                <div style="display:flex;align-items:center;gap:.85rem;">
                    @if($vehicle->currentDriver->avatar)
                        <img src="{{ Storage::url($vehicle->currentDriver->avatar) }}" style="width:42px;height:42px;border-radius:50%;object-fit:cover;" alt="">
                    @else
                        <div style="width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,#10b981,#059669);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.85rem;">
                            {{ strtoupper(substr($vehicle->currentDriver->full_name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <div style="font-weight:600;font-size:.9rem;">{{ $vehicle->currentDriver->full_name }}</div>
                        <div style="font-size:.78rem;color:#64748b;">{{ $vehicle->currentDriver->matricule }} · {{ $vehicle->currentDriver->phone }}</div>
                    </div>
                    @can('drivers.view')
                    <a href="{{ route('drivers.show', $vehicle->currentDriver) }}" class="btn btn-ghost" style="margin-left:auto;padding:.4rem .8rem;font-size:.78rem;">Voir fiche</a>
                    @endcan
                </div>
            </div>
        </div>
        @endif

        {{-- Documents --}}
        <div class="card">
            <div class="card-head" style="justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:.6rem;">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="#d97706" stroke-width="2"/><path d="M14 2v6h6" stroke="#d97706" stroke-width="2" stroke-linecap="round"/></svg>
                    <span class="card-title">Documents administratifs</span>
                    <span style="font-size:.75rem;color:#94a3b8;">{{ $vehicle->documents->count() }} doc(s)</span>
                </div>
                @can('vehicles.edit')
                @if(!$vehicle->trashed())
                <button type="button" onclick="document.getElementById('doc-add-form').classList.toggle('hidden')"
                        style="display:inline-flex;align-items:center;gap:.3rem;padding:.3rem .75rem;background:#fffbeb;color:#92400e;border:1.5px solid #fde68a;border-radius:.4rem;font-size:.78rem;font-weight:600;cursor:pointer;">
                    <svg width="11" height="11" fill="none" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                    Ajouter
                </button>
                @endif
                @endcan
            </div>

            {{-- Formulaire ajout document (masqué par défaut) --}}
            @can('vehicles.edit')
            @if(!$vehicle->trashed())
            <div id="doc-add-form" class="hidden" style="padding:1rem 1.25rem;border-bottom:1px solid #f1f5f9;background:#fafafa;">
                <form method="POST" action="{{ route('vehicle-documents.store', $vehicle) }}" enctype="multipart/form-data">
                    @csrf
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
                        <div>
                            <label style="font-size:.75rem;font-weight:600;color:#64748b;display:block;margin-bottom:.25rem;">Type <span style="color:#ef4444;">*</span></label>
                            <select name="type" required style="width:100%;padding:.45rem .6rem;border:1.5px solid #e2e8f0;border-radius:.4rem;font-size:.82rem;color:#0f172a;background:#fff;">
                                <option value="">— Choisir —</option>
                                @foreach(['insurance'=>'Assurance','technical_control'=>'Visite technique','registration'=>'Carte grise','transport_permit'=>'Autorisation transport','other'=>'Autre'] as $v=>$l)
                                <option value="{{ $v }}">{{ $l }}</option>
                                @endforeach
                            </select>
                            @error('type')<p style="color:#ef4444;font-size:.72rem;margin:.15rem 0 0;">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label style="font-size:.75rem;font-weight:600;color:#64748b;display:block;margin-bottom:.25rem;">N° référence</label>
                            <input type="text" name="document_number" placeholder="Ex : POL-2024-XXX"
                                   style="width:100%;padding:.45rem .6rem;border:1.5px solid #e2e8f0;border-radius:.4rem;font-size:.82rem;font-family:monospace;box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="font-size:.75rem;font-weight:600;color:#64748b;display:block;margin-bottom:.25rem;">Autorité de délivrance</label>
                            <input type="text" name="issuing_authority" placeholder="Préfecture, compagnie…"
                                   style="width:100%;padding:.45rem .6rem;border:1.5px solid #e2e8f0;border-radius:.4rem;font-size:.82rem;box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="font-size:.75rem;font-weight:600;color:#64748b;display:block;margin-bottom:.25rem;">Date de délivrance</label>
                            <input type="date" name="issue_date"
                                   style="width:100%;padding:.45rem .6rem;border:1.5px solid #e2e8f0;border-radius:.4rem;font-size:.82rem;box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="font-size:.75rem;font-weight:600;color:#64748b;display:block;margin-bottom:.25rem;">Date d'expiration</label>
                            <input type="date" name="expiry_date"
                                   style="width:100%;padding:.45rem .6rem;border:1.5px solid #e2e8f0;border-radius:.4rem;font-size:.82rem;box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="font-size:.75rem;font-weight:600;color:#64748b;display:block;margin-bottom:.25rem;">Fichier <small style="color:#94a3b8;">(PDF/image)</small></label>
                            <label style="display:inline-flex;align-items:center;gap:.4rem;padding:.4rem .7rem;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:.4rem;cursor:pointer;font-size:.78rem;font-weight:600;color:#374151;">
                                <svg width="12" height="12" fill="none" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                <span id="doc-file-lbl">Choisir</span>
                                <input type="file" name="file" accept=".pdf,image/jpeg,image/png" style="display:none;"
                                       onchange="document.getElementById('doc-file-lbl').textContent=this.files[0]?.name||'Choisir'">
                            </label>
                        </div>
                    </div>
                    <div style="display:flex;gap:.5rem;justify-content:flex-end;">
                        <button type="button" onclick="document.getElementById('doc-add-form').classList.add('hidden')"
                                style="padding:.4rem .85rem;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:.4rem;font-size:.8rem;font-weight:600;color:#64748b;cursor:pointer;">
                            Annuler
                        </button>
                        <button type="submit"
                                style="padding:.4rem .85rem;background:linear-gradient(135deg,#d97706,#b45309);color:#fff;border:none;border-radius:.4rem;font-size:.8rem;font-weight:600;cursor:pointer;">
                            Enregistrer le document
                        </button>
                    </div>
                </form>
            </div>
            @endif
            @endcan

            <div class="table-mini">
                @if($vehicle->documents->isEmpty())
                    <p style="text-align:center;padding:1.25rem;color:#94a3b8;font-size:.82rem;">Aucun document enregistré.</p>
                @else
                <table>
                    <thead><tr><th>Type</th><th>Référence</th><th>Délivrance</th><th>Expiration</th><th>Statut</th><th></th></tr></thead>
                    <tbody>
                    @foreach($vehicle->documents as $doc)
                    @php $ds = $docStatusMap[$doc->status] ?? ['—','#64748b','#f8fafc']; @endphp
                    <tr>
                        <td style="font-weight:600;font-size:.82rem;">{{ $licMap[$doc->type] ?? $doc->type }}</td>
                        <td style="font-family:monospace;font-size:.78rem;color:#64748b;">{{ $doc->document_number ?? '—' }}</td>
                        <td style="font-size:.78rem;color:#64748b;">{{ $doc->issue_date ? $doc->issue_date->isoFormat('D MMM YY') : '—' }}</td>
                        <td style="font-size:.78rem;">{{ $doc->expiry_date ? $doc->expiry_date->isoFormat('D MMM YYYY') : '—' }}</td>
                        <td><span class="badge" style="background:{{ $ds[2] }};color:{{ $ds[1] }};">{{ $ds[0] }}</span></td>
                        <td style="white-space:nowrap;">
                            @if($doc->file_path)
                            <a href="{{ Storage::url($doc->file_path) }}" target="_blank"
                               style="display:inline-flex;align-items:center;gap:.2rem;font-size:.72rem;font-weight:600;color:#10b981;text-decoration:none;padding:.15rem .45rem;border:1px solid #bbf7d0;background:#f0fdf4;border-radius:99px;margin-right:.25rem;">
                                <svg width="9" height="9" fill="none" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M15 3h6v6M10 14L21 3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                Voir
                            </a>
                            @endif
                            @can('vehicles.edit')
                            @if(!$vehicle->trashed())
                            <form method="POST" action="{{ route('vehicle-documents.destroy', [$vehicle, $doc]) }}" style="display:inline;"
                                  data-confirm="Ce document sera supprimé définitivement." data-title="Supprimer ce document ?"
                                  data-btn-text="Supprimer" data-btn-color="#dc2626" data-icon="warning">
                                @csrf @method('DELETE')
                                <button type="submit" style="display:inline-flex;align-items:center;font-size:.72rem;font-weight:600;color:#dc2626;background:#fef2f2;border:1px solid #fecaca;border-radius:99px;padding:.15rem .45rem;cursor:pointer;">
                                    <svg width="9" height="9" fill="none" viewBox="0 0 24 24"><polyline points="3,6 5,6 21,6" stroke="currentColor" stroke-width="2"/><path d="M19 6l-1 14H6L5 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                </button>
                            </form>
                            @endif
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>

        {{-- Affectations récentes --}}
        <div class="card">
            <div class="card-head" style="justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:.6rem;">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2" stroke="#3b82f6" stroke-width="2"/><path d="M8 2v4M16 2v4M3 10h18" stroke="#3b82f6" stroke-width="2" stroke-linecap="round"/></svg>
                    <span class="card-title">Affectations récentes</span>
                </div>
                <span style="font-size:.75rem;color:#94a3b8;">10 dernières</span>
            </div>
            <div class="table-mini">
                @if($vehicle->assignments->isEmpty())
                    <p style="text-align:center;padding:1.25rem;color:#94a3b8;font-size:.82rem;">Aucune affectation.</p>
                @else
                <table>
                    <thead><tr><th>Chauffeur</th><th>Départ</th><th>Retour prévu</th><th>Statut</th></tr></thead>
                    <tbody>
                    @foreach($vehicle->assignments as $ass)
                    @php $as = $assignStatusMap[$ass->status] ?? ['—','#64748b','#f8fafc']; @endphp
                    <tr>
                        <td style="font-weight:500;">{{ $ass->driver->full_name ?? '—' }}</td>
                        <td style="font-size:.78rem;">{{ $ass->datetime_start->isoFormat('D MMM YYYY') }}</td>
                        <td style="font-size:.78rem;">{{ $ass->datetime_end_planned?->isoFormat('D MMM YYYY') ?? '—' }}</td>
                        <td><span class="badge" style="background:{{ $as[2] }};color:{{ $as[1] }};">{{ $as[0] }}</span></td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>

        {{-- Réparations récentes --}}
        @php
        $repTypeMap = ['corrective'=>'Réparation','preventive'=>'Entretien préventif','warranty'=>'Garantie','recall'=>'Rappel constructeur'];
        @endphp
        <div class="card">
            <div class="card-head">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.77 3.77z" stroke="#d97706" stroke-width="2"/></svg>
                <span class="card-title">Réparations récentes</span>
                <span style="margin-left:auto;font-size:.75rem;color:#94a3b8;">5 dernières</span>
            </div>
            <div class="table-mini">
                @if($vehicle->repairs->isEmpty())
                    <p style="text-align:center;padding:1.25rem;color:#94a3b8;font-size:.82rem;">Aucune réparation enregistrée.</p>
                @else
                <table>
                    <thead><tr><th>Date envoi</th><th>Garage</th><th>Type</th><th>Diagnostic</th><th>Statut</th></tr></thead>
                    <tbody>
                    @foreach($vehicle->repairs as $rep)
                    @php $rs = $repairStatusMap[$rep->status] ?? ['—','#64748b','#f8fafc']; @endphp
                    <tr>
                        <td style="font-size:.78rem;">{{ $rep->datetime_sent ? \Carbon\Carbon::parse($rep->datetime_sent)->isoFormat('D MMM YYYY') : $rep->created_at->isoFormat('D MMM YYYY') }}</td>
                        <td style="font-size:.8rem;">{{ $rep->garage->name ?? '—' }}</td>
                        <td style="font-size:.78rem;font-weight:500;">{{ $repTypeMap[$rep->repair_type] ?? ucfirst($rep->repair_type) }}</td>
                        <td style="font-size:.78rem;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#64748b;" title="{{ $rep->diagnosis }}">{{ $rep->diagnosis ?? '—' }}</td>
                        <td><span class="badge" style="background:{{ $rs[2] }};color:{{ $rs[1] }};">{{ $rs[0] }}</span></td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
