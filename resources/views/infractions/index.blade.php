@extends('layouts.dashboard')

@section('title', 'Infractions')

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
    $statusColor = [
        'open'   => ['bg' => '#f59e0b20', 'color' => '#f59e0b', 'label' => 'Ouverte'],
        'closed' => ['bg' => '#94a3b820', 'color' => '#94a3b8', 'label' => 'Clôturée'],
    ];
    $paymentColor = [
        'unpaid'    => ['bg' => '#ef444420', 'color' => '#ef4444', 'label' => 'Impayée'],
        'paid'      => ['bg' => '#10b98120', 'color' => '#10b981', 'label' => 'Payée'],
        'contested' => ['bg' => '#f59e0b20', 'color' => '#f59e0b', 'label' => 'Contestée'],
        'waived'    => ['bg' => '#94a3b820', 'color' => '#94a3b8', 'label' => 'Remise'],
    ];
@endphp

<div style="padding:1.5rem;">

    {{-- En-tête --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;">
        <div>
            <h1 style="font-size:1.35rem;font-weight:700;color:#f1f5f9;margin:0;">Infractions</h1>
            <p style="color:#94a3b8;font-size:.85rem;margin:.2rem 0 0;">Suivi des infractions routières de la flotte</p>
        </div>
        @can('infractions.create')
        <a href="{{ route('infractions.create') }}"
           style="display:inline-flex;align-items:center;gap:.45rem;padding:.55rem 1.1rem;background:#10b981;color:#fff;border-radius:.45rem;text-decoration:none;font-size:.88rem;font-weight:600;">
            <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2"/></svg>
            Enregistrer une infraction
        </a>
        @endcan
    </div>

    {{-- Statistiques --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:1.5rem;">
        <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;padding:1rem 1.25rem;">
            <div style="font-size:.75rem;color:#94a3b8;margin-bottom:.35rem;">Total</div>
            <div style="font-size:1.6rem;font-weight:700;color:#f1f5f9;">{{ $stats['total'] }}</div>
        </div>
        <div style="background:#1e293b;border:1px solid rgba(245,158,11,.3);border-radius:.65rem;padding:1rem 1.25rem;">
            <div style="font-size:.75rem;color:#94a3b8;margin-bottom:.35rem;">Ouvertes</div>
            <div style="font-size:1.6rem;font-weight:700;color:#f59e0b;">{{ $stats['ouvertes'] }}</div>
        </div>
        <div style="background:#1e293b;border:1px solid rgba(239,68,68,.3);border-radius:.65rem;padding:1rem 1.25rem;">
            <div style="font-size:.75rem;color:#94a3b8;margin-bottom:.35rem;">Impayées</div>
            <div style="font-size:1.6rem;font-weight:700;color:#ef4444;">{{ $stats['non_payees'] }}</div>
        </div>
        <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;padding:1rem 1.25rem;">
            <div style="font-size:.75rem;color:#94a3b8;margin-bottom:.35rem;">Total amendes</div>
            <div style="font-size:1.3rem;font-weight:700;color:#f1f5f9;">{{ number_format($stats['total_amendes'], 0, ',', ' ') }} <span style="font-size:.75rem;color:#94a3b8;">FCFA</span></div>
        </div>
    </div>

    {{-- Filtres --}}
    <form method="GET" action="{{ route('infractions.index') }}"
          style="display:flex;flex-wrap:wrap;gap:.65rem;margin-bottom:1.25rem;align-items:center;">
        <input type="text" name="q" placeholder="Lieu, PV, plaque, conducteur..."
               value="{{ request('q') }}"
               style="flex:1;min-width:180px;background:#1e293b;border:1px solid #475569;border-radius:.4rem;color:#f1f5f9;padding:.4rem .75rem;font-size:.85rem;">
        <select name="status" style="background:#1e293b;color:#e2e8f0;border:1px solid #475569;border-radius:.4rem;padding:.4rem .75rem;font-size:.85rem;">
            <option value="">Statut</option>
            <option value="all"    {{ request('status')==='all'    ? 'selected':'' }}>Tous</option>
            <option value="open"   {{ request('status')==='open'   ? 'selected':'' }}>Ouvertes</option>
            <option value="closed" {{ request('status')==='closed' ? 'selected':'' }}>Clôturées</option>
        </select>
        <select name="type" style="background:#1e293b;color:#e2e8f0;border:1px solid #475569;border-radius:.4rem;padding:.4rem .75rem;font-size:.85rem;">
            <option value="">Type</option>
            <option value="all" {{ request('type')==='all' ? 'selected':'' }}>Tous</option>
            @foreach($typeLabels as $val => $lbl)
            <option value="{{ $val }}" {{ request('type')===$val ? 'selected':'' }}>{{ $lbl }}</option>
            @endforeach
        </select>
        <select name="payment_status" style="background:#1e293b;color:#e2e8f0;border:1px solid #475569;border-radius:.4rem;padding:.4rem .75rem;font-size:.85rem;">
            <option value="">Paiement</option>
            <option value="all"       {{ request('payment_status')==='all'       ? 'selected':'' }}>Tous</option>
            <option value="unpaid"    {{ request('payment_status')==='unpaid'    ? 'selected':'' }}>Impayée</option>
            <option value="paid"      {{ request('payment_status')==='paid'      ? 'selected':'' }}>Payée</option>
            <option value="contested" {{ request('payment_status')==='contested' ? 'selected':'' }}>Contestée</option>
        </select>
        <select name="vehicle_id" style="background:#1e293b;color:#e2e8f0;border:1px solid #475569;border-radius:.4rem;padding:.4rem .75rem;font-size:.85rem;">
            <option value="">Véhicule</option>
            @foreach($vehicles as $v)
            <option value="{{ $v->id }}" {{ request('vehicle_id')==$v->id ? 'selected':'' }}>{{ $v->plate }}</option>
            @endforeach
        </select>
        <button type="submit" style="padding:.4rem .9rem;background:#3b82f6;color:#fff;border:none;border-radius:.4rem;font-size:.85rem;cursor:pointer;">
            Filtrer
        </button>
        @if(request()->hasAny(['q','status','type','payment_status','vehicle_id']))
        <a href="{{ route('infractions.index') }}"
           style="padding:.4rem .9rem;background:#334155;color:#94a3b8;border-radius:.4rem;font-size:.85rem;text-decoration:none;">
            Réinitialiser
        </a>
        @endif
    </form>

    {{-- Table --}}
    <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid #334155;">
                    <th style="text-align:left;padding:.65rem 1rem;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">#</th>
                    <th style="text-align:left;padding:.65rem 1rem;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Véhicule</th>
                    <th style="text-align:left;padding:.65rem 1rem;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Conducteur</th>
                    <th style="text-align:left;padding:.65rem 1rem;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Type</th>
                    <th style="text-align:left;padding:.65rem 1rem;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Date</th>
                    <th style="text-align:right;padding:.65rem 1rem;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Amende</th>
                    <th style="text-align:center;padding:.65rem 1rem;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Paiement</th>
                    <th style="text-align:center;padding:.65rem 1rem;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Statut</th>
                    <th style="padding:.65rem 1rem;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($infractions as $inf)
                @php
                    $st = $statusColor[$inf->status] ?? ['bg'=>'#334155','color'=>'#94a3b8','label'=>$inf->status];
                    $pt = $inf->payment_status ? ($paymentColor[$inf->payment_status] ?? ['bg'=>'#334155','color'=>'#94a3b8','label'=>$inf->payment_status]) : null;
                @endphp
                <tr style="border-bottom:1px solid #334155;{{ $loop->last ? 'border-bottom:none;' : '' }}"
                    onmouseover="this.style.background='#ffffff08'" onmouseout="this.style.background='transparent'">
                    <td style="padding:.75rem 1rem;font-size:.82rem;color:#64748b;">#{{ $inf->id }}</td>
                    <td style="padding:.75rem 1rem;">
                        <div style="font-size:.85rem;font-weight:600;color:#f1f5f9;">{{ $inf->vehicle?->plate ?? '—' }}</div>
                        <div style="font-size:.75rem;color:#64748b;">{{ $inf->vehicle?->brand }} {{ $inf->vehicle?->model }}</div>
                    </td>
                    <td style="padding:.75rem 1rem;">
                        @if($inf->driver)
                        <div style="font-size:.85rem;color:#f1f5f9;">{{ $inf->driver->full_name }}</div>
                        @if($inf->auto_identified)
                        <div style="font-size:.7rem;color:#10b981;">Auto-identifié</div>
                        @endif
                        @elseif($inf->user)
                        <div style="font-size:.85rem;color:#f1f5f9;">{{ $inf->user->name }}</div>
                        @if($inf->auto_identified)
                        <div style="font-size:.7rem;color:#10b981;">Auto-identifié</div>
                        @endif
                        @else
                        <span style="color:#64748b;font-size:.82rem;">Non identifié</span>
                        @endif
                    </td>
                    <td style="padding:.75rem 1rem;font-size:.82rem;color:#e2e8f0;">{{ $typeLabels[$inf->type] ?? $inf->type }}</td>
                    <td style="padding:.75rem 1rem;font-size:.82rem;color:#94a3b8;">{{ $inf->datetime_occurred?->format('d/m/Y') }}</td>
                    <td style="padding:.75rem 1rem;text-align:right;font-size:.85rem;color:#f1f5f9;">
                        {{ $inf->fine_amount ? number_format($inf->fine_amount, 0, ',', ' ').' FCFA' : '—' }}
                    </td>
                    <td style="padding:.75rem 1rem;text-align:center;">
                        @if($pt)
                        <span style="padding:.2rem .55rem;background:{{ $pt['bg'] }};color:{{ $pt['color'] }};border-radius:.35rem;font-size:.72rem;font-weight:600;">
                            {{ $pt['label'] }}
                        </span>
                        @else
                        <span style="color:#64748b;font-size:.78rem;">—</span>
                        @endif
                    </td>
                    <td style="padding:.75rem 1rem;text-align:center;">
                        <span style="padding:.2rem .55rem;background:{{ $st['bg'] }};color:{{ $st['color'] }};border-radius:.35rem;font-size:.72rem;font-weight:600;">
                            {{ $st['label'] }}
                        </span>
                    </td>
                    <td style="padding:.75rem 1rem;text-align:right;">
                        <a href="{{ route('infractions.show', $inf) }}"
                           style="padding:.3rem .65rem;background:#334155;color:#94a3b8;border-radius:.35rem;font-size:.75rem;text-decoration:none;">
                            Voir
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="padding:3rem;text-align:center;color:#64748b;font-size:.88rem;">
                        Aucune infraction enregistrée
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($infractions->hasPages())
    <div style="margin-top:1rem;">{{ $infractions->links() }}</div>
    @endif

</div>
@endsection
