@extends('layouts.dashboard')

@section('title', 'Demande '.$fuelRequest->reference)
@section('page-title', 'Carburant')
@section('breadcrumb', $fuelRequest->reference)

@section('content')
<div class="page-content" style="max-width:820px;">

    @if(session('success'))
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:.65rem;padding:.85rem 1rem;margin-bottom:1.25rem;color:#166534;font-size:.875rem;display:flex;align-items:center;gap:.5rem;">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M22 4L12 14.01l-3-3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:.65rem;padding:.85rem 1rem;margin-bottom:1.25rem;color:#b91c1c;font-size:.875rem;">
        {{ session('error') }}
    </div>
    @endif

    {{-- En-tête --}}
    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.5rem;">
        <a href="{{ route('fuel.requests.index') }}"
           style="display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;background:#fff;border:1px solid #e2e8f0;border-radius:.45rem;color:#64748b;text-decoration:none;">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        </a>
        <div style="flex:1;">
            <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
                <h1 style="font-size:1.2rem;font-weight:700;color:#0f172a;margin:0;">{{ $fuelRequest->reference }}</h1>
                @php
                    $statusMap = [
                        'pending'   => ['label'=>'En attente',  'color'=>'#f59e0b','bg'=>'rgba(245,158,11,.1)'],
                        'approved'  => ['label'=>'Approuvée',   'color'=>'#10b981','bg'=>'rgba(16,185,129,.1)'],
                        'fulfilled' => ['label'=>'Réalisée',    'color'=>'#8b5cf6','bg'=>'rgba(139,92,246,.1)'],
                        'rejected'  => ['label'=>'Rejetée',     'color'=>'#ef4444','bg'=>'rgba(239,68,68,.1)'],
                        'cancelled' => ['label'=>'Annulée',     'color'=>'#94a3b8','bg'=>'rgba(148,163,184,.1)'],
                    ];
                    $st = $statusMap[$fuelRequest->status] ?? ['label'=>$fuelRequest->status,'color'=>'#64748b','bg'=>'#f1f5f9'];
                @endphp
                <span style="padding:.25rem .75rem;border-radius:99px;font-size:.78rem;font-weight:700;color:{{ $st['color'] }};background:{{ $st['bg'] }};">
                    {{ $st['label'] }}
                </span>
                @if($fuelRequest->is_urgent)
                <span style="padding:.25rem .65rem;border-radius:99px;font-size:.72rem;font-weight:700;color:#ef4444;background:#fef2f2;">⚡ URGENT</span>
                @endif
            </div>
            <p style="color:#64748b;font-size:.82rem;margin:.2rem 0 0;">
                Soumise le {{ $fuelRequest->requested_at?->format('d/m/Y à H:i') ?? $fuelRequest->created_at->format('d/m/Y') }}
            </p>
        </div>

        {{-- Annulation --}}
        @if($fuelRequest->canBeCancelled() && (auth()->id() === $fuelRequest->requester_id || auth()->user()->hasAnyRole(['super_admin','admin','fleet_manager'])))
        <form method="POST" action="{{ route('fuel.requests.cancel', $fuelRequest) }}" id="cancelForm">
            @csrf
            <button type="button" onclick="confirmCancel()"
                    style="display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;background:#fff;border:1px solid #fecaca;border-radius:.45rem;color:#ef4444;font-size:.85rem;font-weight:600;cursor:pointer;">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Annuler
            </button>
        </form>
        @endif
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">

        {{-- Informations de la demande --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;grid-column:1/-1;">
            <h2 style="font-size:.9rem;font-weight:700;color:#374151;margin:0 0 1rem;text-transform:uppercase;letter-spacing:.04em;">Détails de la demande</h2>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;">
                <div>
                    <div style="font-size:.75rem;color:#94a3b8;font-weight:500;margin-bottom:.2rem;">Véhicule</div>
                    <div style="font-size:.9rem;color:#0f172a;font-weight:600;">
                        @if($fuelRequest->vehicle)
                            {{ $fuelRequest->vehicle->brand }} {{ $fuelRequest->vehicle->model }}
                            <span style="color:#64748b;font-size:.8rem;font-weight:400;">({{ $fuelRequest->vehicle->plate }})</span>
                        @else — @endif
                    </div>
                </div>
                <div>
                    <div style="font-size:.75rem;color:#94a3b8;font-weight:500;margin-bottom:.2rem;">Type carburant</div>
                    <div style="font-size:.9rem;color:#0f172a;font-weight:600;">
                        {{ ['diesel'=>'Diesel','gasoline'=>'Essence','hybrid'=>'Hybride','electric'=>'Électrique','lpg'=>'GPL'][$fuelRequest->fuel_type] ?? $fuelRequest->fuel_type }}
                    </div>
                </div>
                <div>
                    <div style="font-size:.75rem;color:#94a3b8;font-weight:500;margin-bottom:.2rem;">Litres demandés</div>
                    <div style="font-size:.9rem;color:#0f172a;font-weight:700;">{{ number_format($fuelRequest->liters_requested, 0, ',', ' ') }} L</div>
                </div>
                @if($fuelRequest->estimated_amount)
                <div>
                    <div style="font-size:.75rem;color:#94a3b8;font-weight:500;margin-bottom:.2rem;">Montant estimé</div>
                    <div style="font-size:.9rem;color:#0f172a;font-weight:600;">{{ number_format($fuelRequest->estimated_amount, 0, ',', ' ') }} FCFA</div>
                </div>
                @endif
                <div>
                    <div style="font-size:.75rem;color:#94a3b8;font-weight:500;margin-bottom:.2rem;">Kilométrage</div>
                    <div style="font-size:.9rem;color:#0f172a;font-weight:600;">{{ number_format($fuelRequest->odometer_km, 0, ',', ' ') }} km</div>
                </div>
                @if($fuelRequest->fuelStation)
                <div>
                    <div style="font-size:.75rem;color:#94a3b8;font-weight:500;margin-bottom:.2rem;">Station suggérée</div>
                    <div style="font-size:.9rem;color:#0f172a;font-weight:600;">{{ $fuelRequest->fuelStation->name }}</div>
                </div>
                @endif
            </div>
            @if($fuelRequest->reason)
            <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid #f1f5f9;">
                <div style="font-size:.75rem;color:#94a3b8;font-weight:500;margin-bottom:.3rem;">Motif</div>
                <p style="font-size:.875rem;color:#374151;margin:0;line-height:1.55;">{{ $fuelRequest->reason }}</p>
            </div>
            @endif
            @if($fuelRequest->notes)
            <div style="margin-top:.75rem;padding:.75rem;background:#f8fafc;border-radius:.45rem;">
                <div style="font-size:.75rem;color:#94a3b8;font-weight:500;margin-bottom:.25rem;">Notes</div>
                <p style="font-size:.82rem;color:#64748b;margin:0;">{{ $fuelRequest->notes }}</p>
            </div>
            @endif
        </div>

        {{-- Décision du gestionnaire --}}
        @if(in_array($fuelRequest->status, ['approved','rejected','fulfilled']))
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;grid-column:1/-1;">
            <h2 style="font-size:.9rem;font-weight:700;color:#374151;margin:0 0 1rem;text-transform:uppercase;letter-spacing:.04em;">Décision</h2>
            <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
                <div>
                    <div style="font-size:.75rem;color:#94a3b8;font-weight:500;margin-bottom:.2rem;">Traitée par</div>
                    <div style="font-size:.9rem;color:#0f172a;font-weight:600;">{{ $fuelRequest->reviewedBy?->name ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:.75rem;color:#94a3b8;font-weight:500;margin-bottom:.2rem;">Le</div>
                    <div style="font-size:.9rem;color:#0f172a;font-weight:600;">{{ $fuelRequest->reviewed_at?->format('d/m/Y à H:i') ?? '—' }}</div>
                </div>
            </div>
            @if($fuelRequest->review_notes)
            <div style="margin-top:.75rem;padding:.75rem;background:{{ $fuelRequest->isRejected() ? '#fef2f2' : '#f0fdf4' }};border-radius:.45rem;">
                <p style="font-size:.875rem;color:{{ $fuelRequest->isRejected() ? '#b91c1c' : '#166534' }};margin:0;">
                    {{ $fuelRequest->review_notes }}
                </p>
            </div>
            @endif
        </div>
        @endif

        {{-- Transactions liées --}}
        @if($fuelRequest->fuelTransactions->isNotEmpty())
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;grid-column:1/-1;">
            <h2 style="font-size:.9rem;font-weight:700;color:#374151;margin:0 0 1rem;text-transform:uppercase;letter-spacing:.04em;">Ravitaillement effectué</h2>
            @foreach($fuelRequest->fuelTransactions as $tx)
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:1rem;padding:.75rem;background:#f8fafc;border-radius:.5rem;">
                <div>
                    <div style="font-size:.75rem;color:#94a3b8;font-weight:500;margin-bottom:.2rem;">Référence</div>
                    <div style="font-size:.88rem;color:#0f172a;font-weight:700;">{{ $tx->reference }}</div>
                </div>
                <div>
                    <div style="font-size:.75rem;color:#94a3b8;font-weight:500;margin-bottom:.2rem;">Date</div>
                    <div style="font-size:.88rem;color:#0f172a;font-weight:600;">{{ $tx->fueled_at->format('d/m/Y') }}</div>
                </div>
                <div>
                    <div style="font-size:.75rem;color:#94a3b8;font-weight:500;margin-bottom:.2rem;">Litres</div>
                    <div style="font-size:.88rem;color:#0f172a;font-weight:700;">{{ number_format($tx->liters, 1, ',', ' ') }} L</div>
                </div>
                <div>
                    <div style="font-size:.75rem;color:#94a3b8;font-weight:500;margin-bottom:.2rem;">Montant</div>
                    <div style="font-size:.88rem;color:#0f172a;font-weight:700;">{{ number_format($tx->total_amount, 0, ',', ' ') }} FCFA</div>
                </div>
                <div>
                    <div style="font-size:.75rem;color:#94a3b8;font-weight:500;margin-bottom:.2rem;">Saisi par</div>
                    <div style="font-size:.88rem;color:#64748b;">{{ $tx->recordedBy?->name ?? '—' }}</div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

    </div>

</div>

<script>
function confirmCancel() {
    Swal.fire({
        title: 'Annuler la demande ?',
        text: 'Cette action est irréversible.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Oui, annuler',
        cancelButtonText: 'Non',
        confirmButtonColor: '#ef4444',
    }).then(result => {
        if (result.isConfirmed) document.getElementById('cancelForm').submit();
    });
}
</script>
@endsection
