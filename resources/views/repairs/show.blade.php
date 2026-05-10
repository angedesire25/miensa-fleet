@extends('layouts.dashboard')

@section('title', 'Réparation #' . $repair->id)
@section('page-title', 'Réparation #' . $repair->id)

@section('content')
<style>
.card{background:#fff;border-radius:.75rem;border:1px solid #e2e8f0;overflow:hidden;margin-bottom:1.25rem;}
.card-head{padding:.85rem 1.25rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;gap:.6rem;}
.card-title{font-size:.9rem;font-weight:700;color:#0f172a;}
.badge{display:inline-flex;align-items:center;gap:.25rem;padding:.25rem .65rem;border-radius:99px;font-size:.75rem;font-weight:600;}
.btn{padding:.45rem .9rem;border-radius:.45rem;font-size:.82rem;font-weight:600;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;transition:opacity .15s;}
.btn-primary{background:linear-gradient(135deg,#10b981,#059669);color:#fff;}
.btn-ghost{background:#f8fafc;color:#374151;border:1.5px solid #e2e8f0;}
.btn-ghost:hover{background:#f1f5f9;}
.btn-pdf{background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;}
.detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;padding:1.25rem;}
.detail-item label{font-size:.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;display:block;margin-bottom:.25rem;}
.detail-item span{font-size:.875rem;color:#0f172a;}
.form-control{padding:.5rem .75rem;border:1.5px solid #e2e8f0;border-radius:.45rem;font-size:.855rem;color:#0f172a;outline:none;background:#fff;width:100%;box-sizing:border-box;}
.form-control:focus{border-color:#10b981;}
/* ── Fault codes ── */
.fc-tbl{width:100%;border-collapse:collapse;font-size:.82rem;}
.fc-tbl th{font-size:.7rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.04em;padding:.45rem .7rem;text-align:left;border-bottom:1.5px solid #e2e8f0;white-space:nowrap;}
.fc-tbl td{padding:.55rem .7rem;border-bottom:1px solid #f1f5f9;vertical-align:middle;}
.fc-tbl tr:last-child td{border-bottom:none;}
.code-badge{display:inline-block;padding:.2rem .55rem;border-radius:99px;font-size:.74rem;font-weight:700;letter-spacing:.04em;}
.rs-pending{background:#fef3c7;color:#92400e;}
.rs-resolved{background:#d1fae5;color:#065f46;}
.rs-partial{background:#e0e7ff;color:#3730a3;}
.rs-deferred{background:#f3f4f6;color:#374151;}
.rs-not_covered{background:#fee2e2;color:#991b1b;}
/* ── Signatures ── */
.sig-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem;padding:1.25rem;}
.sig-box{border:1.5px dashed #cbd5e1;border-radius:.6rem;padding:1rem;display:flex;flex-direction:column;align-items:center;gap:.6rem;min-height:160px;background:#fafafa;}
.sig-box canvas{border:1px solid #e2e8f0;border-radius:.4rem;background:#fff;touch-action:none;cursor:crosshair;}
.sig-box img{border-radius:.4rem;border:1px solid #e2e8f0;max-width:100%;}
.sig-label{font-size:.78rem;font-weight:600;color:#374151;text-align:center;}
.sig-note{font-size:.72rem;color:#94a3b8;text-align:center;}
</style>

@php
$rStatusColors = [
    'sent'               => ['#eff6ff','#1e40af'],
    'diagnosing'         => ['#fef3c7','#92400e'],
    'repairing'          => ['#fff7ed','#9a3412'],
    'waiting_parts'      => ['#ede9fe','#5b21b6'],
    'completed'          => ['#f0fdf4','#166534'],
    'returned'           => ['#f0fdf4','#166534'],
    'returned_with_issue'=> ['#fee2e2','#991b1b'],
];
$rStatusLabels = [
    'sent'               => 'Envoyé',
    'diagnosing'         => 'Diagnostic en cours',
    'repairing'          => 'En réparation',
    'waiting_parts'      => 'Attente pièces',
    'completed'          => 'Réparation terminée',
    'returned'           => 'Véhicule retourné',
    'returned_with_issue'=> 'Retour avec problème persistant',
];
[$rBg,$rFg] = $rStatusColors[$repair->status] ?? ['#f8fafc','#64748b'];
$isInProgress = in_array($repair->status, ['sent','diagnosing','repairing','waiting_parts','completed']);
@endphp

{{-- En-tête --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem;">
    <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
        <span class="badge" style="background:{{ $rBg }};color:{{ $rFg }};">{{ $rStatusLabels[$repair->status] ?? $repair->status }}</span>
        @if($repair->di_number)
            <span class="badge" style="background:#f0f9ff;color:#0369a1;font-family:monospace;font-size:.78rem;">{{ $repair->di_number }}</span>
        @endif
        @if($repair->same_issue_recurrence)
            <span class="badge" style="background:#fee2e2;color:#991b1b;">⚠ Récurrence de panne</span>
        @endif
        @if($repair->is_overdue)
            <span class="badge" style="background:#fef3c7;color:#92400e;">⏰ En retard</span>
        @endif
    </div>
    <div style="display:flex;gap:.6rem;flex-wrap:wrap;">
        <a href="{{ route('repairs.index') }}" class="btn btn-ghost">← Retour</a>
        @if($repair->incident)
            <a href="{{ route('incidents.show', $repair->incident) }}" class="btn btn-ghost">Sinistre #{{ $repair->incident_id }}</a>
        @endif
        @can('repairs.edit')
            <a href="{{ route('repairs.edit', $repair) }}" class="btn btn-ghost">✏️ Modifier</a>
        @endcan
        <a href="{{ route('repairs.di-pdf', $repair) }}" class="btn btn-primary" target="_blank"
           style="padding:.55rem 1.25rem;font-weight:700;letter-spacing:.01em;">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="currentColor" stroke-width="1.8"/>
                <polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="1.8"/>
                <line x1="16" y1="13" x2="8" y2="13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                <line x1="16" y1="17" x2="8" y2="17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            Télécharger la fiche DI (PDF)
        </a>
    </div>
</div>

@if($repair->is_overdue)
<div style="background:#fef3c7;border:1px solid #fde68a;border-radius:.6rem;padding:.9rem 1.1rem;margin-bottom:1.25rem;color:#92400e;font-size:.875rem;">
    ⏰ Date de disponibilité dépassée : le véhicule était attendu le
    <strong>{{ $repair->availability_date_requested?->format('d/m/Y') }}</strong>
    et n'est pas encore sorti
    ({{ $repair->immobilization_days }} jour(s) d'immobilisation).
</div>
@endif

@if($repair->same_issue_recurrence && $repair->previousRepair)
<div style="background:#fef2f2;border:1px solid #fecaca;border-radius:.6rem;padding:.9rem 1.1rem;margin-bottom:1.25rem;color:#991b1b;font-size:.875rem;">
    ⚠ Récurrence de panne détectée — même problème que la réparation
    <a href="{{ route('repairs.show', $repair->previousRepair) }}" style="color:#991b1b;font-weight:600;">#{{ $repair->previous_repair_id }}</a>
    ({{ $repair->recurrence_delay_days ?? '?' }} jour(s) plus tôt).
</div>
@endif

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.25rem;">
    {{-- Colonne principale --}}
    <div>
        {{-- Détails départ --}}
        <div class="card">
            <div class="card-head"><span class="card-title">Envoi au garage</span></div>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>Véhicule</label>
                    @if($repair->vehicle)
                        <a href="{{ route('vehicles.show', $repair->vehicle) }}" style="color:#10b981;font-weight:600;text-decoration:none;">
                            {{ $repair->vehicle->plate }} — {{ $repair->vehicle->brand }} {{ $repair->vehicle->model }}
                        </a>
                    @else <span style="color:#94a3b8;">—</span> @endif
                </div>
                <div class="detail-item">
                    <label>Garage</label>
                    @if($repair->garage)
                        <a href="{{ route('garages.show', $repair->garage) }}" style="color:#10b981;font-weight:600;text-decoration:none;">{{ $repair->garage->name }}</a>
                    @else <span style="color:#94a3b8;">—</span> @endif
                </div>
                <div class="detail-item">
                    <label>Date d'envoi</label>
                    <span>{{ $repair->datetime_sent?->format('d/m/Y à H:i') ?? '—' }}</span>
                </div>
                <div class="detail-item">
                    <label>Kilométrage départ</label>
                    <span>{{ $repair->km_at_departure ? number_format($repair->km_at_departure, 0, ',', ' ') . ' km' : '—' }}</span>
                </div>
                <div class="detail-item">
                    <label>Envoyé par</label>
                    <span>{{ $repair->sentBy?->name ?? '—' }}</span>
                </div>
                <div class="detail-item">
                    <label>Type de réparation</label>
                    <span>{{ ucfirst(str_replace('_',' ',$repair->repair_type)) }}</span>
                </div>
            </div>
            @if($repair->condition_at_departure)
            <div style="padding:0 1.25rem 1.25rem;">
                <label style="font-size:.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;display:block;margin-bottom:.4rem;">État au départ</label>
                <p style="font-size:.855rem;color:#374151;margin:0;white-space:pre-wrap;">{{ $repair->condition_at_departure }}</p>
            </div>
            @endif
        </div>

        {{-- Diagnostic et travaux --}}
        @if($repair->diagnosis || $repair->work_performed)
        <div class="card">
            <div class="card-head"><span class="card-title">Diagnostic & travaux effectués</span></div>
            <div style="padding:1.25rem;display:flex;flex-direction:column;gap:1rem;">
                @if($repair->diagnosis)
                <div>
                    <label style="font-size:.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;display:block;margin-bottom:.4rem;">Diagnostic</label>
                    <p style="font-size:.855rem;color:#374151;margin:0;white-space:pre-wrap;">{{ $repair->diagnosis }}</p>
                </div>
                @endif
                @if($repair->work_performed)
                <div>
                    <label style="font-size:.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;display:block;margin-bottom:.4rem;">Travaux effectués</label>
                    <p style="font-size:.855rem;color:#374151;margin:0;white-space:pre-wrap;">{{ $repair->work_performed }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- ── Inventaire des dysfonctionnements ─────────────────────────── --}}
        @if($repair->faultCodes->isNotEmpty() || $repair->di_number)
        <div class="card">
            <div class="card-head">
                <span class="card-title">Inventaire des dysfonctionnements</span>
                @if($repair->faultCodes->isNotEmpty())
                <span style="font-size:.75rem;color:#94a3b8;">{{ $repair->faultCodes->count() }} code(s) ·
                    {{ $repair->resolvedFaults->count() }} résolu(s) ·
                    {{ $repair->pendingFaults->count() }} en attente
                </span>
                @endif
            </div>
            @if($repair->faultCodes->isEmpty())
                <div style="padding:1.25rem;text-align:center;color:#94a3b8;font-size:.83rem;">Aucun code de panne enregistré.</div>
            @else
            <div style="overflow-x:auto;">
                <table class="fc-tbl">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Catégorie</th>
                            <th>Libellé</th>
                            <th>Diagnostic garage</th>
                            <th>Travaux réalisés</th>
                            <th>Statut</th>
                            <th>Coût</th>
                            @can('repairs.edit') <th></th> @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($repair->faultCodes as $fc)
                        @php
                        $rsClass = 'rs-' . $fc->resolution_status;
                        $catColor = ['anomaly'=>'#eff6ff:#1e40af','breakdown'=>'#fff7ed:#9a3412','wear'=>'#f0fdf4:#166534','accident'=>'#fef2f2:#991b1b','other'=>'#f8fafc:#374151'];
                        [$catBg,$catFg] = explode(':', $catColor[$fc->category] ?? '#f8fafc:#374151');
                        @endphp
                        <tr>
                            <td><span class="code-badge" style="background:#eff6ff;color:#1e40af;">{{ $fc->code }}</span></td>
                            <td><span class="code-badge" style="background:{{ $catBg }};color:{{ $catFg }};">{{ $fc->category_label }}</span></td>
                            <td style="max-width:200px;">{{ $fc->label }}</td>
                            <td style="max-width:200px;color:#374151;font-size:.8rem;">{{ $fc->garage_diagnosis ?: '—' }}</td>
                            <td style="max-width:200px;color:#374151;font-size:.8rem;">{{ $fc->work_performed ?: '—' }}</td>
                            <td><span class="code-badge {{ $rsClass }}">{{ $fc->resolution_label }}</span></td>
                            <td style="white-space:nowrap;">{{ $fc->fault_cost ? number_format($fc->fault_cost, 0, ',', ' ') . ' F' : '—' }}</td>
                            @can('repairs.edit')
                            <td>
                                <button type="button" class="btn btn-ghost"
                                        style="padding:.25rem .55rem;font-size:.72rem;"
                                        onclick="openDiagModal({{ $fc->id }}, {{ json_encode($fc->garage_diagnosis) }}, {{ json_encode($fc->work_performed) }}, '{{ $fc->resolution_status }}', '{{ $fc->fault_cost }}')">
                                    ✏️
                                </button>
                            </td>
                            @endcan
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        @endif

        {{-- ── Signatures ─────────────────────────────────────────────────── --}}
        <div class="card" id="signatures-card">
            <div class="card-head">
                <span class="card-title">Signatures</span>
            </div>
            <div class="sig-grid">
                @php
                $sigZones = [
                    ['key'=>'company',      'label'=>'Signature Geomatos — Entrée'],
                    ['key'=>'garage',       'label'=>'Signature Atelier — Entrée'],
                    ['key'=>'company_exit', 'label'=>'Signature Geomatos — Sortie'],
                    ['key'=>'garage_exit',  'label'=>'Signature Atelier — Sortie'],
                ];
                @endphp
                @foreach($sigZones as $zone)
                @php $field = 'signature_' . $zone['key'] . '_path'; $sigPath = $repair->$field; @endphp
                <div class="sig-box" id="sigbox-{{ $zone['key'] }}">
                    <div class="sig-label">{{ $zone['label'] }}</div>
                    @if($sigPath)
                        <img src="{{ asset('storage/' . $sigPath) }}" alt="Signature" style="max-height:100px;">
                        @can('repairs.edit')
                        <button type="button" class="btn btn-ghost" style="font-size:.75rem;padding:.3rem .7rem;"
                                onclick="showSigCanvas('{{ $zone['key'] }}')">Modifier</button>
                        @endcan
                    @else
                        @can('repairs.edit')
                        <canvas id="canvas-{{ $zone['key'] }}" width="260" height="100"></canvas>
                        <div style="display:flex;gap:.5rem;">
                            <button type="button" class="btn btn-primary" style="font-size:.75rem;padding:.35rem .8rem;"
                                    onclick="saveSig('{{ $zone['key'] }}', '{{ route('repairs.signature', $repair) }}')">
                                Enregistrer
                            </button>
                            <button type="button" class="btn btn-ghost" style="font-size:.75rem;padding:.35rem .7rem;"
                                    onclick="clearSig('{{ $zone['key'] }}')">Effacer</button>
                        </div>
                        @else
                        <div class="sig-note">Non signé</div>
                        @endcan
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- Retour --}}
        @if($repair->datetime_returned)
        <div class="card">
            <div class="card-head"><span class="card-title">Retour du véhicule</span></div>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>Date de retour</label>
                    <span>{{ $repair->datetime_returned?->format('d/m/Y à H:i') }}</span>
                </div>
                <div class="detail-item">
                    <label>Kilométrage retour</label>
                    <span>{{ $repair->km_at_return ? number_format($repair->km_at_return, 0, ',', ' ') . ' km' : '—' }}</span>
                </div>
                <div class="detail-item">
                    <label>Reçu par</label>
                    <span>{{ $repair->receivedBy?->name ?? '—' }}</span>
                </div>
                <div class="detail-item">
                    <label>Durée totale</label>
                    <span>{{ $repair->duration_days }} jour(s)</span>
                </div>
            </div>
            @if($repair->condition_at_return)
            <div style="padding:0 1.25rem 1.25rem;">
                <label style="font-size:.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;display:block;margin-bottom:.4rem;">État au retour</label>
                <p style="font-size:.855rem;color:#374151;margin:0;white-space:pre-wrap;">{{ $repair->condition_at_return }}</p>
            </div>
            @endif
        </div>
        @endif

        {{-- Photos de la réparation --}}
        @if($repair->photos->isNotEmpty())
        <div class="card">
            <div class="card-head"><span class="card-title">Photos</span></div>
            <div style="padding:1.25rem;">
                <div style="display:flex;flex-wrap:wrap;gap:.75rem;">
                    @foreach($repair->photos as $photo)
                    <div style="position:relative;">
                        <a href="{{ asset('storage/' . $photo->file_path) }}" target="_blank">
                            <img src="{{ asset('storage/' . $photo->file_path) }}" alt=""
                                 style="width:120px;height:90px;object-fit:cover;border-radius:.5rem;border:1.5px solid #e2e8f0;display:block;">
                        </a>
                        <div style="font-size:.68rem;color:#64748b;margin-top:.25rem;text-align:center;">
                            {{ $photo->context === 'repair_in_progress' ? 'En cours' : 'Après' }}
                        </div>
                        @can('repairs.edit')
                        <form method="POST" action="{{ route('repairs.delete-photo', $repair) }}"
                              onsubmit="return confirm('Supprimer cette photo ?')"
                              style="position:absolute;top:4px;right:4px;">
                            @csrf @method('DELETE')
                            <input type="hidden" name="photo_id" value="{{ $photo->id }}">
                            <button type="submit" style="background:rgba(239,68,68,.9);border:none;border-radius:50%;width:20px;height:20px;color:#fff;cursor:pointer;font-size:.8rem;line-height:1;display:flex;align-items:center;justify-content:center;">×</button>
                        </form>
                        @endcan
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Mise à jour du statut (en cours uniquement) --}}
        @can('repairs.edit')
        @if($isInProgress)
        <div class="card">
            <div class="card-head"><span class="card-title">Mettre à jour le statut</span></div>
            <form method="POST" action="{{ route('repairs.update-status', $repair) }}" enctype="multipart/form-data" style="padding:1.25rem;display:flex;flex-direction:column;gap:.85rem;">
                @csrf
                <div>
                    <label style="font-size:.8rem;font-weight:600;color:#374151;display:block;margin-bottom:.35rem;">Nouvelle étape</label>
                    <select name="status" class="form-control">
                        <option value="sent"          @selected($repair->status==='sent')>Envoyé</option>
                        <option value="diagnosing"    @selected($repair->status==='diagnosing')>Diagnostic en cours</option>
                        <option value="repairing"     @selected($repair->status==='repairing')>En réparation</option>
                        <option value="waiting_parts" @selected($repair->status==='waiting_parts')>Attente de pièces</option>
                        <option value="completed"     @selected($repair->status==='completed')>Réparation terminée (avant retour)</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:.8rem;font-weight:600;color:#374151;display:block;margin-bottom:.35rem;">Diagnostic / notes</label>
                    <textarea name="diagnosis" class="form-control" rows="2" placeholder="Observations du garage…">{{ old('diagnosis', $repair->diagnosis) }}</textarea>
                </div>
                <div>
                    @include('partials._photo_upload', [
                        'contextOptions' => ['repair_in_progress' => 'En cours', 'repair_after' => 'Après réparation'],
                        'defaultContext' => 'repair_in_progress',
                        'existingPhotos' => collect(),
                    ])
                </div>
                <div style="display:flex;justify-content:flex-end;">
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                </div>
            </form>
        </div>

        {{-- Enregistrer le retour --}}
        <div class="card">
            <div class="card-head"><span class="card-title">Enregistrer le retour du véhicule</span></div>
            <form method="POST" action="{{ route('repairs.return-from-garage', $repair) }}" enctype="multipart/form-data" style="padding:1.25rem;display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
                @csrf
                <div>
                    <label style="font-size:.8rem;font-weight:600;color:#374151;display:block;margin-bottom:.35rem;">Date de retour <span style="color:#ef4444">*</span></label>
                    <input type="datetime-local" name="datetime_returned" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                </div>
                <div>
                    <label style="font-size:.8rem;font-weight:600;color:#374151;display:block;margin-bottom:.35rem;">Kilométrage au retour</label>
                    <input type="number" name="km_at_return" class="form-control" min="0" placeholder="km">
                </div>
                <div>
                    <label style="font-size:.8rem;font-weight:600;color:#374151;display:block;margin-bottom:.35rem;">Montant facture (FCFA)</label>
                    <input type="number" name="invoice_amount" class="form-control" min="0" step="1000">
                </div>
                <div>
                    <label style="font-size:.8rem;font-weight:600;color:#374151;display:block;margin-bottom:.35rem;">N° facture</label>
                    <input type="text" name="invoice_number" class="form-control" placeholder="FAC-2024-001">
                </div>
                <div>
                    <label style="font-size:.8rem;font-weight:600;color:#374151;display:block;margin-bottom:.35rem;">Garantie (mois)</label>
                    <input type="number" name="warranty_months" class="form-control" min="0" max="120" placeholder="ex. 12">
                </div>
                <div>
                    <label style="font-size:.8rem;font-weight:600;color:#374151;display:block;margin-bottom:.35rem;">État au retour</label>
                    <input type="text" name="condition_at_return" class="form-control" placeholder="Bon état / réserves…">
                </div>
                <div style="grid-column:1/-1;">
                    <label style="font-size:.8rem;font-weight:600;color:#374151;display:block;margin-bottom:.35rem;">Travaux effectués <span style="color:#ef4444">*</span></label>
                    <textarea name="work_performed" class="form-control" rows="3" placeholder="Description des travaux réalisés par le garage…" required></textarea>
                </div>
                <div style="grid-column:1/-1;">
                    <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer;">
                        <input type="hidden" name="has_persistent_issue" value="0">
                        <input type="checkbox" name="has_persistent_issue" value="1" style="width:16px;height:16px;accent-color:#ef4444;">
                        <span style="font-size:.855rem;color:#374151;font-weight:500;">Problème persistant (rouvre le sinistre)</span>
                    </label>
                </div>
                <div style="grid-column:1/-1;">
                    @include('partials._photo_upload', [
                        'contextOptions' => ['repair_in_progress' => 'En cours', 'repair_after' => 'Après réparation'],
                        'defaultContext' => 'repair_after',
                        'existingPhotos' => collect(),
                    ])
                </div>
                <div style="grid-column:1/-1;display:flex;justify-content:flex-end;">
                    <button type="submit" class="btn btn-primary">Enregistrer le retour</button>
                </div>
            </form>
        </div>
        @endif
        @endcan
    </div>

    {{-- Colonne latérale --}}
    <div>
        {{-- Facturation --}}
        <div class="card">
            <div class="card-head"><span class="card-title">Facturation</span></div>
            <div style="padding:1.25rem;display:flex;flex-direction:column;gap:.75rem;">
                <div>
                    <div style="font-size:.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;">Devis</div>
                    <div style="font-size:1rem;font-weight:700;color:#0f172a;margin-top:.2rem;">
                        {{ $repair->quote_amount ? number_format($repair->quote_amount, 0, ',', ' ') . ' FCFA' : '—' }}
                    </div>
                </div>
                <div>
                    <div style="font-size:.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;">Facture</div>
                    <div style="font-size:1rem;font-weight:700;color:#0f172a;margin-top:.2rem;">
                        {{ $repair->invoice_amount ? number_format($repair->invoice_amount, 0, ',', ' ') . ' FCFA' : '—' }}
                    </div>
                </div>
                @if($repair->invoice_number)
                <div>
                    <div style="font-size:.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;">N° Facture</div>
                    <div style="font-size:.875rem;color:#0f172a;margin-top:.2rem;">{{ $repair->invoice_number }}</div>
                </div>
                @endif
                @if($repair->warranty_months)
                <div style="border-top:1px solid #f1f5f9;padding-top:.75rem;">
                    <div style="font-size:.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;">Garantie</div>
                    <div style="font-size:.875rem;color:#0f172a;margin-top:.2rem;">
                        {{ $repair->warranty_months }} mois
                        @if($repair->warranty_expiry)
                            <span style="font-size:.78rem;color:#64748b;">(jusqu'au {{ $repair->warranty_expiry->format('d/m/Y') }})</span>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Pièces remplacées --}}
        @if($repair->partsReplaced->isNotEmpty())
        <div class="card">
            <div class="card-head"><span class="card-title">Pièces remplacées</span></div>
            <div style="padding:1.25rem;display:flex;flex-direction:column;gap:.6rem;">
                @foreach($repair->partsReplaced as $part)
                <div style="padding:.65rem;background:#f8fafc;border-radius:.4rem;font-size:.8rem;">
                    <div style="font-weight:600;color:#0f172a;">{{ $part->part_name }}</div>
                    <div style="color:#64748b;margin-top:.2rem;">
                        Réf : {{ $part->part_reference ?? '—' }} · Qté : {{ $part->quantity ?? 1 }}
                    </div>
                    @if($part->warranty_expiry)
                    <div style="color:#64748b;">Garantie : {{ $part->warranty_expiry->format('d/m/Y') }}</div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Suivi de l'immobilisation --}}
        <div class="card">
            <div class="card-head"><span class="card-title">Suivi de l'immobilisation</span></div>
            <div style="padding:1.25rem;display:flex;flex-direction:column;gap:.75rem;font-size:.83rem;">
                <div>
                    <div style="font-size:.7rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.04em;">Date d'entrée</div>
                    <div style="margin-top:.2rem;color:#0f172a;">{{ $repair->datetime_sent?->format('d/m/Y à H:i') ?? '—' }}</div>
                </div>
                @if($repair->availability_date_requested)
                <div>
                    <div style="font-size:.7rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.04em;">Disponibilité souhaitée</div>
                    <div style="margin-top:.2rem;color:#0f172a;">{{ $repair->availability_date_requested->format('d/m/Y') }}</div>
                </div>
                @endif
                <div>
                    <div style="font-size:.7rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.04em;">Date de sortie réelle</div>
                    @can('repairs.edit')
                    <form method="POST" action="{{ route('repairs.update', $repair) }}" style="margin-top:.3rem;display:flex;gap:.4rem;align-items:center;">
                        @csrf @method('PUT')
                        <input type="date" name="actual_exit_date" class="form-control"
                               style="font-size:.8rem;padding:.3rem .55rem;"
                               value="{{ $repair->actual_exit_date?->format('Y-m-d') }}">
                        <button type="submit" class="btn btn-primary" style="padding:.3rem .6rem;font-size:.75rem;white-space:nowrap;">OK</button>
                    </form>
                    @else
                    <div style="margin-top:.2rem;color:#0f172a;">{{ $repair->actual_exit_date?->format('d/m/Y') ?? '—' }}</div>
                    @endcan
                </div>
                <div style="border-top:1px solid #f1f5f9;padding-top:.75rem;">
                    <div style="font-size:.7rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.04em;">Immobilisation</div>
                    @php $imDays = $repair->immobilization_days; @endphp
                    <div style="margin-top:.3rem;display:flex;align-items:center;gap:.5rem;">
                        @if($imDays !== null)
                            <span style="font-size:1.35rem;font-weight:800;color:{{ $repair->is_overdue ? '#dc2626' : '#0f172a' }};">{{ $imDays }}</span>
                            <span style="color:#64748b;">jour(s)</span>
                            @if($repair->is_overdue)
                                <span style="font-size:.72rem;background:#fee2e2;color:#dc2626;padding:.15rem .5rem;border-radius:99px;font-weight:600;">Dépassé</span>
                            @endif
                        @else
                            <span style="color:#94a3b8;">—</span>
                        @endif
                    </div>
                </div>
                @if($repair->or_initial_reference)
                <div>
                    <div style="font-size:.7rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.04em;">Réf. OR initial</div>
                    <div style="margin-top:.2rem;color:#0f172a;">{{ $repair->or_initial_reference }}</div>
                </div>
                @endif
                @if($repair->vehicle_type_body)
                <div>
                    <div style="font-size:.7rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.04em;">Type carrosserie</div>
                    <div style="margin-top:.2rem;color:#0f172a;">{{ $repair->vehicle_type_body }}</div>
                </div>
                @endif
            </div>
        </div>

        {{-- Méta --}}
        <div class="card">
            <div class="card-head"><span class="card-title">Informations</span></div>
            <div style="padding:1.25rem;font-size:.8rem;color:#64748b;display:flex;flex-direction:column;gap:.4rem;">
                <div>Durée : <strong>{{ $repair->duration_days ?? '—' }} jour(s)</strong></div>
                @if($repair->incident)
                    <div>Sinistre : <a href="{{ route('incidents.show', $repair->incident) }}" style="color:#10b981;">#{{{ $repair->incident_id }}}</a></div>
                @endif
                <div>Créé le {{ $repair->created_at?->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>
</div>
{{-- ── Modal diagnostic garage ──────────────────────────────────────────── --}}
<div id="diag-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:.75rem;width:min(560px,95vw);max-height:90vh;overflow-y:auto;padding:1.5rem;position:relative;">
        <button onclick="closeDiagModal()" style="position:absolute;top:.75rem;right:.75rem;background:none;border:none;font-size:1.2rem;cursor:pointer;color:#64748b;">×</button>
        <h3 style="margin:0 0 1rem;font-size:1rem;color:#0f172a;">Diagnostic du garage</h3>
        <input type="hidden" id="diag-fc-id">
        <div style="display:flex;flex-direction:column;gap:.85rem;">
            <div>
                <label style="font-size:.8rem;font-weight:600;color:#374151;display:block;margin-bottom:.35rem;">Diagnostic garage</label>
                <textarea id="diag-diagnosis" class="form-control" rows="3" placeholder="Ce que le garage a diagnostiqué…"></textarea>
            </div>
            <div>
                <label style="font-size:.8rem;font-weight:600;color:#374151;display:block;margin-bottom:.35rem;">Travaux réalisés</label>
                <textarea id="diag-work" class="form-control" rows="3" placeholder="Travaux effectués pour résoudre ce code…"></textarea>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
                <div>
                    <label style="font-size:.8rem;font-weight:600;color:#374151;display:block;margin-bottom:.35rem;">Statut de résolution</label>
                    <select id="diag-status" class="form-control" style="font-size:.83rem;">
                        <option value="pending">En attente</option>
                        <option value="resolved">Résolu</option>
                        <option value="partial">Partiellement résolu</option>
                        <option value="deferred">Reporté</option>
                        <option value="not_covered">Non pris en charge</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:.8rem;font-weight:600;color:#374151;display:block;margin-bottom:.35rem;">Coût (FCFA)</label>
                    <input type="number" id="diag-cost" class="form-control" min="0" step="100" placeholder="0">
                </div>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:.6rem;">
                <button type="button" class="btn btn-ghost" onclick="closeDiagModal()">Annuler</button>
                <button type="button" class="btn btn-primary" onclick="submitDiag()">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
// ── Gestion CSRF ──────────────────────────────────────────────────────────
const CSRF = document.querySelector('meta[name=csrf-token]')?.content || '';

// ── Signatures ────────────────────────────────────────────────────────────
const sigPads = {};

document.querySelectorAll('canvas[id^="canvas-"]').forEach(canvas => {
    const key = canvas.id.replace('canvas-', '');
    sigPads[key] = new SignaturePad(canvas, { penColor: '#1e293b' });
});

function clearSig(key) {
    if (sigPads[key]) sigPads[key].clear();
}

function showSigCanvas(key) {
    const box = document.getElementById('sigbox-' + key);
    const img = box.querySelector('img');
    if (img) img.style.display = 'none';

    const canvas = document.createElement('canvas');
    canvas.id = 'canvas-' + key;
    canvas.width  = 260;
    canvas.height = 100;
    box.insertBefore(canvas, box.querySelector('button'));

    sigPads[key] = new SignaturePad(canvas, { penColor: '#1e293b' });

    const btns = document.createElement('div');
    btns.style.cssText = 'display:flex;gap:.5rem;';
    btns.innerHTML = `
        <button type="button" class="btn btn-primary" style="font-size:.75rem;padding:.35rem .8rem;"
                onclick="saveSig('${key}', '{{ route('repairs.signature', $repair) }}')">Enregistrer</button>
        <button type="button" class="btn btn-ghost" style="font-size:.75rem;padding:.35rem .7rem;"
                onclick="clearSig('${key}')">Effacer</button>`;
    box.appendChild(btns);
}

async function saveSig(key, url) {
    const pad = sigPads[key];
    if (!pad || pad.isEmpty()) {
        alert('Veuillez signer avant d\'enregistrer.');
        return;
    }
    const dataUrl = pad.toDataURL('image/png');
    const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ signature_type: key, signature_data: dataUrl }),
    });
    if (res.ok) {
        const data = await res.json();
        const box  = document.getElementById('sigbox-' + key);
        box.innerHTML = `
            <div class="sig-label">${box.querySelector('.sig-label')?.textContent || ''}</div>
            <img src="${data.url}" alt="Signature" style="max-height:100px;border-radius:.4rem;border:1px solid #e2e8f0;">
            <button type="button" class="btn btn-ghost" style="font-size:.75rem;padding:.3rem .7rem;"
                    onclick="showSigCanvas('${key}')">Modifier</button>`;
    } else {
        alert('Erreur lors de l\'enregistrement de la signature.');
    }
}

// ── Modal diagnostic ──────────────────────────────────────────────────────
const diagModal = document.getElementById('diag-modal');

function openDiagModal(id, diag, work, status, cost) {
    document.getElementById('diag-fc-id').value     = id;
    document.getElementById('diag-diagnosis').value = diag || '';
    document.getElementById('diag-work').value      = work || '';
    document.getElementById('diag-status').value    = status || 'pending';
    document.getElementById('diag-cost').value      = cost  || '';
    diagModal.style.display = 'flex';
}

function closeDiagModal() { diagModal.style.display = 'none'; }

diagModal.addEventListener('click', e => { if (e.target === diagModal) closeDiagModal(); });

async function submitDiag() {
    const fcId = document.getElementById('diag-fc-id').value;
    const url  = `/reparations/{{ $repair->id }}/fault-codes/${fcId}/diagnostic`;
    const body = {
        garage_diagnosis:  document.getElementById('diag-diagnosis').value,
        work_performed:    document.getElementById('diag-work').value,
        resolution_status: document.getElementById('diag-status').value,
        fault_cost:        document.getElementById('diag-cost').value || null,
    };
    const res = await fetch(url, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify(body),
    });
    if (res.ok) {
        closeDiagModal();
        location.reload();
    } else {
        alert('Erreur lors de l\'enregistrement.');
    }
}
</script>
@endpush
