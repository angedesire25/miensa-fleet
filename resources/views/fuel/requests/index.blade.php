@extends('layouts.dashboard')

@section('title', 'Mes demandes carburant')
@section('page-title', 'Carburant')
@section('breadcrumb', 'Mes demandes')

@section('content')
<div class="page-content">

    {{-- ── En-tête ──────────────────────────────────────────────────────── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;">
        <div>
            <h1 style="font-size:1.35rem;font-weight:700;color:#0f172a;margin:0;">Mes demandes de carburant</h1>
            <p style="color:#64748b;font-size:.875rem;margin:.2rem 0 0;">Suivez l'état de vos demandes de ravitaillement.</p>
        </div>
        @can('fuel.request')
        <a href="{{ route('fuel.requests.create') }}"
           style="display:inline-flex;align-items:center;gap:.5rem;padding:.6rem 1.2rem;background:linear-gradient(135deg,#10b981,#059669);color:#fff;border-radius:.5rem;font-size:.875rem;font-weight:600;text-decoration:none;">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            Nouvelle demande
        </a>
        @endcan
    </div>

    {{-- ── Statistiques ─────────────────────────────────────────────────── --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:1rem;margin-bottom:1.5rem;">
        @foreach([
            ['label'=>'Total','value'=>$stats['total'],'color'=>'#3b82f6','bg'=>'rgba(59,130,246,.08)'],
            ['label'=>'En attente','value'=>$stats['pending'],'color'=>'#f59e0b','bg'=>'rgba(245,158,11,.08)'],
            ['label'=>'Approuvées','value'=>$stats['approved'],'color'=>'#10b981','bg'=>'rgba(16,185,129,.08)'],
            ['label'=>'Réalisées','value'=>$stats['fulfilled'],'color'=>'#8b5cf6','bg'=>'rgba(139,92,246,.08)'],
        ] as $s)
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1rem 1.25rem;">
            <div style="font-size:.75rem;color:#64748b;font-weight:500;margin-bottom:.3rem;">{{ $s['label'] }}</div>
            <div style="font-size:1.6rem;font-weight:800;color:{{ $s['color'] }};">{{ $s['value'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- ── Filtres ───────────────────────────────────────────────────────── --}}
    <form method="GET" style="display:flex;gap:.6rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <select name="status" onchange="this.form.submit()"
                style="padding:.45rem .75rem;border:1px solid #e2e8f0;border-radius:.45rem;font-size:.85rem;color:#374151;background:#fff;">
            <option value="all" {{ request('status','all')==='all'?'selected':'' }}>Tous les statuts</option>
            <option value="pending"   {{ request('status')==='pending'?'selected':'' }}>En attente</option>
            <option value="approved"  {{ request('status')==='approved'?'selected':'' }}>Approuvées</option>
            <option value="fulfilled" {{ request('status')==='fulfilled'?'selected':'' }}>Réalisées</option>
            <option value="rejected"  {{ request('status')==='rejected'?'selected':'' }}>Rejetées</option>
            <option value="cancelled" {{ request('status')==='cancelled'?'selected':'' }}>Annulées</option>
        </select>
    </form>

    {{-- ── Tableau ───────────────────────────────────────────────────────── --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
        @if($requests->isEmpty())
        <div style="text-align:center;padding:3rem 1rem;">
            <svg width="48" height="48" fill="none" viewBox="0 0 24 24" style="color:#cbd5e1;margin:0 auto 1rem;display:block;">
                <path d="M12 2a10 10 0 100 20A10 10 0 0012 2z" stroke="currentColor" stroke-width="1.5"/>
                <path d="M8 12h8M12 8v8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            <p style="color:#94a3b8;font-size:.9rem;">Aucune demande pour l'instant.</p>
            @can('fuel.request')
            <a href="{{ route('fuel.requests.create') }}"
               style="display:inline-flex;align-items:center;gap:.4rem;margin-top:.75rem;padding:.5rem 1rem;background:#10b981;color:#fff;border-radius:.45rem;font-size:.85rem;text-decoration:none;font-weight:600;">
                Faire une demande
            </a>
            @endcan
        </div>
        @else
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:.875rem;">
                <thead>
                    <tr style="border-bottom:1px solid #e2e8f0;background:#f8fafc;">
                        <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.04em;">Référence</th>
                        <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.04em;">Véhicule</th>
                        <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.04em;">Carburant</th>
                        <th style="padding:.75rem 1rem;text-align:right;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.04em;">Litres</th>
                        <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.04em;">Statut</th>
                        <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.04em;">Date</th>
                        <th style="padding:.75rem 1rem;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $req)
                    @php
                        $statusMap = [
                            'pending'   => ['label'=>'En attente',  'color'=>'#f59e0b','bg'=>'rgba(245,158,11,.1)'],
                            'approved'  => ['label'=>'Approuvée',   'color'=>'#10b981','bg'=>'rgba(16,185,129,.1)'],
                            'fulfilled' => ['label'=>'Réalisée',    'color'=>'#8b5cf6','bg'=>'rgba(139,92,246,.1)'],
                            'rejected'  => ['label'=>'Rejetée',     'color'=>'#ef4444','bg'=>'rgba(239,68,68,.1)'],
                            'cancelled' => ['label'=>'Annulée',     'color'=>'#94a3b8','bg'=>'rgba(148,163,184,.1)'],
                        ];
                        $st = $statusMap[$req->status] ?? ['label'=>$req->status,'color'=>'#64748b','bg'=>'#f1f5f9'];
                    @endphp
                    <tr style="border-bottom:1px solid #f1f5f9;transition:background .1s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                        <td style="padding:.75rem 1rem;font-weight:600;color:#0f172a;">
                            {{ $req->reference }}
                            @if($req->is_urgent)
                                <span style="margin-left:.3rem;font-size:.65rem;background:#fef2f2;color:#ef4444;padding:.1rem .4rem;border-radius:99px;font-weight:700;">URGENT</span>
                            @endif
                        </td>
                        <td style="padding:.75rem 1rem;color:#374151;">
                            @if($req->vehicle)
                                {{ $req->vehicle->brand }} {{ $req->vehicle->model }}<br>
                                <span style="font-size:.75rem;color:#94a3b8;">{{ $req->vehicle->plate }}</span>
                            @else
                                <span style="color:#94a3b8;">—</span>
                            @endif
                        </td>
                        <td style="padding:.75rem 1rem;color:#374151;">
                            {{ ['diesel'=>'Diesel','gasoline'=>'Essence','hybrid'=>'Hybride','electric'=>'Électrique','lpg'=>'GPL'][$req->fuel_type] ?? $req->fuel_type }}
                        </td>
                        <td style="padding:.75rem 1rem;text-align:right;font-weight:600;color:#0f172a;">
                            {{ number_format($req->liters_requested, 0, ',', ' ') }} L
                        </td>
                        <td style="padding:.75rem 1rem;">
                            <span style="display:inline-flex;align-items:center;padding:.2rem .65rem;border-radius:99px;font-size:.75rem;font-weight:600;color:{{ $st['color'] }};background:{{ $st['bg'] }};">
                                {{ $st['label'] }}
                            </span>
                        </td>
                        <td style="padding:.75rem 1rem;color:#64748b;font-size:.82rem;">
                            {{ $req->requested_at?->format('d/m/Y H:i') ?? $req->created_at->format('d/m/Y') }}
                        </td>
                        <td style="padding:.75rem 1rem;text-align:right;">
                            <a href="{{ route('fuel.requests.show', $req) }}"
                               style="display:inline-flex;align-items:center;gap:.3rem;padding:.3rem .7rem;background:#f1f5f9;color:#374151;border-radius:.35rem;font-size:.78rem;font-weight:500;text-decoration:none;">
                                Voir
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($requests->hasPages())
        <div style="padding:.75rem 1rem;border-top:1px solid #f1f5f9;">
            {{ $requests->links() }}
        </div>
        @endif
        @endif
    </div>

</div>
@endsection
