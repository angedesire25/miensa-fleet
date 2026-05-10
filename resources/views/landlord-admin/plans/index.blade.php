@extends('landlord-admin.layouts.app')

@section('title', 'Plans & Tarifs')
@section('page-title', 'Plans & Tarifs')
@section('breadcrumb', 'Liste des plans')

@section('content')

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
    <div>
        <p style="color:#64748b;font-size:.875rem;margin:0;">Modifiez les prix et fonctionnalités de chaque plan affiché sur la page d'accueil.</p>
    </div>
    <a href="{{ route('admin.promotions.index') }}"
       style="display:inline-flex;align-items:center;gap:.5rem;padding:.55rem 1.1rem;background:rgba(251,191,36,.1);border:1px solid rgba(251,191,36,.25);border-radius:8px;font-size:.83rem;font-weight:600;color:#fbbf24;text-decoration:none;">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
        Gérer les promotions
    </a>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:1.25rem;">
    @foreach($plans as $plan)
    <div style="background:#1e293b;border:1px solid {{ $plan->is_featured ? 'rgba(59,130,246,.4)' : 'rgba(255,255,255,.07)' }};border-radius:12px;padding:1.5rem;display:flex;flex-direction:column;gap:.75rem;position:relative;">

        @if($plan->is_featured)
        <div style="position:absolute;top:-11px;left:50%;transform:translateX(-50%);background:linear-gradient(90deg,#3b82f6,#2563eb);color:white;font-size:.7rem;font-weight:700;padding:.2rem .8rem;border-radius:10px;white-space:nowrap;letter-spacing:.04em;text-transform:uppercase;">
            Le plus populaire
        </div>
        @endif

        {{-- En-tête plan --}}
        <div style="display:flex;align-items:flex-start;justify-content:space-between;">
            <div>
                <div style="font-size:1.05rem;font-weight:700;color:#f1f5f9;">{{ $plan->name }}</div>
                <div style="font-size:.8rem;color:#64748b;margin-top:.15rem;">{{ $plan->slug }}</div>
            </div>
            <span class="badge {{ $plan->is_active ? 'badge-green' : 'badge-slate' }}">
                {{ $plan->is_active ? 'Actif' : 'Masqué' }}
            </span>
        </div>

        {{-- Prix --}}
        <div style="background:#0f172a;border-radius:8px;padding:.85rem 1rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.4rem;">
                <span style="font-size:.78rem;color:#64748b;">Mensuel</span>
                <span style="font-size:1.1rem;font-weight:700;color:#f1f5f9;">
                    {{ $plan->price_monthly == 0 ? 'Gratuit' : number_format($plan->price_monthly, 0, ',', ' ').' FCFA' }}
                </span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:.78rem;color:#64748b;">Annuel</span>
                <span style="font-size:.9rem;font-weight:600;color:#93c5fd;">
                    {{ $plan->price_yearly == 0 ? '—' : number_format($plan->price_yearly, 0, ',', ' ').' FCFA' }}
                </span>
            </div>
        </div>

        {{-- Limites --}}
        <div style="font-size:.8rem;color:#94a3b8;display:flex;gap:1rem;flex-wrap:wrap;">
            <span>🚗 {{ $plan->max_vehicles >= 999 ? '∞' : $plan->max_vehicles }} véhicules</span>
            <span>👤 {{ $plan->max_users >= 999 ? '∞' : $plan->max_users }} users</span>
            <span>⏱ {{ $plan->trial_days }}j essai</span>
        </div>

        {{-- Fonctionnalités --}}
        <div style="display:flex;flex-wrap:wrap;gap:.35rem;">
            @foreach(['has_repairs'=>'Réparations','has_infractions'=>'Infractions','has_incidents'=>'Sinistres','has_inspections'=>'Visites','has_reports'=>'Rapports','has_api'=>'API'] as $key => $label)
            <span style="font-size:.72rem;padding:.15rem .55rem;border-radius:10px;
                background:{{ $plan->$key ? 'rgba(34,197,94,.12)' : 'rgba(100,116,139,.1)' }};
                color:{{ $plan->$key ? '#86efac' : '#475569' }};">
                {{ $label }}
            </span>
            @endforeach
        </div>

        {{-- Actions --}}
        <div style="display:flex;gap:.5rem;margin-top:.25rem;">
            <a href="{{ route('admin.plans.edit', $plan) }}"
               style="flex:1;text-align:center;padding:.5rem;background:rgba(59,130,246,.12);color:#93c5fd;border-radius:7px;font-size:.82rem;font-weight:600;text-decoration:none;">
                ✏️ Modifier
            </a>
            <form method="POST" action="{{ route('admin.plans.toggle', $plan) }}" style="flex:1;">
                @csrf @method('PATCH')
                <button type="submit" style="width:100%;padding:.5rem;background:{{ $plan->is_active ? 'rgba(100,116,139,.12)' : 'rgba(34,197,94,.12)' }};color:{{ $plan->is_active ? '#94a3b8' : '#86efac' }};border:none;border-radius:7px;font-size:.82rem;font-weight:600;cursor:pointer;">
                    {{ $plan->is_active ? '🙈 Masquer' : '👁 Afficher' }}
                </button>
            </form>
        </div>
    </div>
    @endforeach
</div>

@endsection
