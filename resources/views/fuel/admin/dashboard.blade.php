@extends('layouts.dashboard')

@section('title', 'Gestion carburant')
@section('page-title', 'Carburant')
@section('breadcrumb', 'Tableau de bord')

@section('content')
<div class="page-content">

    {{-- ── En-tête + sélection période ────────────────────────────────── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;">
        <div>
            <h1 style="font-size:1.35rem;font-weight:700;color:#0f172a;margin:0;">Tableau de bord Carburant</h1>
            <p style="color:#64748b;font-size:.875rem;margin:.2rem 0 0;">
                Période : <strong>{{ \Carbon\Carbon::parse($from)->format('d/m/Y') }}</strong> → <strong>{{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}</strong>
            </p>
        </div>
        <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;">
            <form method="GET">
                <select name="period" onchange="this.form.submit()"
                        style="padding:.45rem .75rem;border:1px solid #e2e8f0;border-radius:.45rem;font-size:.85rem;color:#374151;background:#fff;">
                    <option value="month"   {{ $period==='month'   ?'selected':'' }}>Ce mois</option>
                    <option value="quarter" {{ $period==='quarter' ?'selected':'' }}>Ce trimestre</option>
                    <option value="year"    {{ $period==='year'    ?'selected':'' }}>Cette année</option>
                </select>
            </form>
            @can('fuel.record')
            <a href="{{ route('fuel.admin.transaction-create') }}"
               style="display:inline-flex;align-items:center;gap:.5rem;padding:.55rem 1.1rem;background:linear-gradient(135deg,#10b981,#059669);color:#fff;border-radius:.5rem;font-size:.85rem;font-weight:600;text-decoration:none;">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Enregistrer un plein
            </a>
            @endcan
        </div>
    </div>

    {{-- ── Alertes urgentes --}}
    @if($urgentRequests->isNotEmpty())
    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:.65rem;padding:.9rem 1.1rem;margin-bottom:1.25rem;display:flex;align-items:flex-start;gap:.75rem;">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:.1rem;color:#f59e0b;">
            <path d="M12 9v4M12 17h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <div style="flex:1;">
            <div style="font-size:.85rem;font-weight:700;color:#92400e;margin-bottom:.4rem;">
                {{ $urgentRequests->count() }} demande(s) urgente(s) en attente
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:.5rem;">
                @foreach($urgentRequests as $ur)
                <a href="{{ route('fuel.admin.request-show', $ur) }}"
                   style="padding:.25rem .65rem;background:#fff;border:1px solid #fde68a;border-radius:.35rem;font-size:.78rem;color:#92400e;text-decoration:none;font-weight:600;">
                    {{ $ur->reference }} — {{ $ur->vehicle?->plate ?? '?' }}
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- ── KPIs ──────────────────────────────────────────────────────────── --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:1.5rem;">
        @php
        $kpis = [
            ['label'=>'Demandes en attente', 'value'=>$stats['pending_requests'],   'color'=>'#f59e0b', 'suffix'=>'',  'link'=>route('fuel.admin.requests', ['status'=>'pending'])],
            ['label'=>'Demandes approuvées', 'value'=>$stats['approved_requests'],  'color'=>'#10b981', 'suffix'=>'',  'link'=>route('fuel.admin.requests', ['status'=>'approved'])],
            ['label'=>'Pleins réalisés',     'value'=>$stats['transactions_count'], 'color'=>'#3b82f6', 'suffix'=>'',  'link'=>route('fuel.admin.transactions')],
            ['label'=>'Litres distribués',   'value'=>number_format($stats['total_liters'],0,',',' '), 'color'=>'#8b5cf6','suffix'=>' L', 'link'=>null],
            ['label'=>'Coût total',          'value'=>number_format($stats['total_cost'],0,',',' '),   'color'=>'#ef4444','suffix'=>' FCFA','link'=>null],
            ['label'=>'Dont carte',          'value'=>number_format($stats['card_cost'],0,',',' '),    'color'=>'#06b6d4','suffix'=>' FCFA','link'=>null],
        ];
        @endphp
        @foreach($kpis as $kpi)
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.1rem 1.25rem;{{ $kpi['link'] ? 'cursor:pointer;' : '' }}"
             {{ $kpi['link'] ? 'onclick="location.href=\''.e($kpi['link']).'\'"' : '' }}>
            <div style="font-size:.72rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.4rem;">{{ $kpi['label'] }}</div>
            <div style="font-size:1.5rem;font-weight:800;color:{{ $kpi['color'] }};">
                {{ $kpi['value'] }}<span style="font-size:.9rem;">{{ $kpi['suffix'] }}</span>
            </div>
        </div>
        @endforeach
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">

        {{-- Top véhicules --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
            <h2 style="font-size:.88rem;font-weight:700;color:#374151;margin:0 0 1rem;text-transform:uppercase;letter-spacing:.04em;">Top consommateurs</h2>
            @forelse($topVehicles as $tv)
            @php $veh = $tv->vehicle; @endphp
            <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.75rem;">
                <div style="width:34px;height:34px;background:#f1f5f9;border-radius:.4rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M3 17h2l1-3h12l1 3h2" stroke="#64748b" stroke-width="1.8" stroke-linecap="round"/><circle cx="7.5" cy="18.5" r="1.5" stroke="#64748b" stroke-width="1.5"/><circle cx="16.5" cy="18.5" r="1.5" stroke="#64748b" stroke-width="1.5"/><path d="M6.5 9l1-3h9l1 3" stroke="#64748b" stroke-width="1.5"/></svg>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:.85rem;font-weight:600;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $veh ? $veh->brand.' '.$veh->model : 'Véhicule #'.$tv->vehicle_id }}
                    </div>
                    <div style="font-size:.75rem;color:#94a3b8;">{{ $veh?->plate ?? '—' }} · {{ $tv->fills }} plein(s)</div>
                </div>
                <div style="text-align:right;flex-shrink:0;">
                    <div style="font-size:.9rem;font-weight:700;color:#8b5cf6;">{{ number_format($tv->total_liters, 0, ',', ' ') }} L</div>
                    <div style="font-size:.72rem;color:#94a3b8;">{{ number_format($tv->total_cost, 0, ',', ' ') }} FCFA</div>
                </div>
            </div>
            @empty
            <p style="color:#94a3b8;font-size:.85rem;text-align:center;padding:.5rem 0;">Aucune transaction sur la période.</p>
            @endforelse
        </div>

        {{-- Dernières transactions --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
                <h2 style="font-size:.88rem;font-weight:700;color:#374151;margin:0;text-transform:uppercase;letter-spacing:.04em;">Derniers pleins</h2>
                <a href="{{ route('fuel.admin.transactions') }}" style="font-size:.78rem;color:#3b82f6;text-decoration:none;">Tout voir →</a>
            </div>
            @forelse($recentTransactions as $tx)
            <div style="display:flex;align-items:center;gap:.65rem;padding:.5rem 0;border-bottom:1px solid #f1f5f9;">
                <div style="flex:1;min-width:0;">
                    <div style="font-size:.82rem;font-weight:600;color:#0f172a;">
                        {{ $tx->vehicle?->plate ?? '—' }}
                        <span style="color:#94a3b8;font-weight:400;">· {{ $tx->reference }}</span>
                    </div>
                    <div style="font-size:.72rem;color:#94a3b8;">{{ $tx->fueled_at->format('d/m/Y') }} · {{ $tx->station_label }}</div>
                </div>
                <div style="text-align:right;flex-shrink:0;">
                    <div style="font-size:.85rem;font-weight:700;color:#0f172a;">{{ number_format($tx->liters, 0, ',', ' ') }} L</div>
                    <div style="font-size:.72rem;color:#64748b;">{{ number_format($tx->total_amount, 0, ',', ' ') }} FCFA</div>
                </div>
            </div>
            @empty
            <p style="color:#94a3b8;font-size:.85rem;text-align:center;padding:.5rem 0;">Aucune transaction récente.</p>
            @endforelse
            @if($recentTransactions->isNotEmpty())
            <a href="{{ route('fuel.admin.transactions') }}" style="display:block;text-align:center;margin-top:.75rem;font-size:.8rem;color:#3b82f6;text-decoration:none;">
                Voir toutes les transactions →
            </a>
            @endif
        </div>

    </div>

    {{-- ── Accès rapide ──────────────────────────────────────────────────── --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:.75rem;margin-top:1.25rem;">
        <a href="{{ route('fuel.admin.requests') }}"
           style="display:flex;align-items:center;gap:.75rem;padding:1rem 1.25rem;background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;text-decoration:none;transition:border-color .15s;"
           onmouseover="this.style.borderColor='#10b981'" onmouseout="this.style.borderColor='#e2e8f0'">
            <div style="width:38px;height:38px;background:rgba(16,185,129,.1);border-radius:.5rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4" stroke="#10b981" stroke-width="2" stroke-linecap="round"/><path d="M20 12c0 4.418-3.582 8-8 8s-8-3.582-8-8 3.582-8 8-8 8 3.582 8 8z" stroke="#10b981" stroke-width="1.8"/></svg>
            </div>
            <div>
                <div style="font-size:.88rem;font-weight:700;color:#0f172a;">Gérer les demandes</div>
                <div style="font-size:.75rem;color:#64748b;">Approuver / rejeter</div>
            </div>
        </a>
        <a href="{{ route('fuel.admin.transactions') }}"
           style="display:flex;align-items:center;gap:.75rem;padding:1rem 1.25rem;background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;text-decoration:none;transition:border-color .15s;"
           onmouseover="this.style.borderColor='#3b82f6'" onmouseout="this.style.borderColor='#e2e8f0'">
            <div style="width:38px;height:38px;background:rgba(59,130,246,.1);border-radius:.5rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" stroke="#3b82f6" stroke-width="2" stroke-linecap="round"/></svg>
            </div>
            <div>
                <div style="font-size:.88rem;font-weight:700;color:#0f172a;">Transactions</div>
                <div style="font-size:.75rem;color:#64748b;">Historique des pleins</div>
            </div>
        </a>
        @can('fuel.manage_stations')
        <a href="{{ route('fuel.admin.stations') }}"
           style="display:flex;align-items:center;gap:.75rem;padding:1rem 1.25rem;background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;text-decoration:none;transition:border-color .15s;"
           onmouseover="this.style.borderColor='#8b5cf6'" onmouseout="this.style.borderColor='#e2e8f0'">
            <div style="width:38px;height:38px;background:rgba(139,92,246,.1);border-radius:.5rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke="#8b5cf6" stroke-width="1.8"/><polyline points="9,22 9,12 15,12 15,22" stroke="#8b5cf6" stroke-width="1.8"/></svg>
            </div>
            <div>
                <div style="font-size:.88rem;font-weight:700;color:#0f172a;">Stations</div>
                <div style="font-size:.75rem;color:#64748b;">Gérer les stations</div>
            </div>
        </a>
        @endcan
        @can('fuel.record')
        <a href="{{ route('fuel.admin.transaction-create') }}"
           style="display:flex;align-items:center;gap:.75rem;padding:1rem 1.25rem;background:linear-gradient(135deg,rgba(16,185,129,.06),rgba(16,185,129,.02));border:1px solid rgba(16,185,129,.25);border-radius:.75rem;text-decoration:none;transition:border-color .15s;"
           onmouseover="this.style.borderColor='#10b981'" onmouseout="this.style.borderColor='rgba(16,185,129,.25)'">
            <div style="width:38px;height:38px;background:rgba(16,185,129,.15);border-radius:.5rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke="#10b981" stroke-width="2" stroke-linecap="round"/></svg>
            </div>
            <div>
                <div style="font-size:.88rem;font-weight:700;color:#0f172a;">Nouveau plein</div>
                <div style="font-size:.75rem;color:#64748b;">Saisie directe</div>
            </div>
        </a>
        @endcan
    </div>

</div>
@endsection
