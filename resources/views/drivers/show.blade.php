@extends('layouts.dashboard')

@section('title', $driver->full_name)
@section('page-title', 'Fiche Chauffeur')

@section('content')
@php
$statusMap  = ['active'=>['Actif','#10b981','#f0fdf4'],'suspended'=>['Suspendu','#d97706','#fffbeb'],'on_leave'=>['En congé','#64748b','#f8fafc'],'terminated'=>['Licencié','#ef4444','#fef2f2']];
$contractMap= ['permanent'=>'CDI','fixed_term'=>'CDD','interim'=>'Intérim','contractor'=>'Prestataire'];
$assignStatusMap=['planned'=>['Planifiée','#6366f1','#f0f0ff'],'confirmed'=>['Confirmée','#0891b2','#ecfeff'],'in_progress'=>['En cours','#3b82f6','#eff6ff'],'completed'=>['Terminée','#10b981','#f0fdf4'],'cancelled'=>['Annulée','#64748b','#f8fafc']];
$docTypeMap=['license'=>'Permis','national_id'=>'CNI','medical_fitness'=>'Visite médicale','safety_training'=>'Formation','special_habilitation'=>'Habilitation','employment_contract'=>'Contrat','criminal_record'=>'Casier judiciaire','other'=>'Autre'];
$docStatusMap=['valid'=>['Valide','#10b981','#f0fdf4'],'expiring_soon'=>['Expire bientôt','#d97706','#fffbeb'],'expired'=>['Expiré','#ef4444','#fef2f2'],'missing'=>['Manquant','#64748b','#f8fafc']];
$s = $statusMap[$driver->status] ?? ['Inconnu','#64748b','#f8fafc'];
$licenseExpired  = $driver->license_expiry_date?->isPast() ?? false;
$licenseExpiring = !$licenseExpired
    && $driver->license_expiry_date !== null
    && $driver->license_expiry_date->diffInDays(now()) <= 30;
@endphp

