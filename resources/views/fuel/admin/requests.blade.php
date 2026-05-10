@extends('layouts.dashboard')

@section('title', 'Demandes carburant')
@section('page-title', 'Carburant')
@section('breadcrumb', 'Demandes')

@section('content')
<div class="page-content">

    {{-- En-tête --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;">
        <div>
            <h1 style="font-size:1.35rem;font-weight:700;color:#0f172a;margin:0;">Demandes de carburant</h1>
            <p style="color:#64748b;font-size:.875rem;margin:.2rem 0 0;">Toutes les demandes soumises par les collaborateurs et chauffeurs.</p>
        </div>
        <a href="{{ route('fuel.admin.dashboard') }}"
           style="display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;background:#fff;border:1px solid #e2e8f0;border-radius:.45rem;color:#374151;font-size:.85rem;text-decoration:none;">
            ← Tableau de bord
        </a>
    </div>

    {{-- Statistiques --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:1rem;margin-bottom:1.25rem;">
        @foreach([
            ['label'=>'En attente','value'=>$stats['pending'],'color'=>'#f59e0b','status'=>'pending'],
            ['label'=>'Approuvées','value'=>$stats['approved'],'color'=>'#10b981','status'=>'approved'],
            ['label'=>'Réalisées','value'=>$stats['fulfilled'],'color'=>'#8b5cf6','status'=>'fulfilled'],
            ['label'=>'Urgentes','value'=>$stats['urgent'],'color'=>'#ef4444','status'=>null,'urgent'=>true],
        ] as $s)
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:.9rem 1.1rem;cursor:pointer;"
             onclick="location.href='{{ route('fuel.admin.requests', array_filter(['status'=>$s['status']??null,'urgent_only'=>($s['urgent']??false)?'1':null])) }}'">
            <div style="font-size:.7rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem;">{{ $s['label'] }}</div>
            <div style="font-size:1.6rem;font-weight:800;color:{{ $s['color'] }};">{{ $s['value'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Filtres --}}
    <form method="GET" style="display:flex;gap:.6rem;margin-bottom:1.25rem;flex-wrap:wrap;align-items:center;">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Référence, plaque, nom…"
               style="padding:.45rem .75rem;border:1px solid #e2e8f0;border-radius:.45rem;font-size:.85rem;color:#374151;min-width:200px;">
        <select name="status"
                style="padding:.45rem .75rem;border:1px solid #e2e8f0;border-radius:.45rem;font-size:.85rem;color:#374151;background:#fff;">
            <option value="all" {{ request('status','all')==='all'?'selected':'' }}>Tous statuts</option>
            <option value="pending"   {{ request('status')==='pending'?'selected':'' }}>En attente</option>
            <option value="approved"  {{ request('status')==='approved'?'selected':'' }}>Approuvées</option>
            <option value="fulfilled" {{ request('status')==='fulfilled'?'selected':'' }}>Réalisées</option>
            <option value="rejected"  {{ request('status')==='rejected'?'selected':'' }}>Rejetées</option>
            <option value="cancelled" {{ request('status')==='cancelled'?'selected':'' }}>Annulées</option>
        </select>
        <label style="display:flex;align-items:center;gap:.4rem;font-size:.85rem;color:#374151;cursor:pointer;">
            <input type="checkbox" name="urgent_only" value="1" {{ request('urgent_only')?'checked':'' }}
                   style="accent-color:#ef4444;">
            Urgentes seulement
        </label>
        <button type="submit"
                style="padding:.45rem .9rem;background:#0f172a;color:#fff;border:none;border-radius:.45rem;font-size:.85rem;cursor:pointer;">
            Filtrer
        </button>
        @if(request()->anyFilled(['q','status','urgent_only']))
        <a href="{{ route('fuel.admin.requests') }}" style="font-size:.82rem;color:#64748b;text-decoration:none;">Réinitialiser</a>
        @endif
    </form>

    {{-- Tableau --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
        @if($requests->isEmpty())
        <div style="text-align:center;padding:3rem 1rem;">
            <p style="color:#94a3b8;font-size:.9rem;">Aucune demande correspondant aux critères.</p>
        </div>
        @else
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:.875rem;">
                <thead>
                    <tr style="border-bottom:1px solid #e2e8f0;background:#f8fafc;">
                        @foreach(['Référence','Demandeur','Véhicule','Type','Litres','Station','Statut','Soumise le',''] as $h)
                        <th style="padding:.75rem 1rem;text-align:left;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap;">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $req)
                    @php
                        $stMap = [
                            'pending'   => ['label'=>'En attente',  'color'=>'#f59e0b','bg'=>'rgba(245,158,11,.1)'],
                            'approved'  => ['label'=>'Approuvée',   'color'=>'#10b981','bg'=>'rgba(16,185,129,.1)'],
                            'fulfilled' => ['label'=>'Réalisée',    'color'=>'#8b5cf6','bg'=>'rgba(139,92,246,.1)'],
                            'rejected'  => ['label'=>'Rejetée',     'color'=>'#ef4444','bg'=>'rgba(239,68,68,.1)'],
                            'cancelled' => ['label'=>'Annulée',     'color'=>'#94a3b8','bg'=>'rgba(148,163,184,.1)'],
                        ];
                        $st = $stMap[$req->status] ?? ['label'=>$req->status,'color'=>'#64748b','bg'=>'#f1f5f9'];
                    @endphp
                    <tr style="border-bottom:1px solid #f1f5f9;transition:background .1s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                        <td style="padding:.75rem 1rem;font-weight:600;color:#0f172a;white-space:nowrap;">
                            {{ $req->reference }}
                            @if($req->is_urgent)<span style="margin-left:.3rem;font-size:.62rem;background:#fef2f2;color:#ef4444;padding:.1rem .35rem;border-radius:99px;font-weight:700;">⚡</span>@endif
                        </td>
                        <td style="padding:.75rem 1rem;color:#374151;white-space:nowrap;">{{ $req->requester?->name ?? '—' }}</td>
                        <td style="padding:.75rem 1rem;color:#374151;white-space:nowrap;">
                            @if($req->vehicle)
                                {{ $req->vehicle->brand }} {{ $req->vehicle->model }}<br>
                                <span style="font-size:.75rem;color:#94a3b8;">{{ $req->vehicle->plate }}</span>
                            @else —
                            @endif
                        </td>
                        <td style="padding:.75rem 1rem;color:#374151;white-space:nowrap;">
                            {{ ['diesel'=>'Diesel','gasoline'=>'Essence','hybrid'=>'Hybride','electric'=>'Électrique','lpg'=>'GPL'][$req->fuel_type] ?? $req->fuel_type }}
                        </td>
                        <td style="padding:.75rem 1rem;font-weight:600;color:#0f172a;text-align:right;white-space:nowrap;">{{ number_format($req->liters_requested,0,',',' ') }} L</td>
                        <td style="padding:.75rem 1rem;color:#64748b;font-size:.82rem;">{{ $req->fuelStation?->name ?? '—' }}</td>
                        <td style="padding:.75rem 1rem;">
                            <span style="display:inline-flex;padding:.2rem .65rem;border-radius:99px;font-size:.72rem;font-weight:600;color:{{ $st['color'] }};background:{{ $st['bg'] }};">{{ $st['label'] }}</span>
                        </td>
                        <td style="padding:.75rem 1rem;color:#64748b;font-size:.82rem;white-space:nowrap;">{{ $req->requested_at?->format('d/m/Y H:i') ?? $req->created_at->format('d/m/Y') }}</td>
                        <td style="padding:.75rem 1rem;text-align:right;">
                            <a href="{{ route('fuel.admin.request-show', $req) }}"
                               style="display:inline-flex;align-items:center;gap:.3rem;padding:.3rem .7rem;background:#f1f5f9;color:#374151;border-radius:.35rem;font-size:.78rem;font-weight:500;text-decoration:none;white-space:nowrap;">
                                Traiter →
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($requests->hasPages())
        <div style="padding:.75rem 1rem;border-top:1px solid #f1f5f9;">{{ $requests->links() }}</div>
        @endif
        @endif
    </div>

</div>
@endsection
