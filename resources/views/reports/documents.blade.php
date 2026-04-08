@extends('layouts.dashboard')

@section('title', 'Documents expirants')

@section('content')
<div style="padding:1.5rem;">

    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:1.25rem;font-size:.82rem;color:#64748b;">
        <a href="{{ route('reports.index') }}" style="color:#94a3b8;text-decoration:none;">Rapports</a>
        <span>/</span>
        <span style="color:#f1f5f9;">Documents expirants</span>
    </div>

    {{-- Sélecteur d'horizon --}}
    <form method="GET" action="{{ route('reports.documents') }}"
          style="display:flex;align-items:flex-end;gap:.75rem;background:#1e293b;border:1px solid #334155;border-radius:.65rem;padding:1rem 1.25rem;margin-bottom:1.5rem;">
        <div>
            <label style="display:block;font-size:.75rem;color:#64748b;margin-bottom:.3rem;">Horizon (jours)</label>
            <select name="days"
                    style="background:#0f172a;border:1px solid #475569;border-radius:.4rem;color:#f1f5f9;padding:.45rem .75rem;font-size:.85rem;">
                <option value="15"  {{ $days == 15  ? 'selected':'' }}>15 jours</option>
                <option value="30"  {{ $days == 30  ? 'selected':'' }}>30 jours</option>
                <option value="60"  {{ $days == 60  ? 'selected':'' }}>60 jours</option>
                <option value="90"  {{ $days == 90  ? 'selected':'' }}>90 jours</option>
                <option value="180" {{ $days == 180 ? 'selected':'' }}>6 mois</option>
            </select>
        </div>
        <button type="submit"
                style="padding:.45rem 1.1rem;background:#3b82f6;color:#fff;border:none;border-radius:.4rem;font-size:.85rem;font-weight:600;cursor:pointer;">
            Actualiser
        </button>
    </form>

    {{-- Stats --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:1.5rem;">
        <div style="background:#1e293b;border:1px solid rgba(239,68,68,.3);border-radius:.65rem;padding:1rem 1.25rem;">
            <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;margin-bottom:.35rem;">Docs véhicules</div>
            <div style="font-size:1.6rem;font-weight:700;color:#ef4444;">{{ $report['vehicle_documents']->flatten()->count() }}</div>
            <div style="font-size:.72rem;color:#94a3b8;">{{ $report['vehicle_documents']->count() }} véhicule(s) concerné(s)</div>
        </div>
        <div style="background:#1e293b;border:1px solid rgba(245,158,11,.3);border-radius:.65rem;padding:1rem 1.25rem;">
            <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;margin-bottom:.35rem;">Docs chauffeurs</div>
            <div style="font-size:1.6rem;font-weight:700;color:#f59e0b;">{{ $report['driver_documents']->flatten()->count() }}</div>
            <div style="font-size:.72rem;color:#94a3b8;">{{ $report['driver_documents']->count() }} chauffeur(s) concerné(s)</div>
        </div>
    </div>

    {{-- Documents véhicules --}}
    @if($report['vehicle_documents']->isNotEmpty())
    <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;overflow:hidden;margin-bottom:1.25rem;">
        <div style="padding:.75rem 1.25rem;border-bottom:1px solid #334155;display:flex;align-items:center;gap:.5rem;">
            <svg style="width:18px;height:18px;stroke:#3b82f6;" fill="none" viewBox="0 0 24 24"><path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" stroke="currentColor" stroke-width="1.8"/><path d="M13 6h3l2 4H4l2-4h3M5 17H3v-5h18v5h-2" stroke="currentColor" stroke-width="1.8"/></svg>
            <h3 style="font-size:.88rem;font-weight:600;color:#f1f5f9;margin:0;">Documents véhicules</h3>
        </div>
        @foreach($report['vehicle_documents'] as $vehicleId => $docs)
        @php $firstDoc = $docs->first(); $vehicleObj = $firstDoc['vehicle']; @endphp
        <div style="border-bottom:1px solid #334155;">
            {{-- Entête véhicule --}}
            <div style="padding:.6rem 1.25rem;background:#0f172a20;display:flex;align-items:center;gap:.75rem;">
                <svg style="width:14px;height:14px;stroke:#64748b;" fill="none" viewBox="0 0 24 24"><path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" stroke="currentColor" stroke-width="1.5"/><path d="M13 6h3l2 4H4l2-4h3" stroke="currentColor" stroke-width="1.5"/></svg>
                <span style="font-size:.82rem;font-weight:600;color:#f1f5f9;">
                    @if($vehicleObj)
                    <a href="{{ route('vehicles.show', $vehicleObj->id) }}" style="color:#3b82f6;text-decoration:none;">
                        {{ $vehicleObj->plate }} — {{ $vehicleObj->brand }} {{ $vehicleObj->model }}
                    </a>
                    @else
                    Véhicule #{{ $vehicleId }}
                    @endif
                </span>
            </div>
            {{-- Documents --}}
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="text-align:left;padding:.45rem 1.25rem;font-size:.7rem;font-weight:600;color:#64748b;text-transform:uppercase;">Type</th>
                        <th style="text-align:left;padding:.45rem 1rem;font-size:.7rem;font-weight:600;color:#64748b;text-transform:uppercase;">Numéro</th>
                        <th style="text-align:left;padding:.45rem 1rem;font-size:.7rem;font-weight:600;color:#64748b;text-transform:uppercase;">Expiration</th>
                        <th style="text-align:center;padding:.45rem 1rem;font-size:.7rem;font-weight:600;color:#64748b;text-transform:uppercase;">Restant</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($docs as $doc)
                    @php
                        $dr = $doc['days_remaining'];
                        $color = $dr <= 0 ? '#ef4444' : ($dr <= 7 ? '#ef4444' : ($dr <= 30 ? '#f59e0b' : '#10b981'));
                    @endphp
                    <tr style="border-top:1px solid #334155;">
                        <td style="padding:.55rem 1.25rem;font-size:.85rem;color:#f1f5f9;">{{ $doc['type'] }}</td>
                        <td style="padding:.55rem 1rem;font-size:.82rem;color:#94a3b8;">{{ $doc['document_number'] ?: '—' }}</td>
                        <td style="padding:.55rem 1rem;font-size:.82rem;color:{{ $color }};">{{ $doc['expiry_date']->format('d/m/Y') }}</td>
                        <td style="padding:.55rem 1rem;text-align:center;">
                            <span style="padding:.2rem .55rem;background:{{ $color }}20;color:{{ $color }};border-radius:.35rem;font-size:.78rem;font-weight:600;">
                                {{ $dr <= 0 ? 'Expiré' : 'J–'.$dr }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach
    </div>
    @else
    <div style="background:#1e293b;border:1px solid rgba(16,185,129,.2);border-radius:.65rem;padding:1.5rem 1.25rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.75rem;">
        <svg style="width:20px;height:20px;stroke:#10b981;flex-shrink:0;" fill="none" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.8"/></svg>
        <p style="color:#10b981;font-size:.88rem;margin:0;">Aucun document véhicule n'expire dans les {{ $days }} prochains jours.</p>
    </div>
    @endif

    {{-- Documents chauffeurs --}}
    @if($report['driver_documents']->isNotEmpty())
    <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;overflow:hidden;">
        <div style="padding:.75rem 1.25rem;border-bottom:1px solid #334155;display:flex;align-items:center;gap:.5rem;">
            <svg style="width:18px;height:18px;stroke:#10b981;" fill="none" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 7a4 4 0 100 8 4 4 0 000-8z" stroke="currentColor" stroke-width="1.8"/></svg>
            <h3 style="font-size:.88rem;font-weight:600;color:#f1f5f9;margin:0;">Documents chauffeurs</h3>
        </div>
        @foreach($report['driver_documents'] as $driverId => $docs)
        @php $firstDoc = $docs->first(); $driverObj = $firstDoc['driver']; @endphp
        <div style="{{ !$loop->last ? 'border-bottom:1px solid #334155;' : '' }}">
            <div style="padding:.6rem 1.25rem;background:#0f172a20;display:flex;align-items:center;gap:.75rem;">
                <svg style="width:14px;height:14px;stroke:#64748b;" fill="none" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M12 7a4 4 0 100 8 4 4 0 000-8z" stroke="currentColor" stroke-width="1.5"/></svg>
                <span style="font-size:.82rem;font-weight:600;color:#f1f5f9;">
                    @if($driverObj)
                    <a href="{{ route('drivers.show', $driverObj->id) }}" style="color:#10b981;text-decoration:none;">
                        {{ $driverObj->full_name }} ({{ $driverObj->matricule }})
                    </a>
                    @else
                    Chauffeur #{{ $driverId }}
                    @endif
                </span>
            </div>
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="text-align:left;padding:.45rem 1.25rem;font-size:.7rem;font-weight:600;color:#64748b;text-transform:uppercase;">Type</th>
                        <th style="text-align:left;padding:.45rem 1rem;font-size:.7rem;font-weight:600;color:#64748b;text-transform:uppercase;">Numéro</th>
                        <th style="text-align:left;padding:.45rem 1rem;font-size:.7rem;font-weight:600;color:#64748b;text-transform:uppercase;">Expiration</th>
                        <th style="text-align:center;padding:.45rem 1rem;font-size:.7rem;font-weight:600;color:#64748b;text-transform:uppercase;">Restant</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($docs as $doc)
                    @php
                        $dr = $doc['days_remaining'];
                        $color = $dr <= 0 ? '#ef4444' : ($dr <= 7 ? '#ef4444' : ($dr <= 30 ? '#f59e0b' : '#10b981'));
                    @endphp
                    <tr style="border-top:1px solid #334155;">
                        <td style="padding:.55rem 1.25rem;font-size:.85rem;color:#f1f5f9;">{{ $doc['type'] }}</td>
                        <td style="padding:.55rem 1rem;font-size:.82rem;color:#94a3b8;">{{ $doc['document_number'] ?: '—' }}</td>
                        <td style="padding:.55rem 1rem;font-size:.82rem;color:{{ $color }};">{{ $doc['expiry_date']->format('d/m/Y') }}</td>
                        <td style="padding:.55rem 1rem;text-align:center;">
                            <span style="padding:.2rem .55rem;background:{{ $color }}20;color:{{ $color }};border-radius:.35rem;font-size:.78rem;font-weight:600;">
                                {{ $dr <= 0 ? 'Expiré' : 'J–'.$dr }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach
    </div>
    @else
    <div style="background:#1e293b;border:1px solid rgba(16,185,129,.2);border-radius:.65rem;padding:1.5rem 1.25rem;display:flex;align-items:center;gap:.75rem;">
        <svg style="width:20px;height:20px;stroke:#10b981;flex-shrink:0;" fill="none" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.8"/></svg>
        <p style="color:#10b981;font-size:.88rem;margin:0;">Aucun document chauffeur n'expire dans les {{ $days }} prochains jours.</p>
    </div>
    @endif

</div>
@endsection
