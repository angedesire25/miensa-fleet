@extends('layouts.dashboard')

@section('title', 'Rapport chauffeur')

@section('content')
@php
    $typeLabels = [
        'speeding'          => 'Excès de vitesse',
        'red_light'         => 'Feu rouge',
        'parking'           => 'Stationnement',
        'phone_use'         => 'Téléphone',
        'seatbelt'          => 'Ceinture',
        'alcohol'           => 'Alcool',
        'dangerous_driving' => 'Conduite dang.',
        'overload'          => 'Surcharge',
        'invalid_documents' => 'Docs invalides',
        'other'             => 'Autre',
    ];
@endphp
<div style="padding:1.5rem;">

    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:1.25rem;font-size:.82rem;color:#64748b;">
        <a href="{{ route('reports.index') }}" style="color:#94a3b8;text-decoration:none;">Rapports</a>
        <span>/</span>
        <span style="color:#f1f5f9;">Rapport chauffeur</span>
    </div>

    {{-- Sélecteur --}}
    @include('reports._period_filter', [
        'action'  => route('reports.driver'),
        'from'    => $from,
        'to'      => $to,
        'drivers' => $drivers,
        'selectedDriverId' => $report !== null ? $report['driver']->id : request('driver_id'),
    ])

    @if($report === null)
    <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;padding:2rem;text-align:center;">
        <p style="color:#94a3b8;font-size:.9rem;margin:0;">Sélectionnez un chauffeur et une période, puis cliquez sur <strong>Générer</strong>.</p>
    </div>
    @else
    @php $driver = $report['driver']; @endphp

    {{-- En-tête chauffeur --}}
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1.25rem;">
        <div>
            <h1 style="font-size:1.2rem;font-weight:700;color:#f1f5f9;margin:0;">{{ $driver->full_name }}</h1>
            <p style="color:#94a3b8;font-size:.82rem;margin:.2rem 0 0;">
                {{ $driver->matricule }} · Période : {{ $from->format('d/m/Y') }} → {{ $to->format('d/m/Y') }}
            </p>
        </div>
        <a href="{{ route('drivers.show', $driver) }}"
           style="padding:.4rem .85rem;background:#334155;color:#94a3b8;border-radius:.4rem;text-decoration:none;font-size:.82rem;">
            Fiche chauffeur
        </a>
    </div>

    {{-- KPIs --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:1.25rem;">
        <div style="background:#1e293b;border:1px solid rgba(16,185,129,.3);border-radius:.65rem;padding:1rem 1.25rem;">
            <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;margin-bottom:.35rem;">Affectations</div>
            <div style="font-size:1.5rem;font-weight:700;color:#10b981;">{{ $report['total_assignments'] }}</div>
        </div>
        <div style="background:#1e293b;border:1px solid rgba(59,130,246,.3);border-radius:.65rem;padding:1rem 1.25rem;">
            <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;margin-bottom:.35rem;">Km parcourus</div>
            <div style="font-size:1.5rem;font-weight:700;color:#3b82f6;">{{ number_format($report['total_km'], 0, ',', ' ') }}</div>
        </div>
        <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;padding:1rem 1.25rem;">
            <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;margin-bottom:.35rem;">Trajets</div>
            <div style="font-size:1.5rem;font-weight:700;color:#f1f5f9;">{{ $report['total_trips'] }}</div>
        </div>
        <div style="background:#1e293b;border:1px solid rgba(239,68,68,.3);border-radius:.65rem;padding:1rem 1.25rem;">
            <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;margin-bottom:.35rem;">Infractions</div>
            <div style="font-size:1.5rem;font-weight:700;color:#ef4444;">{{ $report['infractions']->count() }}</div>
        </div>
    </div>

    {{-- Documents --}}
    @if($report['documents']->isNotEmpty())
    <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;overflow:hidden;margin-bottom:1.25rem;">
        <div style="padding:.75rem 1.25rem;border-bottom:1px solid #334155;">
            <h3 style="font-size:.88rem;font-weight:600;color:#f1f5f9;margin:0;">Documents</h3>
        </div>
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid #334155;">
                    <th style="text-align:left;padding:.55rem 1rem;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;">Type</th>
                    <th style="text-align:left;padding:.55rem 1rem;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;">Numéro</th>
                    <th style="text-align:left;padding:.55rem 1rem;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;">Expiration</th>
                    <th style="text-align:center;padding:.55rem 1rem;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;">État</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['documents'] as $doc)
                @php
                    $expired  = $doc->expiry_date && $doc->expiry_date->isPast();
                    $expiring = $doc->expiry_date && !$expired && $doc->expiry_date->diffInDays(now()) <= 30;
                @endphp
                <tr style="border-bottom:1px solid #334155;{{ $loop->last ? 'border-bottom:none;' : '' }}">
                    <td style="padding:.6rem 1rem;font-size:.85rem;color:#f1f5f9;">{{ $doc->type }}</td>
                    <td style="padding:.6rem 1rem;font-size:.82rem;color:#94a3b8;">{{ $doc->document_number ?: '—' }}</td>
                    <td style="padding:.6rem 1rem;font-size:.82rem;color:{{ $expired ? '#ef4444' : ($expiring ? '#f59e0b' : '#94a3b8') }};">
                        {{ $doc->expiry_date ? $doc->expiry_date->format('d/m/Y') : '—' }}
                    </td>
                    <td style="padding:.6rem 1rem;text-align:center;">
                        <span style="padding:.15rem .5rem;border-radius:.3rem;font-size:.72rem;font-weight:600;
                            {{ $expired ? 'background:#ef444420;color:#ef4444;' : ($expiring ? 'background:#f59e0b20;color:#f59e0b;' : 'background:#10b98120;color:#10b981;') }}">
                            {{ $expired ? 'Expiré' : ($expiring ? 'Expire bientôt' : 'Valide') }}
                        </span>
                        @if($doc->medical_result && $doc->medical_result !== 'apt')
                        <span style="margin-left:.35rem;padding:.15rem .5rem;background:#ef444420;color:#ef4444;border-radius:.3rem;font-size:.7rem;">{{ $doc->medical_result }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Infractions --}}
    @if($report['infractions']->isNotEmpty())
    <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;overflow:hidden;margin-bottom:1.25rem;">
        <div style="padding:.75rem 1.25rem;border-bottom:1px solid #334155;">
            <h3 style="font-size:.88rem;font-weight:600;color:#f1f5f9;margin:0;">Infractions sur la période</h3>
        </div>
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid #334155;">
                    <th style="text-align:left;padding:.55rem 1rem;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;">Date</th>
                    <th style="text-align:left;padding:.55rem 1rem;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;">Type</th>
                    <th style="text-align:left;padding:.55rem 1rem;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;">Véhicule</th>
                    <th style="text-align:right;padding:.55rem 1rem;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;">Amende</th>
                    <th style="text-align:center;padding:.55rem 1rem;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;">Imputation</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['infractions'] as $inf)
                <tr style="border-bottom:1px solid #334155;{{ $loop->last ? 'border-bottom:none;' : '' }}">
                    <td style="padding:.6rem 1rem;font-size:.82rem;color:#94a3b8;">{{ $inf->datetime_occurred->format('d/m/Y') }}</td>
                    <td style="padding:.6rem 1rem;font-size:.85rem;color:#f1f5f9;">{{ $typeLabels[$inf->type] ?? $inf->type }}</td>
                    <td style="padding:.6rem 1rem;font-size:.82rem;color:#94a3b8;">{{ $inf->vehicle?->plate ?? '—' }}</td>
                    <td style="padding:.6rem 1rem;text-align:right;font-size:.82rem;color:#f1f5f9;">
                        {{ $inf->fine_amount ? number_format($inf->fine_amount, 0, ',', ' ').' FCFA' : '—' }}
                    </td>
                    <td style="padding:.6rem 1rem;text-align:center;font-size:.78rem;color:#94a3b8;">
                        {{ $inf->imputation === 'driver' ? 'Conducteur' : ($inf->imputation === 'company' ? 'Société' : '—') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Affectations détaillées --}}
    @if($report['assignments_detail']->isNotEmpty())
    <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;overflow:hidden;">
        <div style="padding:.75rem 1.25rem;border-bottom:1px solid #334155;">
            <h3 style="font-size:.88rem;font-weight:600;color:#f1f5f9;margin:0;">Affectations détaillées</h3>
        </div>
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid #334155;">
                    <th style="text-align:left;padding:.55rem 1rem;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;">Départ</th>
                    <th style="text-align:left;padding:.55rem 1rem;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;">Véhicule</th>
                    <th style="text-align:left;padding:.55rem 1rem;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;">Mission</th>
                    <th style="text-align:right;padding:.55rem 1rem;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;">Km</th>
                    <th style="text-align:center;padding:.55rem 1rem;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;">Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['assignments_detail'] as $a)
                <tr style="border-bottom:1px solid #334155;{{ $loop->last ? 'border-bottom:none;' : '' }}">
                    <td style="padding:.6rem 1rem;font-size:.82rem;color:#94a3b8;">{{ $a->datetime_start->format('d/m/Y') }}</td>
                    <td style="padding:.6rem 1rem;font-size:.82rem;color:#f1f5f9;">{{ $a->vehicle?->plate ?? '—' }}</td>
                    <td style="padding:.6rem 1rem;font-size:.82rem;color:#94a3b8;max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $a->mission ?: ($a->destination ?: '—') }}
                    </td>
                    <td style="padding:.6rem 1rem;text-align:right;font-size:.82rem;color:#f1f5f9;">
                        {{ $a->km_total ? number_format($a->km_total, 0, ',', ' ') : '—' }}
                    </td>
                    <td style="padding:.6rem 1rem;text-align:center;">
                        @php
                            $c = match($a->status) {
                                'completed'   => ['bg'=>'#10b98120','color'=>'#10b981','lbl'=>'Terminée'],
                                'in_progress' => ['bg'=>'#3b82f620','color'=>'#3b82f6','lbl'=>'En cours'],
                                'cancelled'   => ['bg'=>'#ef444420','color'=>'#ef4444','lbl'=>'Annulée'],
                                default       => ['bg'=>'#33415520','color'=>'#94a3b8','lbl'=>ucfirst($a->status)],
                            };
                        @endphp
                        <span style="padding:.15rem .5rem;background:{{ $c['bg'] }};color:{{ $c['color'] }};border-radius:.3rem;font-size:.72rem;font-weight:600;">
                            {{ $c['lbl'] }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @endif {{-- report --}}
</div>
@endsection
