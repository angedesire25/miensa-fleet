@extends('landlord-admin.layouts.app')
@section('page-title', 'Tenants')

@push('styles')
<style>
    .filter-bar { display:flex; gap:.75rem; align-items:center; margin-bottom:1.5rem; }
    .filter-input { background:#1e293b; border:1px solid rgba(255,255,255,.1); border-radius:8px; color:#f1f5f9; padding:.55rem 1rem; font-size:.87rem; outline:none; font-family:inherit; }
    .filter-input:focus { border-color:#3b82f6; }
    .filter-select { background:#1e293b; border:1px solid rgba(255,255,255,.1); border-radius:8px; color:#94a3b8; padding:.55rem 1rem; font-size:.87rem; outline:none; font-family:inherit; }
</style>
@endpush

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#f1f5f9;">
        {{ $tenants->total() }} tenant{{ $tenants->total() > 1 ? 's' : '' }}
    </h2>
    <a href="{{ route('admin.tenants.create') }}" class="btn-sm btn-primary" style="padding:.45rem 1rem;font-size:.85rem;">
        + Créer une société
    </a>
</div>

{{-- Filtres --}}
<form method="GET" class="filter-bar">
    <input type="text" name="q" class="filter-input" placeholder="Rechercher…" value="{{ request('q') }}" style="flex:1;max-width:320px;">
    <select name="status" class="filter-select" onchange="this.form.submit()">
        <option value="">Tous les statuts</option>
        <option value="active"    {{ request('status') === 'active'    ? 'selected' : '' }}>Actif</option>
        <option value="trial"     {{ request('status') === 'trial'     ? 'selected' : '' }}>Essai</option>
        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspendu</option>
        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Résilié</option>
    </select>
    <button type="submit" class="btn-sm btn-primary">Filtrer</button>
</form>

<div class="a-card" style="padding:0;overflow:hidden;">
    <table class="a-table">
        <thead>
            <tr>
                <th>Société</th>
                <th>Domaine</th>
                <th>Plan</th>
                <th>Statut</th>
                <th>Créé le</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tenants as $tenant)
            @php $statusMap = ['active'=>'badge-green','trial'=>'badge-yellow','suspended'=>'badge-red','cancelled'=>'badge-slate']; @endphp
            <tr>
                <td>
                    <div style="font-weight:500;color:#f1f5f9;">{{ $tenant->name }}</div>
                    <div style="font-size:.75rem;color:#475569;">{{ $tenant->contact_email ?? '—' }}</div>
                </td>
                <td style="font-size:.8rem;font-family:monospace;color:#64748b;">{{ $tenant->domain }}</td>
                <td>{{ $tenant->plan?->name ?? '—' }}</td>
                <td><span class="badge {{ $statusMap[$tenant->status] ?? 'badge-slate' }}">{{ $tenant->status }}</span></td>
                <td>{{ $tenant->created_at->format('d/m/Y') }}</td>
                <td>
                    <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
                        <a href="{{ route('admin.tenants.show', $tenant) }}" class="btn-sm btn-slate">Voir</a>
                        <a href="{{ route('admin.tenants.impersonate', $tenant) }}" class="btn-sm btn-slate" target="_blank" title="Accéder au panel">
                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        </a>
                        @if(in_array($tenant->status, ['suspended', 'trial']))
                            <form method="POST" action="{{ route('admin.tenants.activate', $tenant) }}">
                                @csrf
                                <button type="submit" class="btn-sm btn-green" title="{{ $tenant->status === 'trial' ? 'Valider l\'abonnement' : 'Réactiver' }}">
                                    {{ $tenant->status === 'trial' ? 'Valider' : 'Activer' }}
                                </button>
                            </form>
                        @endif
                        @if(!in_array($tenant->status, ['suspended', 'cancelled']))
                            <form method="POST" action="{{ route('admin.tenants.suspend', $tenant) }}" onsubmit="return confirm('Suspendre {{ addslashes($tenant->name) }} ?')">
                                @csrf
                                <button type="submit" class="btn-sm btn-red">Suspendre</button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;padding:2rem;color:#475569;">Aucun tenant trouvé.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($tenants->hasPages())
<div style="margin-top:1rem;display:flex;justify-content:flex-end;">
    {{ $tenants->links() }}
</div>
@endif
@endsection
