@extends('landlord-admin.layouts.app')
@section('page-title', 'Tableau de bord')

@section('content')
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:2rem;">
    <div class="a-card">
        <div class="a-card-title">Tenants total</div>
        <div class="a-card-value">{{ $stats['tenants_total'] }}</div>
    </div>
    <div class="a-card">
        <div class="a-card-title">Actifs</div>
        <div class="a-card-value" style="color:#86efac;">{{ $stats['tenants_active'] }}</div>
    </div>
    <div class="a-card">
        <div class="a-card-title">En essai</div>
        <div class="a-card-value" style="color:#fde047;">{{ $stats['tenants_trial'] }}</div>
    </div>
    <div class="a-card">
        <div class="a-card-title">MRR</div>
        <div class="a-card-value">{{ number_format($stats['mrr'], 0, ',', ' ') }}</div>
        <div class="a-card-sub">FCFA / mois</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">

    {{-- Tenants récents --}}
    <div class="a-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
            <h3 style="margin:0;font-size:.9rem;font-weight:700;color:#f1f5f9;">Tenants récents</h3>
            <a href="{{ route('admin.tenants.index') }}" style="font-size:.8rem;color:#3b82f6;text-decoration:none;">Voir tout →</a>
        </div>
        <table class="a-table">
            <thead>
                <tr>
                    <th>Société</th>
                    <th>Plan</th>
                    <th>Statut</th>
                    <th>Créé</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentTenants as $tenant)
                <tr>
                    <td>
                        <a href="{{ route('admin.tenants.show', $tenant) }}" style="color:#f1f5f9;text-decoration:none;font-weight:500;">{{ $tenant->name }}</a>
                        <div style="font-size:.75rem;color:#475569;">{{ $tenant->slug }}</div>
                    </td>
                    <td>{{ $tenant->plan?->name ?? '—' }}</td>
                    <td>
                        @php $statusMap = ['active'=>'badge-green','trial'=>'badge-yellow','suspended'=>'badge-red','cancelled'=>'badge-slate']; @endphp
                        <span class="badge {{ $statusMap[$tenant->status] ?? 'badge-slate' }}">{{ $tenant->status }}</span>
                    </td>
                    <td>{{ $tenant->created_at->format('d/m/Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Plans --}}
    <div class="a-card">
        <h3 style="margin:0 0 1.25rem;font-size:.9rem;font-weight:700;color:#f1f5f9;">Répartition par plan</h3>
        @foreach($plans as $plan)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.6rem 0;border-bottom:1px solid rgba(255,255,255,.05);">
            <div>
                <div style="font-size:.87rem;color:#e2e8f0;font-weight:500;">{{ $plan->name }}</div>
                <div style="font-size:.75rem;color:#475569;">{{ number_format($plan->price_monthly, 0, ',', ' ') }} FCFA/mois</div>
            </div>
            <span style="font-size:1.2rem;font-weight:800;color:#f1f5f9;">{{ $plan->tenants_count }}</span>
        </div>
        @endforeach
    </div>

</div>
@endsection
