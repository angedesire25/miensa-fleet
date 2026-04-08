@extends('layouts.dashboard')

@section('title', 'Sinistre #' . $incident->id)
@section('page-title', 'Sinistre #' . $incident->id)

@section('content')
<style>
.card{background:#fff;border-radius:.75rem;border:1px solid #e2e8f0;overflow:hidden;margin-bottom:1.25rem;}
.card-head{padding:.85rem 1.25rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;gap:.6rem;}
.card-title{font-size:.9rem;font-weight:700;color:#0f172a;}
.badge{display:inline-flex;align-items:center;gap:.25rem;padding:.25rem .65rem;border-radius:99px;font-size:.75rem;font-weight:600;}
.btn{padding:.45rem .9rem;border-radius:.45rem;font-size:.82rem;font-weight:600;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;transition:opacity .15s;}
.btn-primary{background:linear-gradient(135deg,#10b981,#059669);color:#fff;}
.btn-danger{background:#fee2e2;color:#991b1b;border:1px solid #fecaca;}
.btn-warning{background:#fef3c7;color:#92400e;border:1px solid #fde68a;}
.btn-ghost{background:#f8fafc;color:#374151;border:1.5px solid #e2e8f0;}
.btn-ghost:hover{background:#f1f5f9;}
.detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;padding:1.25rem;}
.detail-item label{font-size:.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;display:block;margin-bottom:.25rem;}
.detail-item span{font-size:.875rem;color:#0f172a;}
.form-control{padding:.5rem .75rem;border:1.5px solid #e2e8f0;border-radius:.45rem;font-size:.855rem;color:#0f172a;outline:none;background:#fff;width:100%;box-sizing:border-box;}
.form-control:focus{border-color:#10b981;}
table{width:100%;border-collapse:collapse;}
th{font-size:.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;padding:.6rem 1rem;border-bottom:1.5px solid #f1f5f9;text-align:left;}
td{padding:.7rem 1rem;border-bottom:1px solid #f8fafc;font-size:.855rem;color:#374151;vertical-align:middle;}
tr:hover td{background:#f8fafc;}
</style>

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
    'breakdown'       => 'Panne mécanique',
    'flat_tire'       => 'Crevaison',
    'electrical_fault'=> 'Panne électrique',
    'body_damage'     => 'Dommage carrosserie',
    'theft_attempt'   => 'Tentative de vol',
    'theft'           => 'Vol',
    'flood_damage'    => 'Dommage inondation',
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

{{-- En-tête --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem;">
    <div style="display:flex;align-items:center;gap:.75rem;">
        <span class="badge" style="background:{{ $sBg }};color:{{ $sFg }};">{{ $statusLabels[$incident->status] ?? $incident->status }}</span>
        <span class="badge" style="background:{{ $svBg }};color:{{ $svFg }};">{{ $severityLabels[$incident->severity] ?? $incident->severity }}</span>
        <span style="font-size:.8rem;color:#94a3b8;">{{ $typeLabels[$incident->type] ?? $incident->type }}</span>
    </div>
    <div style="display:flex;gap:.6rem;flex-wrap:wrap;">
        <a href="{{ route('incidents.index') }}" class="btn btn-ghost">← Retour</a>
        @can('incidents.edit')
            @if(! in_array($incident->status, ['closed','total_loss']))
                <a href="{{ route('incidents.edit', $incident) }}" class="btn btn-ghost">Modifier</a>
            @endif
        @endcan
    </div>
</div>

{{-- Alerte si grave --}}
@if(in_array($incident->severity, ['major','total_loss']))
<div style="background:#fef2f2;border:1px solid #fecaca;border-radius:.6rem;padding:.9rem 1.1rem;margin-bottom:1.25rem;display:flex;gap:.6rem;align-items:center;color:#991b1b;font-size:.875rem;">
    <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="1.8"/><path d="M12 9v4M12 17h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
    <span><strong>Sinistre {{ $severityLabels[$incident->severity] }}</strong> — une alerte a été envoyée automatiquement aux gestionnaires de flotte.</span>
</div>
@endif

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.25rem;">
    {{-- Colonne principale --}}
    <div>
        {{-- Détails --}}
        <div class="card">
            <div class="card-head">
                <span class="card-title">Détails du sinistre</span>
            </div>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>Véhicule</label>
                    @if($incident->vehicle)
                        <a href="{{ route('vehicles.show', $incident->vehicle) }}" style="color:#10b981;font-weight:600;text-decoration:none;">
                            {{ $incident->vehicle->plate }} — {{ $incident->vehicle->brand }} {{ $incident->vehicle->model }}
                        </a>
                    @else
                        <span style="color:#94a3b8;">—</span>
                    @endif
                </div>
                <div class="detail-item">
                    <label>Chauffeur</label>
                    <span>{{ $incident->driver?->full_name ?? ($incident->user?->name ?? '—') }}</span>
                </div>
                <div class="detail-item">
                    <label>Date et heure</label>
                    <span>{{ $incident->datetime_occurred?->format('d/m/Y à H:i') ?? '—' }}</span>
                </div>
                <div class="detail-item">
                    <label>Lieu</label>
                    <span>{{ $incident->location ?? '—' }}</span>
                </div>
                <div class="detail-item">
                    <label>Véhicule immobilisé</label>
                    <span>{{ $incident->vehicle_immobilized ? 'Oui' : 'Non' }}</span>
                </div>
                <div class="detail-item">
                    <label>Déclaré à l'assurance</label>
                    <span>{{ $incident->insurance_declared ? 'Oui' : 'Non' }}</span>
                </div>
                @if($incident->insurance_claim_number)
                <div class="detail-item">
                    <label>N° sinistre assurance</label>
                    <span>{{ $incident->insurance_claim_number }}</span>
                </div>
                @endif
                @if($incident->police_report_number)
                <div class="detail-item">
                    <label>N° procès-verbal</label>
                    <span>{{ $incident->police_report_number }}</span>
                </div>
                @endif
            </div>
            <div style="padding:0 1.25rem 1.25rem;">
                <label style="font-size:.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;display:block;margin-bottom:.5rem;">Description</label>
                <p style="font-size:.875rem;color:#374151;line-height:1.6;white-space:pre-wrap;margin:0;">{{ $incident->description }}</p>
            </div>
        </div>

        {{-- Galerie photos --}}
        @if($incident->photos->isNotEmpty())
        <div class="card">
            <div class="card-head"><span class="card-title">Photos ({{ $incident->photos->count() }})</span></div>
            <div style="padding:1.25rem;">
                <div style="display:flex;flex-wrap:wrap;gap:.75rem;">
                    @foreach($incident->photos->groupBy('context') as $context => $contextPhotos)
                    <div style="width:100%;margin-bottom:.5rem;">
                        <div style="font-size:.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.5rem;">
                            {{ $context === 'incident_before' ? 'État avant incident' : 'Dégâts constatés' }}
                        </div>
                        <div style="display:flex;flex-wrap:wrap;gap:.6rem;">
                            @foreach($contextPhotos as $photo)
                            <div style="position:relative;">
                                <a href="{{ asset('storage/' . $photo->file_path) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $photo->file_path) }}" alt=""
                                         style="width:120px;height:90px;object-fit:cover;border-radius:.5rem;border:1.5px solid #e2e8f0;display:block;transition:opacity .15s;"
                                         onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                                </a>
                                @can('incidents.edit')
                                <form method="POST" action="{{ route('incidents.delete-photo', $incident) }}"
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
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Réparations --}}
        <div class="card">
            <div class="card-head">
                <span class="card-title">Réparations liées</span>
                @can('incidents.edit')
                    @if($incident->status === 'open')
                        <button type="button" class="btn btn-primary" onclick="document.getElementById('modal-garage').style.display='flex'">
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke="currentColor" stroke-width="1.8"/></svg>
                            Envoyer au garage
                        </button>
                    @endif
                @endcan
            </div>
            @if($incident->repairs->isEmpty())
                <div style="padding:2rem;text-align:center;color:#94a3b8;font-size:.855rem;">Aucune réparation pour ce sinistre.</div>
            @else
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Garage</th>
                            <th>Type</th>
                            <th>Envoyé le</th>
                            <th>Statut</th>
                            <th>Montant</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($incident->repairs as $repair)
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
                                'diagnosing'         => 'Diagnostic',
                                'repairing'          => 'En réparation',
                                'waiting_parts'      => 'Attente pièces',
                                'completed'          => 'Terminé',
                                'returned'           => 'Retourné',
                                'returned_with_issue'=> 'Retour avec problème',
                            ];
                            [$rBg,$rFg] = $rStatusColors[$repair->status] ?? ['#f8fafc','#64748b'];
                        @endphp
                        <tr>
                            <td style="font-weight:600;color:#64748b;">#{{ $repair->id }}</td>
                            <td>{{ $repair->garage?->name ?? '—' }}</td>
                            <td style="font-size:.8rem;">{{ ucfirst(str_replace('_',' ',$repair->repair_type)) }}</td>
                            <td style="font-size:.8rem;white-space:nowrap;">{{ $repair->datetime_sent?->format('d/m/Y') ?? '—' }}</td>
                            <td><span class="badge" style="background:{{ $rBg }};color:{{ $rFg }};">{{ $rStatusLabels[$repair->status] ?? $repair->status }}</span></td>
                            <td style="font-size:.8rem;">{{ $repair->invoice_amount ? number_format($repair->invoice_amount, 0, ',', ' ') . ' FCFA' : '—' }}</td>
                            <td>
                                <a href="{{ route('repairs.show', $repair) }}" class="btn btn-ghost" style="padding:.3rem .65rem;font-size:.78rem;">Voir</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    {{-- Colonne latérale --}}
    <div>
        {{-- Coûts --}}
        <div class="card">
            <div class="card-head"><span class="card-title">Coûts</span></div>
            <div style="padding:1.25rem;display:flex;flex-direction:column;gap:.85rem;">
                <div>
                    <div style="font-size:.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;">Estimation</div>
                    <div style="font-size:1.1rem;font-weight:700;color:#0f172a;margin-top:.2rem;">
                        {{ $incident->estimated_repair_cost ? number_format($incident->estimated_repair_cost, 0, ',', ' ') . ' FCFA' : '—' }}
                    </div>
                </div>
                <div>
                    <div style="font-size:.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;">Coût réel</div>
                    <div style="font-size:1.1rem;font-weight:700;color:#0f172a;margin-top:.2rem;">
                        {{ $incident->actual_repair_cost ? number_format($incident->actual_repair_cost, 0, ',', ' ') . ' FCFA' : '—' }}
                    </div>
                </div>
                @if($incident->insurance_amount_claimed || $incident->insurance_amount_received)
                <div style="border-top:1px solid #f1f5f9;padding-top:.85rem;">
                    <div style="font-size:.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;">Remboursement assurance</div>
                    <div style="font-size:.875rem;color:#0f172a;margin-top:.2rem;">
                        Réclamé : {{ $incident->insurance_amount_claimed ? number_format($incident->insurance_amount_claimed, 0, ',', ' ') . ' FCFA' : '—' }}<br>
                        Reçu : {{ $incident->insurance_amount_received ? number_format($incident->insurance_amount_received, 0, ',', ' ') . ' FCFA' : '—' }}
                    </div>
                </div>
                @endif
                <div>
                    <div style="font-size:.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;">Durée</div>
                    <div style="font-size:.875rem;color:#0f172a;margin-top:.2rem;">{{ $incident->duration_days ?? '—' }} jour(s)</div>
                </div>
            </div>
        </div>

        {{-- Tiers impliqué --}}
        @if($incident->third_party_involved)
        <div class="card">
            <div class="card-head"><span class="card-title">Tiers impliqué</span></div>
            <div style="padding:1.25rem;font-size:.855rem;color:#374151;display:flex;flex-direction:column;gap:.6rem;">
                @if($incident->third_party_name)
                    <div><strong>Nom :</strong> {{ $incident->third_party_name }}</div>
                @endif
                @if($incident->third_party_plate)
                    <div><strong>Plaque :</strong> {{ $incident->third_party_plate }}</div>
                @endif
                @if($incident->third_party_insurance)
                    <div><strong>Assurance :</strong> {{ $incident->third_party_insurance }}</div>
                @endif
                @if($incident->police_report_number)
                    <div><strong>N° PV :</strong> {{ $incident->police_report_number }}</div>
                @endif
            </div>
        </div>
        @endif

        {{-- Actions --}}
        @can('incidents.edit')
        <div class="card">
            <div class="card-head"><span class="card-title">Actions</span></div>
            <div style="padding:1.25rem;display:flex;flex-direction:column;gap:.6rem;">
                @if($incident->status === 'open')
                    <button type="button" class="btn btn-primary" style="width:100%;justify-content:center;"
                            onclick="document.getElementById('modal-garage').style.display='flex'">
                        Envoyer au garage
                    </button>
                @endif
                @if(in_array($incident->status, ['open','repaired']))
                    <form method="POST" action="{{ route('incidents.close', $incident) }}">
                        @csrf
                        <button type="submit" class="btn btn-ghost" style="width:100%;justify-content:center;"
                                data-confirm="Clôturer ce sinistre ?">
                            Clôturer le sinistre
                        </button>
                    </form>
                @endif
                @if(! in_array($incident->status, ['closed','total_loss']))
                    <a href="{{ route('incidents.edit', $incident) }}" class="btn btn-ghost" style="width:100%;justify-content:center;">Modifier</a>
                @endif
            </div>
        </div>
        @endcan

        {{-- Méta --}}
        <div class="card">
            <div class="card-head"><span class="card-title">Informations</span></div>
            <div style="padding:1.25rem;font-size:.8rem;color:#64748b;display:flex;flex-direction:column;gap:.4rem;">
                <div>Déclaré par <strong>{{ $incident->createdBy?->name ?? '—' }}</strong></div>
                <div>Le {{ $incident->created_at?->format('d/m/Y à H:i') }}</div>
                <div>Dernière modif. {{ $incident->updated_at?->diffForHumans() }}</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal : Envoyer au garage ─────────────────────────────────────────── --}}
@can('incidents.edit')
@if($incident->status === 'open')
<div id="modal-garage" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:.75rem;width:100%;max-width:520px;box-shadow:0 20px 60px rgba(0,0,0,.3);overflow:hidden;">
        <div style="padding:1.1rem 1.25rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
            <span style="font-weight:700;color:#0f172a;">Envoyer au garage</span>
            <button onclick="document.getElementById('modal-garage').style.display='none'" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:1.25rem;">×</button>
        </div>
        <form method="POST" action="{{ route('incidents.send-to-garage', $incident) }}" style="padding:1.25rem;display:flex;flex-direction:column;gap:.85rem;">
            @csrf
            <div>
                <label style="font-size:.8rem;font-weight:600;color:#374151;display:block;margin-bottom:.35rem;">Garage <span style="color:#ef4444">*</span></label>
                <select name="garage_id" class="form-control" required>
                    <option value="">Sélectionner un garage agréé…</option>
                    @foreach(\App\Models\Garage::approved()->orderBy('name')->get() as $garage)
                        <option value="{{ $garage->id }}">{{ $garage->name }} — {{ $garage->city }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:.8rem;font-weight:600;color:#374151;display:block;margin-bottom:.35rem;">Type de réparation <span style="color:#ef4444">*</span></label>
                <select name="repair_type" class="form-control" required>
                    <option value="">Sélectionner…</option>
                    <option value="body_repair">Carrosserie</option>
                    <option value="mechanical">Mécanique</option>
                    <option value="electrical">Électrique</option>
                    <option value="tire">Pneus</option>
                    <option value="painting">Peinture</option>
                    <option value="glass">Vitrage</option>
                    <option value="full_service">Révision complète</option>
                    <option value="other">Autre</option>
                </select>
            </div>
            <div>
                <label style="font-size:.8rem;font-weight:600;color:#374151;display:block;margin-bottom:.35rem;">Date d'envoi <span style="color:#ef4444">*</span></label>
                <input type="datetime-local" name="datetime_sent" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
            </div>
            <div>
                <label style="font-size:.8rem;font-weight:600;color:#374151;display:block;margin-bottom:.35rem;">Kilométrage au départ</label>
                <input type="number" name="km_at_departure" class="form-control" min="0" placeholder="km">
            </div>
            <div>
                <label style="font-size:.8rem;font-weight:600;color:#374151;display:block;margin-bottom:.35rem;">État au départ</label>
                <textarea name="condition_at_departure" class="form-control" rows="2" placeholder="Description de l'état du véhicule au moment de l'envoi…"></textarea>
            </div>
            <div style="display:flex;gap:.6rem;justify-content:flex-end;border-top:1px solid #f1f5f9;padding-top:.85rem;">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('modal-garage').style.display='none'">Annuler</button>
                <button type="submit" class="btn btn-primary">Confirmer l'envoi</button>
            </div>
        </form>
    </div>
</div>
@endif
@endcan
@endsection
