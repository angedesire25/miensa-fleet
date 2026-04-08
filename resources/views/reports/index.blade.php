@extends('layouts.dashboard')

@section('title', 'Rapports')

@section('content')
<div style="padding:1.5rem;">

    <div style="margin-bottom:1.5rem;">
        <h1 style="font-size:1.35rem;font-weight:700;color:#f1f5f9;margin:0;">Rapports</h1>
        <p style="color:#94a3b8;font-size:.85rem;margin:.2rem 0 0;">
            Synthèse de la flotte du {{ $from->format('d/m/Y') }} au {{ $to->format('d/m/Y') }}
        </p>
    </div>

    {{-- Filtre période --}}
    @include('reports._period_filter', [
        'action' => route('reports.index'),
        'from'   => $from,
        'to'     => $to,
    ])

    {{-- KPIs principaux --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:1.5rem;">

        {{-- Véhicules --}}
        @php
            $vTotal    = array_sum($summary['total_vehicles']);
            $vAvail    = $summary['total_vehicles']['available']   ?? 0;
            $vMaint    = $summary['total_vehicles']['maintenance'] ?? 0;
            $vAssigned = $summary['total_vehicles']['assigned']    ?? 0;
        @endphp
        <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;padding:1.1rem 1.25rem;">
            <div style="font-size:.75rem;color:#64748b;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.5rem;">Véhicules</div>
            <div style="font-size:1.6rem;font-weight:700;color:#f1f5f9;">{{ $vTotal }}</div>
            <div style="font-size:.75rem;color:#94a3b8;margin-top:.25rem;">
                {{ $vAvail }} dispo · {{ $vAssigned }} affectés · {{ $vMaint }} maint.
            </div>
        </div>

        {{-- Chauffeurs --}}
        @php
            $dTotal  = array_sum($summary['total_drivers']);
            $dActive = $summary['total_drivers']['active']   ?? 0;
        @endphp
        <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;padding:1.1rem 1.25rem;">
            <div style="font-size:.75rem;color:#64748b;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.5rem;">Chauffeurs</div>
            <div style="font-size:1.6rem;font-weight:700;color:#f1f5f9;">{{ $dTotal }}</div>
            <div style="font-size:.75rem;color:#94a3b8;margin-top:.25rem;">{{ $dActive }} actifs</div>
        </div>

        {{-- Affectations --}}
        @php
            $aComp  = $summary['total_assignments']['completed']   ?? 0;
            $aInPro = $summary['total_assignments']['in_progress'] ?? 0;
            $aPlan  = $summary['total_assignments']['planned']     ?? 0;
        @endphp
        <div style="background:#1e293b;border:1px solid rgba(16,185,129,.3);border-radius:.65rem;padding:1.1rem 1.25rem;">
            <div style="font-size:.75rem;color:#64748b;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.5rem;">Affectations</div>
            <div style="font-size:1.6rem;font-weight:700;color:#10b981;">{{ $aComp + $aInPro + $aPlan }}</div>
            <div style="font-size:.75rem;color:#94a3b8;margin-top:.25rem;">{{ $aComp }} terminées · {{ $aInPro }} en cours</div>
        </div>

        {{-- Kilométrage --}}
        <div style="background:#1e293b;border:1px solid rgba(59,130,246,.3);border-radius:.65rem;padding:1.1rem 1.25rem;">
            <div style="font-size:.75rem;color:#64748b;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.5rem;">Km parcourus</div>
            <div style="font-size:1.6rem;font-weight:700;color:#3b82f6;">{{ number_format($summary['total_km_fleet'], 0, ',', ' ') }}</div>
            <div style="font-size:.75rem;color:#94a3b8;margin-top:.25rem;">km total flotte</div>
        </div>

        {{-- Infractions --}}
        <div style="background:#1e293b;border:1px solid rgba(239,68,68,.3);border-radius:.65rem;padding:1.1rem 1.25rem;">
            <div style="font-size:.75rem;color:#64748b;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.5rem;">Infractions</div>
            <div style="font-size:1.6rem;font-weight:700;color:#ef4444;">{{ $summary['total_infractions']['count'] }}</div>
            <div style="font-size:.75rem;color:#94a3b8;margin-top:.25rem;">{{ number_format($summary['total_infractions']['total_amount'], 0, ',', ' ') }} FCFA</div>
        </div>

        {{-- Alertes actives --}}
        <div style="background:#1e293b;border:1px solid rgba(245,158,11,.3);border-radius:.65rem;padding:1.1rem 1.25rem;">
            <div style="font-size:.75rem;color:#64748b;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.5rem;">Alertes actives</div>
            <div style="font-size:1.6rem;font-weight:700;color:#f59e0b;">{{ $summary['alerts_summary']['total'] }}</div>
            <div style="font-size:.75rem;color:#94a3b8;margin-top:.25rem;">
                {{ $summary['alerts_summary']['by_severity']['critical'] ?? 0 }} critiques
            </div>
        </div>
    </div>

    {{-- Grille inférieure : Top + Navigation rapports --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">

        {{-- Top chauffeurs --}}
        <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;padding:1.25rem;">
            <h3 style="font-size:.85rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin:0 0 1rem;">Top chauffeurs (km)</h3>
            @forelse($summary['top_drivers_km'] as $d)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:.45rem 0;{{ !$loop->last ? 'border-bottom:1px solid #334155;' : '' }}">
                <div>
                    <div style="font-size:.85rem;color:#f1f5f9;">{{ $d->full_name }}</div>
                    <div style="font-size:.75rem;color:#64748b;">{{ $d->matricule }} · {{ $d->nb_assignments }} affectations</div>
                </div>
                <div style="font-size:.88rem;font-weight:600;color:#3b82f6;">{{ number_format($d->total_km, 0, ',', ' ') }} km</div>
            </div>
            @empty
            <p style="color:#64748b;font-size:.85rem;margin:0;">Aucune donnée sur la période.</p>
            @endforelse
        </div>

        {{-- Top véhicules --}}
        <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;padding:1.25rem;">
            <h3 style="font-size:.85rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin:0 0 1rem;">Top véhicules (missions)</h3>
            @forelse($summary['top_vehicles_used'] as $v)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:.45rem 0;{{ !$loop->last ? 'border-bottom:1px solid #334155;' : '' }}">
                <div>
                    <div style="font-size:.85rem;color:#f1f5f9;">{{ $v->plate }}</div>
                    <div style="font-size:.75rem;color:#64748b;">{{ $v->brand }} {{ $v->model }}</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:.88rem;font-weight:600;color:#10b981;">{{ $v->nb_assignments }} missions</div>
                    <div style="font-size:.75rem;color:#64748b;">{{ number_format($v->total_km, 0, ',', ' ') }} km</div>
                </div>
            </div>
            @empty
            <p style="color:#64748b;font-size:.85rem;margin:0;">Aucune donnée sur la période.</p>
            @endforelse
        </div>

        {{-- Navigation rapide --}}
        <div style="grid-column:1/-1;background:#1e293b;border:1px solid #334155;border-radius:.65rem;padding:1.25rem;">
            <h3 style="font-size:.85rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin:0 0 1rem;">Rapports détaillés</h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:.75rem;">

                <a href="{{ route('reports.vehicle', ['from'=>$from->format('Y-m-d'),'to'=>$to->format('Y-m-d')]) }}"
                   style="display:flex;align-items:center;gap:.75rem;padding:1rem;background:#0f172a;border-radius:.5rem;text-decoration:none;border:1px solid #334155;transition:border-color .15s;"
                   onmouseover="this.style.borderColor='#3b82f6'" onmouseout="this.style.borderColor='#334155'">
                    <svg style="width:28px;height:28px;stroke:#3b82f6;flex-shrink:0;" fill="none" viewBox="0 0 24 24"><path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" stroke="currentColor" stroke-width="1.8"/><path d="M13 6h3l2 4H4l2-4h3M5 17H3v-5h18v5h-2M5 12V7" stroke="currentColor" stroke-width="1.8"/></svg>
                    <div>
                        <div style="font-size:.88rem;font-weight:600;color:#f1f5f9;">Rapport véhicule</div>
                        <div style="font-size:.75rem;color:#64748b;">Activité d'un véhicule</div>
                    </div>
                </a>

                <a href="{{ route('reports.driver', ['from'=>$from->format('Y-m-d'),'to'=>$to->format('Y-m-d')]) }}"
                   style="display:flex;align-items:center;gap:.75rem;padding:1rem;background:#0f172a;border-radius:.5rem;text-decoration:none;border:1px solid #334155;transition:border-color .15s;"
                   onmouseover="this.style.borderColor='#10b981'" onmouseout="this.style.borderColor='#334155'">
                    <svg style="width:28px;height:28px;stroke:#10b981;flex-shrink:0;" fill="none" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 7a4 4 0 100 8 4 4 0 000-8zM23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke="currentColor" stroke-width="1.8"/></svg>
                    <div>
                        <div style="font-size:.88rem;font-weight:600;color:#f1f5f9;">Rapport chauffeur</div>
                        <div style="font-size:.75rem;color:#64748b;">Activité d'un chauffeur</div>
                    </div>
                </a>

                <a href="{{ route('reports.infractions', ['from'=>$from->format('Y-m-d'),'to'=>$to->format('Y-m-d')]) }}"
                   style="display:flex;align-items:center;gap:.75rem;padding:1rem;background:#0f172a;border-radius:.5rem;text-decoration:none;border:1px solid #334155;transition:border-color .15s;"
                   onmouseover="this.style.borderColor='#ef4444'" onmouseout="this.style.borderColor='#334155'">
                    <svg style="width:28px;height:28px;stroke:#ef4444;flex-shrink:0;" fill="none" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 000 4h6a2 2 0 000-4M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke="currentColor" stroke-width="1.8"/></svg>
                    <div>
                        <div style="font-size:.88rem;font-weight:600;color:#f1f5f9;">Rapport infractions</div>
                        <div style="font-size:.75rem;color:#64748b;">Analyse des infractions</div>
                    </div>
                </a>

                <a href="{{ route('reports.documents') }}"
                   style="display:flex;align-items:center;gap:.75rem;padding:1rem;background:#0f172a;border-radius:.5rem;text-decoration:none;border:1px solid #334155;transition:border-color .15s;"
                   onmouseover="this.style.borderColor='#f59e0b'" onmouseout="this.style.borderColor='#334155'">
                    <svg style="width:28px;height:28px;stroke:#f59e0b;flex-shrink:0;" fill="none" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z" stroke="currentColor" stroke-width="1.8"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8" stroke="currentColor" stroke-width="1.8"/></svg>
                    <div>
                        <div style="font-size:.88rem;font-weight:600;color:#f1f5f9;">Documents expirants</div>
                        <div style="font-size:.75rem;color:#64748b;">Véhicules et chauffeurs</div>
                    </div>
                </a>

            </div>
        </div>
    </div>

</div>
@endsection
