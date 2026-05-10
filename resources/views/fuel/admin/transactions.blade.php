@extends('layouts.dashboard')

@section('title', 'Transactions carburant')
@section('page-title', 'Carburant')
@section('breadcrumb', 'Transactions')

@section('content')
<div class="page-content">

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;">
        <div>
            <h1 style="font-size:1.35rem;font-weight:700;color:#0f172a;margin:0;">Transactions carburant</h1>
            <p style="color:#64748b;font-size:.875rem;margin:.2rem 0 0;">Historique complet des ravitaillements.</p>
        </div>
        <div style="display:flex;gap:.6rem;flex-wrap:wrap;">
            <a href="{{ route('fuel.admin.dashboard') }}"
               style="display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;background:#fff;border:1px solid #e2e8f0;border-radius:.45rem;color:#374151;font-size:.85rem;text-decoration:none;">
                ← Tableau de bord
            </a>
            @can('fuel.record')
            <a href="{{ route('fuel.admin.transaction-create') }}"
               style="display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;background:linear-gradient(135deg,#10b981,#059669);color:#fff;border-radius:.45rem;font-size:.85rem;font-weight:600;text-decoration:none;">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Nouveau plein
            </a>
            @endcan
        </div>
    </div>

    {{-- Totaux --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:1.25rem;">
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:.9rem 1.1rem;">
            <div style="font-size:.7rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem;">Total litres</div>
            <div style="font-size:1.4rem;font-weight:800;color:#8b5cf6;">{{ number_format($totals['liters'],0,',',' ') }} L</div>
        </div>
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:.9rem 1.1rem;">
            <div style="font-size:.7rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem;">Coût total</div>
            <div style="font-size:1.4rem;font-weight:800;color:#ef4444;">{{ number_format($totals['cost'],0,',',' ') }} FCFA</div>
        </div>
    </div>

    {{-- Filtres --}}
    <form method="GET" style="display:flex;gap:.6rem;margin-bottom:1.25rem;flex-wrap:wrap;align-items:center;">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Référence, plaque…"
               style="padding:.45rem .75rem;border:1px solid #e2e8f0;border-radius:.45rem;font-size:.85rem;color:#374151;min-width:180px;">
        <select name="vehicle_id"
                style="padding:.45rem .75rem;border:1px solid #e2e8f0;border-radius:.45rem;font-size:.85rem;color:#374151;background:#fff;">
            <option value="">Tous véhicules</option>
            @foreach($vehicles as $v)
            <option value="{{ $v->id }}" {{ request('vehicle_id')==$v->id?'selected':'' }}>{{ $v->brand }} {{ $v->model }} — {{ $v->plate }}</option>
            @endforeach
        </select>
        <select name="fuel_type"
                style="padding:.45rem .75rem;border:1px solid #e2e8f0;border-radius:.45rem;font-size:.85rem;color:#374151;background:#fff;">
            <option value="">Tous types</option>
            @foreach(['diesel'=>'Diesel','gasoline'=>'Essence','hybrid'=>'Hybride','electric'=>'Électrique','lpg'=>'GPL'] as $v => $l)
            <option value="{{ $v }}" {{ request('fuel_type')===$v?'selected':'' }}>{{ $l }}</option>
            @endforeach
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}"
               style="padding:.45rem .75rem;border:1px solid #e2e8f0;border-radius:.45rem;font-size:.85rem;color:#374151;">
        <input type="date" name="date_to" value="{{ request('date_to') }}"
               style="padding:.45rem .75rem;border:1px solid #e2e8f0;border-radius:.45rem;font-size:.85rem;color:#374151;">
        <button type="submit"
                style="padding:.45rem .9rem;background:#0f172a;color:#fff;border:none;border-radius:.45rem;font-size:.85rem;cursor:pointer;">
            Filtrer
        </button>
        @if(request()->anyFilled(['q','vehicle_id','fuel_type','date_from','date_to']))
        <a href="{{ route('fuel.admin.transactions') }}" style="font-size:.82rem;color:#64748b;text-decoration:none;">Réinitialiser</a>
        @endif
    </form>

    {{-- Tableau --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
        @if($transactions->isEmpty())
        <div style="text-align:center;padding:3rem;">
            <p style="color:#94a3b8;">Aucune transaction trouvée.</p>
        </div>
        @else
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:.875rem;">
                <thead>
                    <tr style="border-bottom:1px solid #e2e8f0;background:#f8fafc;">
                        @foreach(['Référence','Date','Véhicule','Chauffeur','Type','Litres','Prix/L','Montant','Conso','Station','Carte','Saisi par'] as $h)
                        <th style="padding:.65rem .85rem;text-align:left;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap;">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $tx)
                    <tr style="border-bottom:1px solid #f1f5f9;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                        <td style="padding:.65rem .85rem;font-weight:600;color:#0f172a;white-space:nowrap;">{{ $tx->reference }}</td>
                        <td style="padding:.65rem .85rem;color:#374151;white-space:nowrap;">{{ $tx->fueled_at->format('d/m/Y') }}</td>
                        <td style="padding:.65rem .85rem;color:#374151;white-space:nowrap;">
                            @if($tx->vehicle){{ $tx->vehicle->brand }} {{ $tx->vehicle->model }}<br><span style="font-size:.72rem;color:#94a3b8;">{{ $tx->vehicle->plate }}</span>@else —@endif
                        </td>
                        <td style="padding:.65rem .85rem;color:#374151;white-space:nowrap;">{{ $tx->driver?->full_name ?? '—' }}</td>
                        <td style="padding:.65rem .85rem;white-space:nowrap;">
                            {{ ['diesel'=>'Diesel','gasoline'=>'Essence','hybrid'=>'Hybride','electric'=>'Électrique','lpg'=>'GPL'][$tx->fuel_type] ?? $tx->fuel_type }}
                        </td>
                        <td style="padding:.65rem .85rem;font-weight:700;color:#8b5cf6;text-align:right;white-space:nowrap;">{{ number_format($tx->liters,1,',',' ') }} L</td>
                        <td style="padding:.65rem .85rem;color:#64748b;text-align:right;white-space:nowrap;">{{ number_format($tx->unit_price,0,',',' ') }}</td>
                        <td style="padding:.65rem .85rem;font-weight:700;color:#0f172a;text-align:right;white-space:nowrap;">{{ number_format($tx->total_amount,0,',',' ') }}</td>
                        <td style="padding:.65rem .85rem;color:#64748b;text-align:right;white-space:nowrap;">
                            @if($tx->consumption_per_100km){{ number_format($tx->consumption_per_100km,1,',','') }} L/100@else —@endif
                        </td>
                        <td style="padding:.65rem .85rem;color:#64748b;white-space:nowrap;font-size:.8rem;">{{ $tx->station_label }}</td>
                        <td style="padding:.65rem .85rem;text-align:center;">
                            @if($tx->fuel_card_used)
                                <span style="padding:.15rem .5rem;background:rgba(16,185,129,.1);color:#059669;border-radius:99px;font-size:.7rem;font-weight:600;">Carte</span>
                            @else
                                <span style="color:#94a3b8;font-size:.8rem;">—</span>
                            @endif
                        </td>
                        <td style="padding:.65rem .85rem;color:#64748b;white-space:nowrap;font-size:.8rem;">{{ $tx->recordedBy?->name ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
        <div style="padding:.75rem 1rem;border-top:1px solid #f1f5f9;">{{ $transactions->links() }}</div>
        @endif
        @endif
    </div>

</div>
@endsection
