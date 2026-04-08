@extends('layouts.dashboard')

@section('title', 'Alerte — ' . $alert->title)

@section('content')
@php
    $severityColor = match($alert->severity) {
        'critical' => '#ef4444',
        'warning'  => '#f59e0b',
        default    => '#3b82f6',
    };
    $severityLabel = match($alert->severity) {
        'critical' => 'Critique',
        'warning'  => 'Avertissement',
        default    => 'Information',
    };
    $typeLabels = [
        'document_expiring'   => 'Document expirant',
        'maintenance_due'     => 'Maintenance requise',
        'vehicle_idle'        => 'Véhicule immobilisé',
        'infraction_unpaid'   => 'Amende impayée',
        'assignment_conflict' => 'Conflit affectation',
        'inspection_overdue'  => 'Inspection en retard',
        'fuel_anomaly'        => 'Anomalie carburant',
    ];
@endphp

<div style="padding:1.5rem;">

    {{-- Breadcrumb --}}
    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:1.25rem;font-size:.82rem;color:#64748b;">
        <a href="{{ route('alerts.index') }}" style="color:#94a3b8;text-decoration:none;">Alertes</a>
        <span>/</span>
        <span style="color:#f1f5f9;">{{ $alert->title }}</span>
    </div>

    <div style="display:grid;grid-template-columns:1fr 320px;gap:1.5rem;align-items:start;">

        {{-- Colonne principale --}}
        <div>

            {{-- Carte alerte --}}
            <div style="background:#1e293b;border:1px solid #334155;border-left:4px solid {{ $severityColor }};border-radius:.65rem;padding:1.5rem;margin-bottom:1.25rem;">
                <div style="display:flex;align-items:flex-start;gap:1rem;">
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:44px;height:44px;border-radius:50%;background:{{ $severityColor }}20;flex-shrink:0;">
                        @if($alert->severity === 'critical')
                        <svg style="width:22px;height:22px;stroke:{{ $severityColor }};" fill="none" viewBox="0 0 24 24"><path d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="1.8"/></svg>
                        @else
                        <svg style="width:22px;height:22px;stroke:{{ $severityColor }};" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/><path d="M12 8v4m0 4h.01" stroke="currentColor" stroke-width="1.8"/></svg>
                        @endif
                    </span>
                    <div style="flex:1;">
                        <div style="display:flex;align-items:center;gap:.65rem;flex-wrap:wrap;margin-bottom:.5rem;">
                            <h1 style="font-size:1.1rem;font-weight:700;color:#f1f5f9;margin:0;">{{ $alert->title }}</h1>
                            <span style="padding:.2rem .6rem;border-radius:.35rem;font-size:.75rem;font-weight:700;background:{{ $severityColor }}20;color:{{ $severityColor }};">
                                {{ $severityLabel }}
                            </span>
                            @if($alert->status === 'new')
                            <span style="padding:.2rem .55rem;background:#3b82f620;color:#3b82f6;border-radius:.35rem;font-size:.72rem;font-weight:700;">NEW</span>
                            @elseif($alert->status === 'seen')
                            <span style="padding:.2rem .55rem;background:#94a3b820;color:#94a3b8;border-radius:.35rem;font-size:.72rem;">Vue</span>
                            @else
                            <span style="padding:.2rem .55rem;background:#10b98120;color:#10b981;border-radius:.35rem;font-size:.72rem;">Traitée</span>
                            @endif
                        </div>
                        <p style="color:#cbd5e1;font-size:.9rem;margin:0;line-height:1.55;">{{ $alert->message }}</p>
                        <div style="margin-top:.75rem;font-size:.78rem;color:#64748b;">
                            <span>Type : <strong style="color:#94a3b8;">{{ $typeLabels[$alert->type] ?? $alert->type }}</strong></span>
                            <span style="margin-left:1.5rem;">Créée {{ $alert->created_at->diffForHumans() }}</span>
                            @if($alert->due_date)
                            <span style="margin-left:1.5rem;">
                                Échéance :
                                <strong style="color:{{ $alert->due_date->isPast() ? '#ef4444' : '#f59e0b' }};">
                                    {{ $alert->due_date->format('d/m/Y') }}
                                </strong>
                            </span>
                            @endif
                            @if($alert->days_remaining !== null)
                            <span style="margin-left:1.5rem;">J–{{ $alert->days_remaining }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Entités liées --}}
            @if($alert->vehicle || $alert->driver || $alert->user || $alert->infraction || $alert->vehicleRequest)
            <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;padding:1.25rem;margin-bottom:1.25rem;">
                <h3 style="font-size:.85rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin:0 0 1rem;">Entités concernées</h3>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:.75rem;">
                    @if($alert->vehicle)
                    <div style="background:#0f172a;border-radius:.45rem;padding:.75rem;">
                        <div style="font-size:.72rem;color:#64748b;margin-bottom:.3rem;">Véhicule</div>
                        <a href="{{ route('vehicles.show', $alert->vehicle) }}"
                           style="font-size:.88rem;font-weight:600;color:#3b82f6;text-decoration:none;">
                            {{ $alert->vehicle->brand }} {{ $alert->vehicle->model }}
                        </a>
                        <div style="font-size:.78rem;color:#94a3b8;">{{ $alert->vehicle->plate }}</div>
                    </div>
                    @endif
                    @if($alert->driver)
                    <div style="background:#0f172a;border-radius:.45rem;padding:.75rem;">
                        <div style="font-size:.72rem;color:#64748b;margin-bottom:.3rem;">Conducteur</div>
                        <a href="{{ route('drivers.show', $alert->driver) }}"
                           style="font-size:.88rem;font-weight:600;color:#3b82f6;text-decoration:none;">
                            {{ $alert->driver->full_name }}
                        </a>
                        <div style="font-size:.78rem;color:#94a3b8;">{{ $alert->driver->matricule }}</div>
                    </div>
                    @endif
                    @if($alert->infraction)
                    <div style="background:#0f172a;border-radius:.45rem;padding:.75rem;">
                        <div style="font-size:.72rem;color:#64748b;margin-bottom:.3rem;">Infraction</div>
                        <a href="{{ route('infractions.show', $alert->infraction) }}"
                           style="font-size:.88rem;font-weight:600;color:#3b82f6;text-decoration:none;">
                            #{{ $alert->infraction->id }} — {{ $alert->infraction->type }}
                        </a>
                        <div style="font-size:.78rem;color:#94a3b8;">{{ number_format($alert->infraction->fine_amount, 0, ',', ' ') }} FCFA</div>
                    </div>
                    @endif
                    @if($alert->vehicleRequest)
                    <div style="background:#0f172a;border-radius:.45rem;padding:.75rem;">
                        <div style="font-size:.72rem;color:#64748b;margin-bottom:.3rem;">Demande</div>
                        <span style="font-size:.88rem;font-weight:600;color:#f1f5f9;">#{{ $alert->vehicleRequest->id }}</span>
                        <div style="font-size:.78rem;color:#94a3b8;">{{ $alert->vehicleRequest->purpose ?? '—' }}</div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Traitement --}}
            @if($alert->status === 'processed')
            <div style="background:#10b98110;border:1px solid #10b98130;border-radius:.65rem;padding:1.25rem;">
                <h3 style="font-size:.85rem;font-weight:600;color:#10b981;margin:0 0 .5rem;">Alerte traitée</h3>
                <div style="font-size:.82rem;color:#94a3b8;">
                    <span>Traité par <strong style="color:#f1f5f9;">{{ $alert->processedBy?->name ?? '—' }}</strong></span>
                    <span style="margin-left:1rem;">le {{ $alert->processed_at?->format('d/m/Y à H:i') }}</span>
                </div>
                @if($alert->process_notes)
                <p style="margin:.6rem 0 0;color:#cbd5e1;font-size:.85rem;line-height:1.5;">{{ $alert->process_notes }}</p>
                @endif
            </div>
            @endif

        </div>

        {{-- Colonne latérale --}}
        <div>

            {{-- Action traiter --}}
            @can('alerts.manage')
            @if($alert->status !== 'processed')
            <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;padding:1.25rem;margin-bottom:1rem;">
                <h3 style="font-size:.85rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin:0 0 .9rem;">Marquer comme traité</h3>
                <form method="POST" action="{{ route('alerts.process', $alert) }}">
                    @csrf
                    <textarea name="process_notes" rows="3" placeholder="Notes (optionnel)"
                              style="width:100%;background:#0f172a;border:1px solid #475569;border-radius:.4rem;color:#f1f5f9;padding:.6rem .75rem;font-size:.85rem;resize:vertical;box-sizing:border-box;"></textarea>
                    <button type="submit"
                            style="margin-top:.65rem;width:100%;padding:.55rem;background:#10b981;color:#fff;border:none;border-radius:.45rem;font-size:.85rem;font-weight:600;cursor:pointer;">
                        Marquer comme traité
                    </button>
                </form>
            </div>
            @endif
            @endcan

            {{-- Détails techniques --}}
            <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;padding:1.25rem;">
                <h3 style="font-size:.85rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin:0 0 .9rem;">Informations</h3>
                <dl style="margin:0;display:grid;gap:.55rem;">
                    <div>
                        <dt style="font-size:.72rem;color:#64748b;">Identifiant</dt>
                        <dd style="font-size:.85rem;color:#f1f5f9;margin:0;">#{{ $alert->id }}</dd>
                    </div>
                    <div>
                        <dt style="font-size:.72rem;color:#64748b;">Statut</dt>
                        <dd style="font-size:.85rem;color:#f1f5f9;margin:0;text-transform:capitalize;">{{ $alert->status }}</dd>
                    </div>
                    <div>
                        <dt style="font-size:.72rem;color:#64748b;">Canaux</dt>
                        <dd style="font-size:.85rem;color:#f1f5f9;margin:0;">{{ $alert->channels ? implode(', ', $alert->channels) : '—' }}</dd>
                    </div>
                    <div>
                        <dt style="font-size:.72rem;color:#64748b;">Envoyée</dt>
                        <dd style="font-size:.85rem;color:#f1f5f9;margin:0;">
                            @if($alert->sent_at) {{ $alert->sent_at->format('d/m/Y H:i') }}
                            @elseif($alert->send_failed) <span style="color:#ef4444;">Échec d'envoi</span>
                            @else <span style="color:#94a3b8;">En attente</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt style="font-size:.72rem;color:#64748b;">Créée</dt>
                        <dd style="font-size:.85rem;color:#f1f5f9;margin:0;">{{ $alert->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </div>

            <div style="margin-top:1rem;">
                <a href="{{ route('alerts.index') }}"
                   style="display:block;text-align:center;padding:.55rem;background:#334155;color:#94a3b8;border-radius:.45rem;text-decoration:none;font-size:.85rem;">
                    ← Retour aux alertes
                </a>
            </div>

        </div>
    </div>
</div>
@endsection