<style>
.card{background:#fff;border-radius:.75rem;border:1px solid #e2e8f0;overflow:hidden;margin-bottom:1rem;}
.card-head{padding:.85rem 1.25rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:.6rem;}
.card-title{font-size:.875rem;font-weight:700;color:#0f172a;}
.card-body{padding:1.1rem 1.5rem;}
.dl{display:grid;grid-template-columns:150px 1fr;gap:.5rem .75rem;}
.dt{font-size:.78rem;color:#94a3b8;font-weight:500;}
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
.stat-mini{text-align:center;padding:.75rem;background:#f8fafc;border-radius:.5rem;}
.stat-mini .val{font-size:1.25rem;font-weight:800;color:#0f172a;}
.stat-mini .lbl{font-size:.7rem;color:#64748b;}
</style>

<div style="display:flex;align-items:center;gap:.5rem;font-size:.825rem;color:#94a3b8;margin-bottom:1.25rem;">
    <a href="{{ route('drivers.index') }}" style="color:#10b981;text-decoration:none;font-weight:500;">Chauffeurs</a>
    <span>›</span>
    <span style="color:#374151;">{{ $driver->full_name }}</span>
</div>

{{-- Bandeau profil incomplet (téléphone ou permis manquant) --}}
@if(!$driver->phone || !$driver->license_number)
<div style="padding:.85rem 1.1rem;background:#fffbeb;border:1px solid #fde68a;border-radius:.65rem;margin-bottom:1.25rem;display:flex;gap:.75rem;align-items:center;justify-content:space-between;">
    <div style="display:flex;gap:.65rem;align-items:center;">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" style="flex-shrink:0;color:#d97706;">
            <path d="M12 9v4M12 17h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="2"/>
        </svg>
        <div style="font-size:.83rem;color:#92400e;">
            <strong>Profil incomplet</strong> —
            @if(!$driver->phone && !$driver->license_number)
                Le téléphone et les informations du permis de conduire ne sont pas encore renseignés.
            @elseif(!$driver->phone)
                Le numéro de téléphone n'est pas encore renseigné.
            @else
                Les informations du permis de conduire ne sont pas encore renseignées.
            @endif
        </div>
    </div>
    @can('drivers.edit')
    <a href="{{ route('drivers.edit', $driver) }}" style="flex-shrink:0;padding:.4rem .9rem;background:#d97706;color:#fff;border-radius:.45rem;font-size:.8rem;font-weight:700;text-decoration:none;white-space:nowrap;">
        Compléter la fiche
    </a>
    @endcan
</div>
@endif

<div style="display:grid;grid-template-columns:260px 1fr;gap:1.25rem;align-items:start;">

    {{-- ── Colonne gauche ──────────────────────────────────────────────── --}}
    <div>
        <div class="card">
            <div class="card-body" style="padding:1.5rem;text-align:center;">
                @if($driver->avatar)
                    <img src="{{ Storage::url($driver->avatar) }}"
                         style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid {{ $s[1] }}33;margin:0 auto .75rem;display:block;"
                         alt="">
                @else
                    <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#10b981,#059669);display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:800;color:#fff;margin:0 auto .75rem;">
                        {{ strtoupper(substr($driver->full_name, 0, 2)) }}
                    </div>
                @endif

                <div style="font-size:1rem;font-weight:700;color:#0f172a;">{{ $driver->full_name }}</div>
                <div style="font-family:monospace;font-size:.82rem;color:#64748b;background:#f1f5f9;padding:.15rem .5rem;border-radius:.3rem;display:inline-block;margin:.2rem 0 .6rem;">{{ $driver->matricule }}</div>

                <div style="margin-bottom:.5rem;">
                    <span class="badge" style="background:{{ $s[2] }};color:{{ $s[1] }};">
                        <span style="width:6px;height:6px;border-radius:50%;background:{{ $s[1] }};display:inline-block;"></span>
                        {{ $s[0] }}
                    </span>
                </div>

                @if($driver->trashed())
                    <span class="badge" style="background:#fef2f2;color:#dc2626;">Archivé</span>
                @endif

                {{-- Stats mini --}}
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.5rem;margin-top:.85rem;">
                    <div class="stat-mini">
                        <div class="val">{{ number_format($driver->total_km / 1000, 0) }}k</div>
                        <div class="lbl">km</div>
                    </div>
                    <div class="stat-mini">
                        <div class="val">{{ $driver->total_assignments }}</div>
                        <div class="lbl">missions</div>
                    </div>
                    <div class="stat-mini">
                        <div class="val">{{ $driver->total_infractions }}</div>
                        <div class="lbl">infract.</div>
                    </div>
                </div>
            </div>

            @if(!$driver->trashed())
            <div style="padding:1rem 1.25rem;border-top:1px solid #f1f5f9;display:flex;flex-direction:column;gap:.5rem;">
                @can('drivers.edit')
                <a href="{{ route('drivers.edit', $driver) }}" class="btn btn-primary" style="justify-content:center;">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    Modifier
                </a>

                {{-- Changer statut --}}
                @if($driver->status === 'active')
                <form method="POST" action="{{ route('drivers.toggle-status', $driver) }}"
                      data-confirm="Le chauffeur sera suspendu et ne pourra plus être affecté."
                      data-title="Suspendre {{ $driver->full_name }} ?" data-btn-text="Suspendre" data-btn-color="#d97706">
                    @csrf
                    <input type="hidden" name="status" value="suspended">
                    <button type="submit" class="btn btn-orange" style="width:100%;justify-content:center;">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M10 15V9M14 15V9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        Suspendre
                    </button>
                </form>
                @else
                <form method="POST" action="{{ route('drivers.toggle-status', $driver) }}"
                      data-confirm="Le chauffeur redeviendra actif et pourra être affecté."
                      data-title="Réactiver {{ $driver->full_name }} ?" data-icon="question" data-btn-text="Réactiver" data-btn-color="#10b981">
                    @csrf
                    <input type="hidden" name="status" value="active">
                    <button type="submit" class="btn btn-ghost" style="width:100%;justify-content:center;color:#16a34a;border-color:#bbf7d0;">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        Réactiver
                    </button>
                </form>
                @endif
                @endcan

                @can('drivers.delete')
                <form method="POST" action="{{ route('drivers.destroy', $driver) }}"
                      data-confirm="Ce chauffeur sera archivé. Il pourra être restauré."
                      data-title="Archiver {{ $driver->full_name }} ?" data-btn-text="Archiver">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger" style="width:100%;justify-content:center;">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><polyline points="3,6 5,6 21,6" stroke="currentColor" stroke-width="2"/><path d="M19 6l-1 14H6L5 6M10 11v6M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        Archiver
                    </button>
                </form>
                @endcan

                @if(auth()->user()->hasAnyRole(['super_admin', 'admin']))
                <form method="POST" action="{{ route('drivers.force-destroy', $driver->id) }}"
                      data-confirm="Cette suppression est IRRÉVERSIBLE. Toutes les données du profil seront effacées définitivement."
                      data-title="Supprimer {{ $driver->full_name }} définitivement ?"
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
                @can('drivers.delete')
                <form method="POST" action="{{ route('drivers.restore', $driver->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M3 12a9 9 0 109-9 9 9 0 00-6.3 2.6L3 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M3 3v5h5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        Restaurer
                    </button>
                </form>
                @endcan

                @if(auth()->user()->hasAnyRole(['super_admin', 'admin']))
                <form method="POST" action="{{ route('drivers.force-destroy', $driver->id) }}"
                      data-confirm="Cette suppression est IRRÉVERSIBLE. Toutes les données du profil seront effacées définitivement."
                      data-title="Supprimer {{ $driver->full_name }} définitivement ?"
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

        {{-- Informations personnelles --}}
        <div class="card">
            <div class="card-head">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4" stroke="#10b981" stroke-width="2"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke="#10b981" stroke-width="2" stroke-linecap="round"/></svg>
                <span class="card-title">Informations personnelles</span>
            </div>
            <div class="card-body">
                <div class="dl">
                    <span class="dt">Téléphone</span>
                    <span class="dd">{{ $driver->phone }}</span>
                    <span class="dt">Email</span>
                    <span class="dd">{{ $driver->email ?? '—' }}</span>
                    <span class="dt">Adresse</span>
                    <span class="dd">{{ $driver->address ?? '—' }}</span>
                    <span class="dt">Date de naissance</span>
                    <span class="dd">{{ $driver->date_of_birth ? $driver->date_of_birth->isoFormat('D MMMM YYYY') : '—' }}</span>
                    <span class="dt">Embauché le</span>
                    <span class="dd">{{ $driver->hire_date->isoFormat('D MMMM YYYY') }}</span>
                    <span class="dt">Contrat</span>
                    <span class="dd">{{ $contractMap[$driver->contract_type] ?? $driver->contract_type }}
                        @if($driver->contract_end_date)
                        <span style="font-size:.78rem;color:#94a3b8;">· jusqu'au {{ $driver->contract_end_date->isoFormat('D MMM YYYY') }}</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- Permis de conduire --}}
        <div class="card">
            <div class="card-head">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" stroke="#d97706" stroke-width="2"/><circle cx="8" cy="12" r="2" stroke="#d97706" stroke-width="1.5"/><path d="M14 9h4M14 12h4M14 15h2" stroke="#d97706" stroke-width="1.5" stroke-linecap="round"/></svg>
                <span class="card-title">Permis de conduire</span>
            </div>
            <div class="card-body">
                <div class="dl">
                    <span class="dt">N° permis</span>
                    <span class="dd" style="font-family:monospace;">{{ $driver->license_number }}</span>
                    <span class="dt">Catégories</span>
                    <span class="dd">
                        @foreach($driver->license_categories ?? [] as $cat)
                        <span class="badge" style="background:#eff6ff;color:#1d4ed8;margin-right:.2rem;">{{ $cat }}</span>
                        @endforeach
                    </span>
                    <span class="dt">Expiration</span>
                    <span class="dd" style="color:{{ $licenseExpired ? '#dc2626' : ($licenseExpiring ? '#d97706' : '#0f172a') }};font-weight:{{ ($licenseExpired||$licenseExpiring)?'700':'500' }};">
                        {{ $driver->license_expiry_date?->isoFormat('D MMMM YYYY') ?? '—' }}
                        @if($licenseExpired) <span class="badge" style="background:#fef2f2;color:#dc2626;margin-left:.3rem;">Expiré</span>
                        @elseif($licenseExpiring) <span class="badge" style="background:#fffbeb;color:#d97706;margin-left:.3rem;">Expire dans {{ $driver->license_expiry_date?->diffInDays(now()) }} j</span>
                        @endif
                    </span>
                    <span class="dt">Autorité</span>
                    <span class="dd">{{ $driver->license_issuing_authority ?? '—' }}</span>
                </div>
                @if($driver->suspension_reason)
                <div style="margin-top:.75rem;background:#fffbeb;border:1px solid #fde68a;border-radius:.4rem;padding:.55rem .75rem;font-size:.8rem;color:#92400e;">
                    <strong>Motif :</strong> {{ $driver->suspension_reason }}
                </div>
                @endif
            </div>
        </div>

        {{-- Affectation en cours --}}
        @if($driver->activeAssignment)
        <div class="card">
            <div class="card-head">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3" fill="#3b82f6"/><circle cx="12" cy="12" r="9" stroke="#3b82f6" stroke-width="2"/></svg>
                <span class="card-title" style="color:#3b82f6;">Mission en cours</span>
            </div>
            <div class="card-body">
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <div>
                        <div style="font-weight:600;font-size:.9rem;">{{ $driver->activeAssignment->vehicle->plate ?? '—' }} — {{ $driver->activeAssignment->vehicle->brand ?? '' }} {{ $driver->activeAssignment->vehicle->model ?? '' }}</div>
                        <div style="font-size:.78rem;color:#64748b;margin-top:.2rem;">
                            Depuis le {{ $driver->activeAssignment->datetime_start->isoFormat('D MMM YYYY à HH:mm') }}
                        </div>
                    </div>
                    <span class="badge" style="background:#eff6ff;color:#3b82f6;">En cours</span>
                </div>
            </div>
        </div>
        @endif

        {{-- Documents --}}
        <div class="card">
            <div class="card-head" style="justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:.6rem;">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="#6366f1" stroke-width="2"/><path d="M14 2v6h6" stroke="#6366f1" stroke-width="2" stroke-linecap="round"/></svg>
                    <span class="card-title">Documents administratifs</span>
                </div>
                <div style="display:flex;align-items:center;gap:.75rem;">
                    <span style="font-size:.75rem;color:#94a3b8;">{{ $driver->documents->count() }} doc(s)</span>
                    @can('drivers.edit')
                    @if(!$driver->trashed())
                    <a href="{{ route('drivers.edit', $driver) }}" style="font-size:.73rem;font-weight:600;color:#6366f1;text-decoration:none;border:1px solid #c7d2fe;background:#f0f0ff;padding:.15rem .55rem;border-radius:99px;">
                        + Ajouter / modifier
                    </a>
                    @endif
                    @endcan
                </div>
            </div>
            <div class="table-mini">
                @if($driver->documents->isEmpty())
                    <p style="text-align:center;padding:1.25rem;color:#94a3b8;font-size:.82rem;">Aucun document enregistré. <a href="{{ route('drivers.edit', $driver) }}" style="color:#6366f1;">Ajouter →</a></p>
                @else
                <table>
                    <thead><tr><th>Type</th><th>Référence</th><th>Délivrance</th><th>Expiration</th><th>Statut</th><th></th></tr></thead>
                    <tbody>
                    @foreach($driver->documents as $doc)
                    @php $ds = $docStatusMap[$doc->status] ?? ['—','#64748b','#f8fafc']; @endphp
                    <tr>
                        <td style="font-weight:600;font-size:.82rem;">{{ $docTypeMap[$doc->type] ?? $doc->type }}</td>
                        <td style="font-family:monospace;font-size:.78rem;color:#64748b;">{{ $doc->document_number ?? '—' }}</td>
                        <td style="font-size:.78rem;color:#64748b;">{{ $doc->issue_date ? $doc->issue_date->isoFormat('D MMM YYYY') : '—' }}</td>
                        <td style="font-size:.78rem;">{{ $doc->expiry_date ? $doc->expiry_date->isoFormat('D MMM YYYY') : '—' }}</td>
                        <td><span class="badge" style="background:{{ $ds[2] }};color:{{ $ds[1] }};">{{ $ds[0] }}</span></td>
                        <td>
                            @if($doc->file_path)
                            <a href="{{ Storage::url($doc->file_path) }}" target="_blank"
                               style="display:inline-flex;align-items:center;gap:.25rem;font-size:.72rem;font-weight:600;color:#10b981;text-decoration:none;padding:.2rem .5rem;border:1px solid #bbf7d0;background:#f0fdf4;border-radius:99px;white-space:nowrap;">
                                <svg width="10" height="10" fill="none" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M15 3h6v6M10 14L21 3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                Voir
                            </a>
                            @else
                            <span style="font-size:.72rem;color:#cbd5e1;">—</span>
                            @endif
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
            <div class="card-head">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2" stroke="#10b981" stroke-width="2"/><path d="M8 2v4M16 2v4M3 10h18" stroke="#10b981" stroke-width="2" stroke-linecap="round"/></svg>
                <span class="card-title">Historique des missions</span>
                <span style="margin-left:auto;font-size:.75rem;color:#94a3b8;">10 dernières</span>
            </div>
            <div class="table-mini">
                @if($driver->assignments->isEmpty())
                    <p style="text-align:center;padding:1.25rem;color:#94a3b8;font-size:.82rem;">Aucune mission.</p>
                @else
                <table>
                    <thead><tr><th>Véhicule</th><th>Départ</th><th>Retour prévu</th><th>Statut</th></tr></thead>
                    <tbody>
                    @foreach($driver->assignments as $ass)
                    @php $as = $assignStatusMap[$ass->status] ?? ['—','#64748b','#f8fafc']; @endphp
                    <tr>
                        <td style="font-weight:500;font-family:monospace;font-size:.82rem;">{{ $ass->vehicle->plate ?? '—' }}</td>
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

        {{-- Historique des infractions --}}
        <div class="card">
            <div class="card-head">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M12 9v4M12 17h.01" stroke="#ef4444" stroke-width="2" stroke-linecap="round"/><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="#ef4444" stroke-width="2"/></svg>
                <span class="card-title">Infractions</span>
                <span style="margin-left:auto;font-size:.75rem;color:#94a3b8;">10 dernières</span>
            </div>
            @php
            $infrTypeMap = ['speeding'=>'Excès de vitesse','red_light'=>'Feu rouge','illegal_parking'=>'Stationnement illicite','no_seatbelt'=>'Ceinture','phone_use'=>'Téléphone au volant','drunk_driving'=>'Conduite en état d\'ivresse','overloading'=>'Surcharge','no_stop'=>'Stop non respecté','wrong_way'=>'Contresens','other'=>'Autre'];
            $infrPayMap  = ['unpaid'=>['Non payée','#ef4444','#fef2f2'],'paid_by_company'=>['Payée (société)','#10b981','#f0fdf4'],'charged_to_driver'=>['Imputée chauffeur','#d97706','#fffbeb'],'paid'=>['Payée','#10b981','#f0fdf4']];
            @endphp
            <div class="table-mini">
                @if($driver->infractions->isEmpty())
                    <p style="text-align:center;padding:1.25rem;color:#94a3b8;font-size:.82rem;">Aucune infraction enregistrée.</p>
                @else
                <table>
                    <thead><tr><th>Type</th><th>Véhicule</th><th>Date</th><th>Amende</th><th>Paiement</th></tr></thead>
                    <tbody>
                    @foreach($driver->infractions as $inf)
                    @php $ip = $infrPayMap[$inf->payment_status] ?? ['—','#64748b','#f8fafc']; @endphp
                    <tr>
                        <td style="font-weight:500;font-size:.82rem;">{{ $infrTypeMap[$inf->type] ?? ucfirst(str_replace('_',' ',$inf->type)) }}</td>
                        <td style="font-family:monospace;font-size:.78rem;color:#64748b;">{{ $inf->vehicle?->plate ?? '—' }}</td>
                        <td style="font-size:.78rem;">{{ $inf->datetime_occurred ? \Carbon\Carbon::parse($inf->datetime_occurred)->isoFormat('D MMM YYYY') : '—' }}</td>
                        <td style="font-size:.82rem;font-weight:600;color:#dc2626;">{{ $inf->fine_amount ? number_format($inf->fine_amount, 0, ',', ' ') . ' FCFA' : '—' }}</td>
                        <td><span class="badge" style="background:{{ $ip[2] }};color:{{ $ip[1] }};">{{ $ip[0] }}</span></td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>

        {{-- Historique des sinistres --}}
        <div class="card">
            <div class="card-head">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="#8b5cf6" stroke-width="2"/><path d="M9 12l2 2 4-4" stroke="#8b5cf6" stroke-width="2" stroke-linecap="round"/></svg>
                <span class="card-title">Sinistres</span>
                <span style="margin-left:auto;font-size:.75rem;color:#94a3b8;">10 derniers</span>
            </div>
            @php
            $incTypeMap = ['accident'=>'Accident','breakdown'=>'Panne mécanique','flat_tire'=>'Crevaison','electrical_fault'=>'Panne électrique','body_damage'=>'Dommage carrosserie','theft_attempt'=>'Tentative de vol','theft'=>'Vol','flood_damage'=>'Inondation','fire'=>'Incendie','vandalism'=>'Vandalisme','other'=>'Autre'];
            $incSevMap  = ['minor'=>['Léger','#10b981','#f0fdf4'],'moderate'=>['Modéré','#d97706','#fffbeb'],'major'=>['Grave','#ef4444','#fef2f2'],'total_loss'=>['Perte totale','#7c3aed','#f5f3ff']];
            $incStatMap = ['open'=>['Ouvert','#3b82f6','#eff6ff'],'at_garage'=>['Au garage','#d97706','#fffbeb'],'repaired'=>['Réparé','#10b981','#f0fdf4'],'total_loss'=>['Épave','#7c3aed','#f5f3ff'],'closed'=>['Clôturé','#64748b','#f8fafc']];
            @endphp
            <div class="table-mini">
                @if($driver->incidents->isEmpty())
                    <p style="text-align:center;padding:1.25rem;color:#94a3b8;font-size:.82rem;">Aucun sinistre enregistré.</p>
                @else
                <table>
                    <thead><tr><th>Type</th><th>Véhicule</th><th>Date</th><th>Gravité</th><th>Statut</th></tr></thead>
                    <tbody>
                    @foreach($driver->incidents as $inc)
                    @php
                        $isev = $incSevMap[$inc->severity]  ?? ['—','#64748b','#f8fafc'];
                        $ist  = $incStatMap[$inc->status]   ?? ['—','#64748b','#f8fafc'];
                    @endphp
                    <tr>
                        <td style="font-weight:500;font-size:.82rem;">{{ $incTypeMap[$inc->type] ?? ucfirst(str_replace('_',' ',$inc->type)) }}</td>
                        <td style="font-family:monospace;font-size:.78rem;color:#64748b;">{{ $inc->vehicle?->plate ?? '—' }}</td>
                        <td style="font-size:.78rem;">{{ \Carbon\Carbon::parse($inc->datetime_occurred)->isoFormat('D MMM YYYY') }}</td>
                        <td><span class="badge" style="background:{{ $isev[2] }};color:{{ $isev[1] }};">{{ $isev[0] }}</span></td>
                        <td><span class="badge" style="background:{{ $ist[2] }};color:{{ $ist[1] }};">{{ $ist[0] }}</span></td>
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
