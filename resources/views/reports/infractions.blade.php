@extends('layouts.dashboard')

@section('title', 'Rapport infractions')

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
        <span style="color:#f1f5f9;">Rapport infractions</span>
    </div>

    {{-- Filtre période --}}
    @include('reports._period_filter', [
        'action' => route('reports.infractions'),
        'from'   => $from,
        'to'     => $to,
    ])

    {{-- KPIs --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:1.5rem;">
        <div style="background:#1e293b;border:1px solid rgba(239,68,68,.3);border-radius:.65rem;padding:1rem 1.25rem;">
            <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;margin-bottom:.35rem;">Total infractions</div>
            <div style="font-size:1.6rem;font-weight:700;color:#ef4444;">{{ $report['total'] }}</div>
        </div>
        <div style="background:#1e293b;border:1px solid rgba(245,158,11,.3);border-radius:.65rem;padding:1rem 1.25rem;">
            <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;margin-bottom:.35rem;">Total amendes</div>
            <div style="font-size:1.3rem;font-weight:700;color:#f59e0b;">{{ number_format($report['total_amount'], 0, ',', ' ') }}</div>
            <div style="font-size:.72rem;color:#94a3b8;">FCFA</div>
        </div>
        <div style="background:#1e293b;border:1px solid rgba(239,68,68,.3);border-radius:.65rem;padding:1rem 1.25rem;">
            <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;margin-bottom:.35rem;">Impayées</div>
            <div style="font-size:1.6rem;font-weight:700;color:#ef4444;">{{ $report['payment_status_summary']['unpaid'] ?? 0 }}</div>
        </div>
        <div style="background:#1e293b;border:1px solid rgba(59,130,246,.3);border-radius:.65rem;padding:1rem 1.25rem;">
            <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;margin-bottom:.35rem;">Imputées société</div>
            <div style="font-size:1.6rem;font-weight:700;color:#3b82f6;">{{ $report['imputation_summary']['company'] ?? 0 }}</div>
        </div>
        <div style="background:#1e293b;border:1px solid rgba(245,158,11,.3);border-radius:.65rem;padding:1rem 1.25rem;">
            <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;margin-bottom:.35rem;">Imputées conducteur</div>
            <div style="font-size:1.6rem;font-weight:700;color:#f59e0b;">{{ $report['imputation_summary']['driver'] ?? 0 }}</div>
        </div>
    </div>

    @if($report['total'] > 0)
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.25rem;">

        {{-- Répartition par type --}}
        <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;padding:1.25rem;">
            <h3 style="font-size:.85rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin:0 0 1rem;">Par type</h3>
            @forelse($report['by_type'] as $type => $count)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:.4rem 0;{{ !$loop->last ? 'border-bottom:1px solid #334155;' : '' }}">
                <span style="font-size:.85rem;color:#f1f5f9;">{{ $typeLabels[$type] ?? $type }}</span>
                <div style="display:flex;align-items:center;gap:.75rem;">
                    <div style="width:80px;height:6px;background:#334155;border-radius:3px;overflow:hidden;">
                        <div style="height:100%;width:{{ $report['total'] > 0 ? round($count/$report['total']*100) : 0 }}%;background:#3b82f6;border-radius:3px;"></div>
                    </div>
                    <span style="font-size:.82rem;font-weight:600;color:#3b82f6;min-width:24px;text-align:right;">{{ $count }}</span>
                </div>
            </div>
            @empty
            <p style="color:#64748b;font-size:.85rem;margin:0;">Aucune donnée.</p>
            @endforelse
        </div>

        {{-- Statut paiement --}}
        <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;padding:1.25rem;">
            <h3 style="font-size:.85rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin:0 0 1rem;">Statut des paiements</h3>
            @php
                $payLabels = ['unpaid'=>'Impayée','paid'=>'Payée','contested'=>'Contestée','waived'=>'Remise'];
                $payColors = ['unpaid'=>'#ef4444','paid'=>'#10b981','contested'=>'#f59e0b','waived'=>'#94a3b8'];
            @endphp
            @forelse($report['payment_status_summary'] as $ps => $count)
            @if($ps)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:.4rem 0;{{ !$loop->last ? 'border-bottom:1px solid #334155;' : '' }}">
                <span style="font-size:.85rem;color:#f1f5f9;">{{ $payLabels[$ps] ?? $ps }}</span>
                <span style="padding:.2rem .6rem;background:{{ ($payColors[$ps] ?? '#94a3b8') }}20;color:{{ $payColors[$ps] ?? '#94a3b8' }};border-radius:.35rem;font-size:.8rem;font-weight:600;">{{ $count }}</span>
            </div>
            @endif
            @empty
            <p style="color:#64748b;font-size:.85rem;margin:0;">Aucune donnée.</p>
            @endforelse
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">

        {{-- Top conducteurs --}}
        @if($report['by_driver']->isNotEmpty())
        <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;overflow:hidden;">
            <div style="padding:.75rem 1.25rem;border-bottom:1px solid #334155;">
                <h3 style="font-size:.88rem;font-weight:600;color:#f1f5f9;margin:0;">Top conducteurs impliqués</h3>
            </div>
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid #334155;">
                        <th style="text-align:left;padding:.5rem 1rem;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;">Conducteur</th>
                        <th style="text-align:right;padding:.5rem 1rem;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;">Nbre</th>
                        <th style="text-align:right;padding:.5rem 1rem;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report['by_driver'] as $d)
                    <tr style="border-bottom:1px solid #334155;{{ $loop->last ? 'border-bottom:none;' : '' }}">
                        <td style="padding:.55rem 1rem;">
                            <div style="font-size:.85rem;color:#f1f5f9;">{{ $d->full_name }}</div>
                            <div style="font-size:.72rem;color:#64748b;">{{ $d->matricule }}</div>
                        </td>
                        <td style="padding:.55rem 1rem;text-align:right;font-size:.88rem;font-weight:600;color:#ef4444;">{{ $d->nb_infractions }}</td>
                        <td style="padding:.55rem 1rem;text-align:right;font-size:.82rem;color:#94a3b8;">{{ number_format($d->total_amount, 0, ',', ' ') }} FCFA</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Top véhicules --}}
        @if($report['by_vehicle']->isNotEmpty())
        <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;overflow:hidden;">
            <div style="padding:.75rem 1.25rem;border-bottom:1px solid #334155;">
                <h3 style="font-size:.88rem;font-weight:600;color:#f1f5f9;margin:0;">Top véhicules impliqués</h3>
            </div>
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid #334155;">
                        <th style="text-align:left;padding:.5rem 1rem;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;">Véhicule</th>
                        <th style="text-align:right;padding:.5rem 1rem;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;">Nbre</th>
                        <th style="text-align:right;padding:.5rem 1rem;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report['by_vehicle'] as $v)
                    <tr style="border-bottom:1px solid #334155;{{ $loop->last ? 'border-bottom:none;' : '' }}">
                        <td style="padding:.55rem 1rem;">
                            <div style="font-size:.85rem;color:#f1f5f9;">{{ $v->plate }}</div>
                            <div style="font-size:.72rem;color:#64748b;">{{ $v->brand }} {{ $v->model }}</div>
                        </td>
                        <td style="padding:.55rem 1rem;text-align:right;font-size:.88rem;font-weight:600;color:#ef4444;">{{ $v->nb_infractions }}</td>
                        <td style="padding:.55rem 1rem;text-align:right;font-size:.82rem;color:#94a3b8;">{{ number_format($v->total_amount, 0, ',', ' ') }} FCFA</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @else
    <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;padding:3rem;text-align:center;">
        <p style="color:#64748b;font-size:.9rem;margin:0;">Aucune infraction enregistrée sur cette période.</p>
        <a href="{{ route('infractions.index') }}"
           style="display:inline-block;margin-top:.75rem;padding:.45rem .9rem;background:#334155;color:#94a3b8;border-radius:.4rem;text-decoration:none;font-size:.82rem;">
            Voir toutes les infractions
        </a>
    </div>
    @endif

</div>
@endsection
