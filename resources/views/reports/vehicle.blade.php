@extends('layouts.dashboard')

@section('title', 'Rapport véhicule')

@section('content')
<div style="padding:1.5rem;">

    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:1.25rem;font-size:.82rem;color:#64748b;">
        <a href="{{ route('reports.index') }}" style="color:#94a3b8;text-decoration:none;">Rapports</a>
        <span>/</span>
        <span style="color:#f1f5f9;">Rapport véhicule</span>
    </div>

    {{-- Sélecteur --}}
    @include('reports._period_filter', [
        'action'   => route('reports.vehicle'),
        'from'     => $from,
        'to'       => $to,
        'vehicles' => $vehicles,
        'selectedVehicleId' => $report !== null ? $report['vehicle']->id : request('vehicle_id'),
    ])

    @if($report === null)
    <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;padding:2rem;text-align:center;">
        <p style="color:#94a3b8;font-size:.9rem;margin:0;">Sélectionnez un véhicule et une période, puis cliquez sur <strong>Générer</strong>.</p>
    </div>
    @else
    @php
        $vehicle = $report['vehicle'];
        $period  = $report['period'];
    @endphp

    {{-- En-tête véhicule --}}
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1.25rem;">
        <div>
            <h1 style="font-size:1.2rem;font-weight:700;color:#f1f5f9;margin:0;">
                {{ $vehicle->brand }} {{ $vehicle->model }} — {{ $vehicle->plate }}
            </h1>
            <p style="color:#94a3b8;font-size:.82rem;margin:.2rem 0 0;">
                Période : {{ $from->format('d/m/Y') }} → {{ $to->format('d/m/Y') }}
            </p>
        </div>
        <a href="{{ route('vehicles.show', $vehicle) }}"
           style="padding:.4rem .85rem;background:#334155;color:#94a3b8;border-radius:.4rem;text-decoration:none;font-size:.82rem;">
            Fiche véhicule
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
            <div style="font-size:1.5rem;font-weight:700;color:#ef4444;">{{ $report['total_infractions']['count'] }}</div>
            <div style="font-size:.72rem;color:#94a3b8;">{{ number_format($report['total_infractions']['total_amount'], 0, ',', ' ') }} FCFA</div>
        </div>
        <div style="background:#1e293b;border:1px solid rgba(245,158,11,.3);border-radius:.65rem;padding:1rem 1.25rem;">
            <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;margin-bottom:.35rem;">Contrôles</div>
            <div style="font-size:1.5rem;font-weight:700;color:#f59e0b;">{{ $report['inspections']->count() }}</div>
        </div>
        <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;padding:1rem 1.25rem;">
            <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;margin-bottom:.35rem;">Demandes</div>
            <div style="font-size:1.5rem;font-weight:700;color:#f1f5f9;">{{ $report['requests']->count() }}</div>
        </div>
    </div>

    {{-- Documents --}}
    @if($report['documents']->isNotEmpty())
    <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;overflow:hidden;margin-bottom:1.25rem;">
        <div style="padding:.75rem 1.25rem;border-bottom:1px solid #334155;">
            <h3 style="font-size:.88rem;font-weight:600;color:#f1f5f9;margin:0;">Documents administratifs</h3>
        </div>
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid #334155;">
                    <th style="text-align:left;padding:.55rem 1rem;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;">Type</th>
                    <th style="text-align:left;padding:.55rem 1rem;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;">Numéro</th>
                    <th style="text-align:left;padding:.55rem 1rem;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;">Expiration</th>
                    <th style="text-align:center;padding:.55rem 1rem;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;">Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['documents'] as $doc)
                @php
                    $expired = $doc->expiry_date && $doc->expiry_date->isPast();
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
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Contrôles --}}
    @if($report['inspections']->isNotEmpty())
    <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;overflow:hidden;margin-bottom:1.25rem;">
        <div style="padding:.75rem 1.25rem;border-bottom:1px solid #334155;">
            <h3 style="font-size:.88rem;font-weight:600;color:#f1f5f9;margin:0;">Contrôles sur la période</h3>
        </div>
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid #334155;">
                    <th style="text-align:left;padding:.55rem 1rem;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;">Date</th>
                    <th style="text-align:left;padding:.55rem 1rem;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;">Type</th>
                    <th style="text-align:right;padding:.55rem 1rem;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;">Km</th>
                    <th style="text-align:center;padding:.55rem 1rem;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;">Anomalie</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['inspections'] as $insp)
                <tr style="border-bottom:1px solid #334155;{{ $loop->last ? 'border-bottom:none;' : '' }}">
                    <td style="padding:.6rem 1rem;font-size:.82rem;color:#94a3b8;">{{ $insp->inspected_at->format('d/m/Y') }}</td>
                    <td style="padding:.6rem 1rem;font-size:.85rem;color:#f1f5f9;">{{ $insp->inspection_type }}</td>
                    <td style="padding:.6rem 1rem;text-align:right;font-size:.82rem;color:#94a3b8;">{{ $insp->km ? number_format($insp->km, 0, ',', ' ') : '—' }}</td>
                    <td style="padding:.6rem 1rem;text-align:center;">
                        @if($insp->has_critical_issue)
                        <span style="padding:.15rem .5rem;background:#ef444420;color:#ef4444;border-radius:.3rem;font-size:.72rem;font-weight:600;">Critique</span>
                        @else
                        <span style="padding:.15rem .5rem;background:#10b98120;color:#10b981;border-radius:.3rem;font-size:.72rem;">OK</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Demandes --}}
    @if($report['requests']->isNotEmpty())
    <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;overflow:hidden;">
        <div style="padding:.75rem 1.25rem;border-bottom:1px solid #334155;">
            <h3 style="font-size:.88rem;font-weight:600;color:#f1f5f9;margin:0;">Demandes de véhicule</h3>
        </div>
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid #334155;">
                    <th style="text-align:left;padding:.55rem 1rem;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;">Demandeur</th>
                    <th style="text-align:left;padding:.55rem 1rem;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;">Départ</th>
                    <th style="text-align:left;padding:.55rem 1rem;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;">Destination</th>
                    <th style="text-align:right;padding:.55rem 1rem;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;">Km</th>
                    <th style="text-align:center;padding:.55rem 1rem;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;">Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['requests'] as $req)
                <tr style="border-bottom:1px solid #334155;{{ $loop->last ? 'border-bottom:none;' : '' }}">
                    <td style="padding:.6rem 1rem;font-size:.85rem;color:#f1f5f9;">{{ $req->requester?->name ?? '—' }}</td>
                    <td style="padding:.6rem 1rem;font-size:.82rem;color:#94a3b8;">{{ $req->datetime_start->format('d/m/Y') }}</td>
                    <td style="padding:.6rem 1rem;font-size:.82rem;color:#94a3b8;">{{ $req->destination ?: '—' }}</td>
                    <td style="padding:.6rem 1rem;text-align:right;font-size:.82rem;color:#94a3b8;">{{ $req->km_total ? number_format($req->km_total, 0, ',', ' ') : '—' }}</td>
                    <td style="padding:.6rem 1rem;text-align:center;">
                        <span style="padding:.15rem .5rem;border-radius:.3rem;font-size:.72rem;font-weight:600;background:#334155;color:#94a3b8;">
                            {{ $req->status }}
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
