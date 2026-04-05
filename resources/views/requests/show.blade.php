@extends('layouts.dashboard')
@section('title', 'Demande #' . $vehicleRequest->id)
@section('page-title', 'Détail de la demande')

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
$condMap  = ['good'=>['Bon','#10b981','#f0fdf4'],'fair'=>['Moyen','#d97706','#fffbeb'],'poor'=>['Mauvais','#ef4444','#fef2f2']];
$typePrefs= ['any'=>'Indifférent','city'=>'Citadine','sedan'=>'Berline','suv'=>'SUV','pickup'=>'Pickup','van'=>'Fourgon','truck'=>'Camion'];
$s = $statusMap[$vehicleRequest->status] ?? ['Inconnu','#64748b','#f8fafc'];
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
.btn-cyan{background:linear-gradient(135deg,#0891b2,#0e7490);color:#fff;}
.btn-ghost{background:#f8fafc;color:#374151;border:1.5px solid #e2e8f0;}
.btn-ghost:hover{background:#f1f5f9;}
.btn-danger{background:#fef2f2;color:#dc2626;border:1.5px solid #fecaca;}
.btn-warning{background:#fffbeb;color:#92400e;border:1.5px solid #fde68a;}
.form-input{width:100%;padding:.5rem .75rem;border:1.5px solid #e2e8f0;border-radius:.45rem;font-size:.83rem;background:#fff;color:#0f172a;outline:none;}
.form-input:focus{border-color:#10b981;}
.form-label{font-size:.78rem;font-weight:600;color:#374151;margin-bottom:.3rem;display:block;}
.form-group{margin-bottom:.85rem;}
.vehicle-opt{border:2px solid #e2e8f0;border-radius:.5rem;padding:.55rem .85rem;cursor:pointer;display:flex;align-items:center;gap:.6rem;margin-bottom:.4rem;transition:border-color .15s;}
.vehicle-opt:hover{border-color:#cbd5e1;background:#f8fafc;}
.vehicle-opt.selected{border-color:#10b981;background:#f0fdf4;}
</style>

{{-- Breadcrumb --}}
<div style="display:flex;align-items:center;gap:.5rem;font-size:.825rem;color:#94a3b8;margin-bottom:1.25rem;">
    <a href="{{ route('requests.index') }}" style="color:#10b981;text-decoration:none;font-weight:500;">Demandes</a>
    <span>›</span>
    <span style="color:#374151;">Demande #{{ $vehicleRequest->id }}</span>
</div>

<div style="display:grid;grid-template-columns:280px 1fr;gap:1.25rem;align-items:start;">

    {{-- ── Colonne gauche ──────────────────────────────────────────────────── --}}
    <div>

        {{-- Statut & résumé --}}
        <div class="card">
            <div class="card-body" style="padding:1.5rem;text-align:center;">
                <div style="width:56px;height:56px;border-radius:.65rem;background:{{ $s[2] }};display:flex;align-items:center;justify-content:center;margin:0 auto .85rem;">
                    <svg width="24" height="24" fill="none" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4" stroke="{{ $s[1] }}" stroke-width="2" stroke-linecap="round"/><path d="M20 12c0 4.418-3.582 8-8 8s-8-3.582-8-8 3.582-8 8-8 8 3.582 8 8z" stroke="{{ $s[1] }}" stroke-width="2"/></svg>
                </div>
                <div style="font-size:1rem;font-weight:800;color:#0f172a;margin-bottom:.25rem;">Demande #{{ $vehicleRequest->id }}</div>
                @if($vehicleRequest->is_urgent)
                    <div style="margin-bottom:.4rem;"><span class="badge" style="background:#fef2f2;color:#dc2626;">🚨 Urgente</span></div>
                @endif
                <span class="badge" style="background:{{ $s[2] }};color:{{ $s[1] }};">
                    <span style="width:6px;height:6px;border-radius:50%;background:{{ $s[1] }};display:inline-block;"></span>
                    {{ $s[0] }}
                </span>
            </div>
        </div>

        {{-- Demandeur --}}
        <div class="card">
            <div class="card-head">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4" stroke="#10b981" stroke-width="2"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke="#10b981" stroke-width="2" stroke-linecap="round"/></svg>
                <span class="card-title">Demandeur</span>
            </div>
            <div class="card-body">
                <div style="display:flex;align-items:center;gap:.75rem;">
                    @if($vehicleRequest->requester?->avatar)
                        <img src="{{ Storage::url($vehicleRequest->requester->avatar) }}"
                             style="width:40px;height:40px;border-radius:50%;object-fit:cover;flex-shrink:0;" alt="">
                    @else
                        <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#4f46e5);display:flex;align-items:center;justify-content:center;font-size:.82rem;font-weight:700;color:#fff;flex-shrink:0;">
                            {{ strtoupper(substr($vehicleRequest->requester->name ?? 'U', 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <div style="font-weight:600;font-size:.88rem;color:#0f172a;">{{ $vehicleRequest->requester?->name ?? '—' }}</div>
                        <div style="font-size:.76rem;color:#64748b;">{{ $vehicleRequest->requester?->email }}</div>
                    </div>
                </div>
                <div style="margin-top:.75rem;font-size:.78rem;color:#94a3b8;">
                    Demande soumise le {{ $vehicleRequest->created_at->isoFormat('D MMM YYYY, H:mm') }}
                </div>
            </div>
        </div>

        {{-- Conducteur attribué --}}
        @if(!in_array($vehicleRequest->status, ['pending','rejected']) && ($vehicleRequest->driver || $vehicleRequest->self_driving))
        <div class="card">
            <div class="card-head">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4" stroke="#6366f1" stroke-width="2"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke="#6366f1" stroke-width="2" stroke-linecap="round"/></svg>
                <span class="card-title">Conducteur</span>
            </div>
            <div class="card-body">
                @if($vehicleRequest->self_driving)
                    <div style="display:flex;align-items:center;gap:.65rem;">
                        <div style="width:36px;height:36px;border-radius:50%;background:#f0f0ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M3 17h2l1-3h12l1 3h2" stroke="#6366f1" stroke-width="2" stroke-linecap="round"/></svg>
                        </div>
                        <div>
                            <div style="font-weight:600;font-size:.85rem;color:#0f172a;">{{ $vehicleRequest->requester?->name }} (auto-conduite)</div>
                            <div style="font-size:.74rem;color:#6366f1;">Le demandeur conduit lui-même</div>
                        </div>
                    </div>
                @elseif($vehicleRequest->driver)
                    <div style="display:flex;align-items:center;gap:.65rem;">
                        @if($vehicleRequest->driver->avatar)
                            <img src="{{ Storage::url($vehicleRequest->driver->avatar) }}" style="width:36px;height:36px;border-radius:50%;object-fit:cover;flex-shrink:0;" alt="">
                        @else
                            <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#10b981,#059669);display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;color:#fff;flex-shrink:0;">
                                {{ strtoupper(substr($vehicleRequest->driver->full_name, 0, 2)) }}
                            </div>
                        @endif
                        <div>
                            <div style="font-weight:600;font-size:.85rem;color:#0f172a;">{{ $vehicleRequest->driver->full_name }}</div>
                            <div style="font-size:.74rem;color:#64748b;">{{ $vehicleRequest->driver->matricule }}</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Véhicule attribué --}}
        @if($vehicleRequest->vehicle)
        <div class="card">
            <div class="card-head">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M3 17h2l1-3h12l1 3h2" stroke="#3b82f6" stroke-width="2" stroke-linecap="round"/><circle cx="7.5" cy="18.5" r="1.5" stroke="#3b82f6" stroke-width="1.5"/><circle cx="16.5" cy="18.5" r="1.5" stroke="#3b82f6" stroke-width="1.5"/></svg>
                <span class="card-title">Véhicule attribué</span>
            </div>
            <div class="card-body">
                @if($vehicleRequest->vehicle->profilePhoto)
                    <img src="{{ Storage::url($vehicleRequest->vehicle->profilePhoto->path) }}"
                         style="width:100%;height:90px;object-fit:cover;border-radius:.4rem;margin-bottom:.65rem;" alt="">
                @endif
                <div style="font-family:monospace;font-size:.9rem;font-weight:700;color:#0f172a;background:#f1f5f9;padding:.2rem .5rem;border-radius:.3rem;display:inline-block;margin-bottom:.3rem;">
                    {{ $vehicleRequest->vehicle->plate }}
                </div>
                <div style="font-size:.82rem;color:#374151;">{{ $vehicleRequest->vehicle->brand }} {{ $vehicleRequest->vehicle->model }}</div>
                <a href="{{ route('vehicles.show', $vehicleRequest->vehicle) }}" class="btn btn-ghost" style="width:100%;justify-content:center;margin-top:.65rem;padding:.4rem .75rem;">
                    Voir la fiche
                </a>
            </div>
        </div>
        @endif

        {{-- Traité par --}}
        @if($vehicleRequest->reviewedBy)
        <div class="card">
            <div class="card-body" style="font-size:.8rem;color:#64748b;">
                <div style="margin-bottom:.4rem;"><span style="font-weight:600;color:#374151;">Traité par :</span> {{ $vehicleRequest->reviewedBy->name }}</div>
                <div><span style="font-weight:600;color:#374151;">Le :</span> {{ $vehicleRequest->reviewed_at?->isoFormat('D MMM YYYY, H:mm') ?? '—' }}</div>
                @if($vehicleRequest->review_notes)
                    <div style="margin-top:.5rem;padding:.5rem .65rem;background:#f8fafc;border-radius:.35rem;font-style:italic;">{{ $vehicleRequest->review_notes }}</div>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- ── Colonne droite ───────────────────────────────────────────────────── --}}
    <div>

        {{-- ── APPROBATION (gestionnaire, statut pending) ──────────────────── --}}
        @if($vehicleRequest->status === 'pending')
        @canany(['vehicle_requests.approve'])
        <div class="card" style="border-left:4px solid #d97706;">
            <div class="card-head">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4" stroke="#d97706" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="#d97706" stroke-width="2"/></svg>
                <span class="card-title" style="color:#92400e;">Traitement de la demande</span>
            </div>
            <div class="card-body">
                {{-- Approuver --}}
                <div style="margin-bottom:1.25rem;">
                    <div style="font-size:.82rem;font-weight:600;color:#0f172a;margin-bottom:.85rem;">Approuver — attribuer un véhicule</div>
                    <form method="POST" action="{{ route('requests.approve', $vehicleRequest) }}" id="approve-form">
                        @csrf

                        {{-- Sélection véhicule --}}
                        <div class="form-group">
                            <label class="form-label">Véhicule à attribuer <span style="color:#ef4444;">*</span></label>
                            @forelse($availableVehicles as $v)
                            <label class="vehicle-opt {{ old('vehicle_id') == $v->id ? 'selected' : '' }}" id="voption-{{ $v->id }}">
                                <input type="radio" name="vehicle_id" value="{{ $v->id }}" style="display:none;"
                                       @checked(old('vehicle_id') == $v->id)
                                       onchange="selectVOpt({{ $v->id }})">
                                @if($v->profilePhoto)
                                    <img src="{{ Storage::url($v->profilePhoto->path) }}" style="width:40px;height:30px;border-radius:.3rem;object-fit:cover;flex-shrink:0;" alt="">
                                @else
                                    <div style="width:40px;height:30px;background:#f1f5f9;border-radius:.3rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M3 17h2l1-3h12l1 3h2" stroke="#cbd5e1" stroke-width="2"/></svg>
                                    </div>
                                @endif
                                <div style="flex:1;">
                                    <span style="font-family:monospace;font-size:.82rem;font-weight:700;">{{ $v->plate }}</span>
                                    <span style="font-size:.8rem;color:#374151;margin-left:.4rem;">{{ $v->brand }} {{ $v->model }}</span>
                                    <div style="font-size:.73rem;color:#94a3b8;">{{ number_format($v->km_current ?? 0) }} km · {{ $v->year }}</div>
                                </div>
                            </label>
                            @empty
                            <div style="padding:.75rem;background:#f8fafc;border-radius:.45rem;font-size:.82rem;color:#94a3b8;text-align:center;">
                                Aucun véhicule disponible sur cette période.
                            </div>
                            @endforelse
                            @error('vehicle_id')<div style="font-size:.74rem;color:#ef4444;margin-top:.3rem;">{{ $message }}</div>@enderror
                        </div>

                        {{-- Mode conducteur --}}
                        <div class="form-group">
                            <label class="form-label" style="margin-bottom:.6rem;">Conducteur <span style="color:#ef4444;">*</span></label>
                            <div style="display:flex;flex-direction:column;gap:.4rem;">

                                {{-- Option 1 : affecter un chauffeur --}}
                                <label id="mode-assign-label" style="border:2px solid {{ old('driver_mode','none')==='assign' ? '#10b981' : '#e2e8f0' }};border-radius:.5rem;padding:.6rem .85rem;cursor:pointer;transition:border-color .15s;">
                                    <div style="display:flex;align-items:center;gap:.65rem;">
                                        <input type="radio" name="driver_mode" value="assign"
                                               @checked(old('driver_mode')==='assign')
                                               onchange="setDriverMode('assign')"
                                               style="accent-color:#10b981;flex-shrink:0;">
                                        <div>
                                            <div style="font-weight:600;font-size:.83rem;color:#0f172a;">
                                                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:.3rem;"><circle cx="12" cy="8" r="4" stroke="#10b981" stroke-width="2"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke="#10b981" stroke-width="2" stroke-linecap="round"/></svg>
                                                Affecter un chauffeur professionnel
                                            </div>
                                            <div style="font-size:.73rem;color:#94a3b8;">Un chauffeur de la flotte conduit le demandeur</div>
                                        </div>
                                    </div>
                                    {{-- Liste chauffeurs (visible si mode assign) --}}
                                    <div id="driver-list-panel" style="margin-top:.65rem;display:{{ old('driver_mode')==='assign' ? 'block' : 'none' }};">
                                        <div style="position:relative;margin-bottom:.4rem;">
                                            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" style="position:absolute;left:.55rem;top:50%;transform:translateY(-50%);color:#94a3b8;"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2"/></svg>
                                            <input type="text" placeholder="Rechercher un chauffeur…"
                                                   oninput="filterApproveDrivers(this.value)"
                                                   style="width:100%;padding:.42rem .75rem .42rem 2rem;border:1.5px solid #e2e8f0;border-radius:.4rem;font-size:.81rem;outline:none;">
                                        </div>
                                        <div id="approve-driver-list" style="max-height:180px;overflow-y:auto;">
                                            @forelse($availableDrivers as $d)
                                            <label id="dopt-{{ $d->id }}" style="display:flex;align-items:center;gap:.6rem;border:1.5px solid {{ old('driver_id') == $d->id ? '#10b981' : '#f1f5f9' }};background:{{ old('driver_id') == $d->id ? '#f0fdf4' : '#fafafa' }};border-radius:.4rem;padding:.45rem .7rem;cursor:pointer;margin-bottom:.3rem;transition:border-color .15s;">
                                                <input type="radio" name="driver_id" value="{{ $d->id }}" style="display:none;"
                                                       @checked(old('driver_id') == $d->id)
                                                       onchange="selectApproveDriver({{ $d->id }})">
                                                @if($d->avatar)
                                                    <img src="{{ Storage::url($d->avatar) }}" style="width:30px;height:30px;border-radius:50%;object-fit:cover;flex-shrink:0;" alt="">
                                                @else
                                                    <div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#10b981,#059669);display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;color:#fff;flex-shrink:0;">
                                                        {{ strtoupper(substr($d->full_name, 0, 2)) }}
                                                    </div>
                                                @endif
                                                <div style="flex:1;">
                                                    <div style="font-weight:600;font-size:.81rem;color:#0f172a;">{{ $d->full_name }}</div>
                                                    <div style="font-size:.72rem;color:#94a3b8;">{{ $d->matricule }} · Permis {{ implode(', ', (array)$d->license_categories) }}</div>
                                                </div>
                                            </label>
                                            @empty
                                            <div style="font-size:.8rem;color:#94a3b8;padding:.5rem;text-align:center;">Aucun chauffeur disponible sur ce créneau.</div>
                                            @endforelse
                                        </div>
                                        @error('driver_id')<div style="font-size:.74rem;color:#ef4444;margin-top:.25rem;">{{ $message }}</div>@enderror
                                    </div>
                                </label>

                                {{-- Option 2 : auto-conduite --}}
                                @php $requesterDriver = $vehicleRequest->requester?->driver; @endphp
                                <label id="mode-self-label" style="border:2px solid {{ old('driver_mode','none')==='self' ? '#6366f1' : '#e2e8f0' }};border-radius:.5rem;padding:.6rem .85rem;cursor:pointer;transition:border-color .15s;">
                                    <div style="display:flex;align-items:center;gap:.65rem;">
                                        <input type="radio" name="driver_mode" value="self"
                                               @checked(old('driver_mode')==='self')
                                               onchange="setDriverMode('self')"
                                               style="accent-color:#6366f1;flex-shrink:0;">
                                        <div>
                                            <div style="font-weight:600;font-size:.83rem;color:#0f172a;">
                                                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:.3rem;"><path d="M3 17h2l1-3h12l1 3h2" stroke="#6366f1" stroke-width="2" stroke-linecap="round"/><circle cx="7.5" cy="18.5" r="1.5" stroke="#6366f1" stroke-width="1.5"/><circle cx="16.5" cy="18.5" r="1.5" stroke="#6366f1" stroke-width="1.5"/></svg>
                                                Le demandeur conduit lui-même
                                            </div>
                                            @if($requesterDriver)
                                                <div style="font-size:.73rem;color:#059669;margin-top:.1rem;">
                                                    ✓ Permis {{ implode(', ', (array)$requesterDriver->license_categories) }}
                                                    · Expire le {{ $requesterDriver->license_expiry_date?->format('d/m/Y') }}
                                                </div>
                                            @else
                                                <div style="font-size:.73rem;color:#94a3b8;">Vérifiez que le demandeur possède un permis valide</div>
                                            @endif
                                        </div>
                                    </div>
                                </label>

                                {{-- Option 3 : sans chauffeur attitré --}}
                                <label id="mode-none-label" style="border:2px solid {{ old('driver_mode','none')==='none' ? '#64748b' : '#e2e8f0' }};border-radius:.5rem;padding:.6rem .85rem;cursor:pointer;transition:border-color .15s;">
                                    <div style="display:flex;align-items:center;gap:.65rem;">
                                        <input type="radio" name="driver_mode" value="none"
                                               @checked(old('driver_mode', 'none')==='none')
                                               onchange="setDriverMode('none')"
                                               style="accent-color:#64748b;flex-shrink:0;">
                                        <div>
                                            <div style="font-weight:600;font-size:.83rem;color:#0f172a;">Sans conducteur désigné pour l'instant</div>
                                            <div style="font-size:.73rem;color:#94a3b8;">À préciser ultérieurement</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            @error('driver_mode')<div style="font-size:.74rem;color:#ef4444;margin-top:.3rem;">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Note d'approbation (optionnel)</label>
                            <textarea name="review_notes" class="form-input" rows="2" placeholder="Conditions, remarques…">{{ old('review_notes') }}</textarea>
                        </div>

                        @if($availableVehicles->isNotEmpty())
                        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                            <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                            Approuver la demande
                        </button>
                        @endif
                    </form>
                </div>

                <div style="height:1px;background:#f1f5f9;margin:1rem 0;"></div>

                {{-- Rejeter --}}
                <div>
                    <div style="font-size:.82rem;font-weight:600;color:#dc2626;margin-bottom:.6rem;">Rejeter la demande</div>
                    <form method="POST" action="{{ route('requests.reject', $vehicleRequest) }}">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Motif du rejet (optionnel)</label>
                            <textarea name="review_notes" class="form-input" rows="2" placeholder="Raison du refus…">{{ old('review_notes') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-danger" style="width:100%;justify-content:center;"
                                data-confirm="Rejeter définitivement cette demande ?"
                                data-title="Rejeter la demande"
                                data-btn-text="Rejeter"
                                data-btn-color="#ef4444">
                            <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                            Rejeter la demande
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endcanany
        @endif

        {{-- ── DÉPART (statut approved) ───────────────────────────────────── --}}
        @if($vehicleRequest->status === 'approved')
        @can('vehicle_requests.edit')
        <div class="card" style="border-left:4px solid #3b82f6;">
            <div class="card-head">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7" stroke="#3b82f6" stroke-width="2" stroke-linecap="round"/></svg>
                <span class="card-title" style="color:#1d4ed8;">Enregistrer le départ</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('requests.start', $vehicleRequest) }}">
                    @csrf
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
                        <div class="form-group">
                            <label class="form-label">Km au départ <span style="color:#ef4444;">*</span></label>
                            <input type="number" name="km_start" class="form-input" min="0" required placeholder="ex: 45800" value="{{ old('km_start', $vehicleRequest->vehicle?->km_current) }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">État du véhicule <span style="color:#ef4444;">*</span></label>
                            <select name="condition_start" class="form-input" required>
                                <option value="">— Sélectionner —</option>
                                <option value="good"  @selected(old('condition_start')==='good')>Bon état</option>
                                <option value="fair"  @selected(old('condition_start')==='fair')>État moyen</option>
                                <option value="poor"  @selected(old('condition_start')==='poor')>Mauvais état</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:1rem;">
                        <label class="form-label">Observations (optionnel)</label>
                        <textarea name="condition_start_notes" class="form-input" rows="2" placeholder="Rayures, carburant…">{{ old('condition_start_notes') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-blue" style="width:100%;justify-content:center;">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                        Confirmer le départ
                    </button>
                </form>
            </div>
        </div>
        @endcan
        @endif

        {{-- ── RETOUR (statut in_progress) ────────────────────────────────── --}}
        @if($vehicleRequest->status === 'in_progress')
        @can('vehicle_requests.edit')
        <div class="card" style="border-left:4px solid #10b981;">
            <div class="card-head">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4" stroke="#10b981" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="#10b981" stroke-width="2"/></svg>
                <span class="card-title" style="color:#065f46;">Enregistrer le retour</span>
            </div>
            <div class="card-body">
                @if($vehicleRequest->km_start)
                <div style="padding:.55rem .85rem;background:#eff6ff;border-radius:.4rem;font-size:.8rem;color:#1d4ed8;margin-bottom:.85rem;">
                    Km au départ : <strong>{{ number_format($vehicleRequest->km_start) }} km</strong>
                </div>
                @endif
                <form method="POST" action="{{ route('requests.complete', $vehicleRequest) }}">
                    @csrf
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
                        <div class="form-group">
                            <label class="form-label">Km au retour <span style="color:#ef4444;">*</span></label>
                            <input type="number" name="km_end" class="form-input" min="{{ $vehicleRequest->km_start ?? 0 }}" required placeholder="ex: 46250" value="{{ old('km_end') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">État au retour <span style="color:#ef4444;">*</span></label>
                            <select name="condition_end" class="form-input" required>
                                <option value="">— Sélectionner —</option>
                                <option value="good"  @selected(old('condition_end')==='good')>Bon état</option>
                                <option value="fair"  @selected(old('condition_end')==='fair')>État moyen</option>
                                <option value="poor"  @selected(old('condition_end')==='poor')>Mauvais état</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:1rem;">
                        <label class="form-label">Observations (optionnel)</label>
                        <textarea name="condition_end_notes" class="form-input" rows="2" placeholder="Dommages constatés, carburant…">{{ old('condition_end_notes') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/></svg>
                        Valider le retour
                    </button>
                </form>
            </div>
        </div>
        @endcan
        @endif

        {{-- Informations de la demande --}}
        <div class="card">
            <div class="card-head" style="justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:.6rem;">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4" stroke="#10b981" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="#10b981" stroke-width="2"/></svg>
                    <span class="card-title">Informations de la demande</span>
                </div>
                @if($vehicleRequest->status === 'pending')
                @can('vehicle_requests.edit')
                    <a href="{{ route('requests.edit', $vehicleRequest) }}" class="btn btn-ghost" style="padding:.3rem .65rem;font-size:.75rem;">
                        <svg width="11" height="11" fill="none" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="2"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2"/></svg>
                        Modifier
                    </a>
                @endcan
                @endif
            </div>
            <div class="card-body">
                <div class="dl">
                    <div class="dt">Destination</div>
                    <div class="dd">{{ $vehicleRequest->destination }}</div>

                    <div class="dt">Objet / raison</div>
                    <div class="dd">{{ $vehicleRequest->purpose }}</div>

                    <div class="dt">Passagers</div>
                    <div class="dd">{{ $vehicleRequest->passengers }}</div>

                    <div class="dt">Type préféré</div>
                    <div class="dd">{{ $typePrefs[$vehicleRequest->vehicle_type_preferred] ?? $vehicleRequest->vehicle_type_preferred }}</div>

                    <div class="dt">Départ</div>
                    <div class="dd">{{ $vehicleRequest->datetime_start->isoFormat('dddd D MMMM YYYY, H:mm') }}</div>

                    <div class="dt">Retour prévu</div>
                    <div class="dd">{{ $vehicleRequest->datetime_end_planned->isoFormat('dddd D MMMM YYYY, H:mm') }}</div>

                    @if($vehicleRequest->datetime_end_actual)
                    <div class="dt">Retour effectif</div>
                    <div class="dd" style="color:#10b981;font-weight:600;">{{ $vehicleRequest->datetime_end_actual->isoFormat('D MMM YYYY, H:mm') }}</div>
                    @endif

                    @if($vehicleRequest->requester_notes)
                    <div class="dt">Notes demandeur</div>
                    <div class="dd" style="font-style:italic;color:#64748b;">{{ $vehicleRequest->requester_notes }}</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Kilométrage --}}
        @if($vehicleRequest->km_start || $vehicleRequest->km_end)
        <div class="card">
            <div class="card-head">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke="#3b82f6" stroke-width="2"/><path d="M12 7v5l3 3" stroke="#3b82f6" stroke-width="2" stroke-linecap="round"/></svg>
                <span class="card-title">Kilométrage</span>
            </div>
            <div class="card-body">
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;text-align:center;">
                    <div style="padding:.85rem;background:#f8fafc;border-radius:.5rem;">
                        <div style="font-size:1.1rem;font-weight:800;color:#0f172a;">{{ $vehicleRequest->km_start ? number_format($vehicleRequest->km_start) : '—' }}</div>
                        <div style="font-size:.72rem;color:#64748b;margin-top:.15rem;">Km départ</div>
                    </div>
                    <div style="padding:.85rem;background:#f8fafc;border-radius:.5rem;">
                        <div style="font-size:1.1rem;font-weight:800;color:#0f172a;">{{ $vehicleRequest->km_end ? number_format($vehicleRequest->km_end) : '—' }}</div>
                        <div style="font-size:.72rem;color:#64748b;margin-top:.15rem;">Km retour</div>
                    </div>
                    <div style="padding:.85rem;background:{{ $vehicleRequest->km_total ? '#f0fdf4' : '#f8fafc' }};border-radius:.5rem;">
                        <div style="font-size:1.1rem;font-weight:800;color:{{ $vehicleRequest->km_total ? '#10b981' : '#0f172a' }};">{{ $vehicleRequest->km_total ? number_format($vehicleRequest->km_total) : '—' }}</div>
                        <div style="font-size:.72rem;color:#64748b;margin-top:.15rem;">Km parcourus</div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Annulation / Rejet --}}
        @if(in_array($vehicleRequest->status, ['cancelled','rejected']) && $vehicleRequest->review_notes)
        <div style="padding:.85rem 1.1rem;background:{{ $vehicleRequest->status === 'rejected' ? '#fef2f2' : '#f8fafc' }};border:1px solid {{ $vehicleRequest->status === 'rejected' ? '#fecaca' : '#e2e8f0' }};border-radius:.65rem;margin-bottom:1rem;font-size:.83rem;color:{{ $vehicleRequest->status === 'rejected' ? '#7f1d1d' : '#374151' }};">
            <div style="font-weight:700;margin-bottom:.2rem;">{{ $vehicleRequest->status === 'rejected' ? 'Motif du rejet' : 'Motif d\'annulation' }}</div>
            {{ $vehicleRequest->review_notes }}
        </div>
        @endif

        {{-- Action annulation (collaborateur sur sa propre demande) --}}
        @if(!in_array($vehicleRequest->status, ['completed','rejected','cancelled']))
        @can('vehicle_requests.edit')
        <div class="card">
            <div class="card-body" style="padding:.85rem 1.25rem;">
                <form method="POST" action="{{ route('requests.cancel', $vehicleRequest) }}"
                      data-confirm="Annuler cette demande de véhicule ?"
                      data-title="Annuler la demande"
                      data-btn-text="Oui, annuler"
                      data-btn-color="#ef4444">
                    @csrf
                    <button type="submit" class="btn btn-danger" style="font-size:.78rem;padding:.38rem .75rem;">
                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                        Annuler la demande
                    </button>
                </form>
            </div>
        </div>
        @endcan
        @endif

    </div>
</div>

<script>
function selectVOpt(id) {
    document.querySelectorAll('.vehicle-opt').forEach(c => c.classList.remove('selected'));
    const el = document.getElementById('voption-' + id);
    if (el) el.classList.add('selected');
}

const modeColors = { assign: '#10b981', self: '#6366f1', none: '#64748b' };

function setDriverMode(mode) {
    Object.keys(modeColors).forEach(m => {
        const lbl = document.getElementById('mode-' + m + '-label');
        if (lbl) lbl.style.borderColor = m === mode ? modeColors[m] : '#e2e8f0';
    });
    const panel = document.getElementById('driver-list-panel');
    if (panel) panel.style.display = mode === 'assign' ? 'block' : 'none';
    // Clear driver_id if not assigning
    if (mode !== 'assign') {
        document.querySelectorAll('[name="driver_id"]').forEach(r => r.checked = false);
        document.querySelectorAll('#approve-driver-list label').forEach(l => {
            l.style.borderColor = '#f1f5f9';
            l.style.background  = '#fafafa';
        });
    }
}

function selectApproveDriver(id) {
    document.querySelectorAll('#approve-driver-list label').forEach(l => {
        l.style.borderColor = '#f1f5f9';
        l.style.background  = '#fafafa';
    });
    const el = document.getElementById('dopt-' + id);
    if (el) { el.style.borderColor = '#10b981'; el.style.background = '#f0fdf4'; }
}

function filterApproveDrivers(q) {
    q = q.toLowerCase();
    document.querySelectorAll('#approve-driver-list label').forEach(lbl => {
        lbl.style.display = lbl.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}
</script>

@if($errors->has('km_start') || $errors->has('condition_start'))
<script>document.addEventListener('DOMContentLoaded',()=>{
    window.scrollTo(0, document.querySelector('[action*="demarrer"]')?.offsetTop ?? 0);
});</script>
@endif

@endsection
