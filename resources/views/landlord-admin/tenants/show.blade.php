@extends('landlord-admin.layouts.app')
@section('page-title', $tenant->name)
@section('breadcrumb')
    <a href="{{ route('admin.tenants.index') }}" style="color:#64748b;text-decoration:none;">Tenants</a>
    / {{ $tenant->name }}
@endsection

@section('content')

{{-- En-tête --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
    <div style="display:flex;align-items:center;gap:1rem;">
        <a href="{{ route('admin.tenants.index') }}" class="btn-sm btn-slate">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            Retour
        </a>
        @php $statusMap = ['active'=>'badge-green','trial'=>'badge-yellow','suspended'=>'badge-red','cancelled'=>'badge-slate']; @endphp
        <span class="badge {{ $statusMap[$tenant->status] ?? 'badge-slate' }}" style="font-size:.8rem;padding:.3rem .8rem;">{{ $tenant->status }}</span>
    </div>

    <div style="display:flex;gap:.5rem;">
        <a href="{{ route('admin.tenants.impersonate', $tenant) }}" class="btn-sm btn-slate" target="_blank">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            Accéder au panel
        </a>
        @if(in_array($tenant->status, ['suspended', 'trial']))
            <form method="POST" action="{{ route('admin.tenants.activate', $tenant) }}"
                  onsubmit="return confirm('{{ $tenant->status === 'trial' ? 'Convertir le trial en abonnement actif ?' : 'Réactiver ce tenant ?' }}')">
                @csrf
                <button type="submit" class="btn-sm btn-green">
                    {{ $tenant->status === 'trial' ? 'Valider l\'abonnement' : 'Réactiver' }}
                </button>
            </form>
        @endif
        @if(!in_array($tenant->status, ['suspended', 'cancelled']))
            <form method="POST" action="{{ route('admin.tenants.suspend', $tenant) }}"
                  onsubmit="return confirm('Suspendre {{ addslashes($tenant->name) }} ?')">
                @csrf
                <button type="submit" class="btn-sm btn-red">Suspendre</button>
            </form>
        @endif
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">

    {{-- Informations générales --}}
    <div class="a-card">
        <h3 style="margin:0 0 1.25rem;font-size:.88rem;font-weight:700;color:#f1f5f9;">Informations générales</h3>

        <dl style="display:grid;grid-template-columns:max-content 1fr;gap:.6rem 1.5rem;font-size:.87rem;margin:0;">
            <dt style="color:#475569;white-space:nowrap;">Société</dt>
            <dd style="margin:0;color:#f1f5f9;font-weight:500;">{{ $tenant->name }}</dd>

            <dt style="color:#475569;">Slug</dt>
            <dd style="margin:0;font-family:monospace;color:#64748b;">{{ $tenant->slug }}</dd>

            <dt style="color:#475569;">Domaine</dt>
            <dd style="margin:0;font-family:monospace;font-size:.82rem;color:#64748b;">{{ $tenant->domain }}</dd>

            <dt style="color:#475569;">Base de données</dt>
            <dd style="margin:0;font-family:monospace;font-size:.82rem;color:#64748b;">{{ $tenant->database }}</dd>

            <dt style="color:#475569;">Plan</dt>
            <dd style="margin:0;color:#e2e8f0;">{{ $tenant->plan?->name ?? '—' }}</dd>

            <dt style="color:#475569;">Créé le</dt>
            <dd style="margin:0;color:#94a3b8;">{{ $tenant->created_at->format('d/m/Y à H:i') }}</dd>

            @if($tenant->subscribed_at)
            <dt style="color:#475569;">Abonné le</dt>
            <dd style="margin:0;color:#94a3b8;">{{ $tenant->subscribed_at->format('d/m/Y') }}</dd>
            @endif

            @if($tenant->trial_ends_at)
            <dt style="color:#475569;">Fin d'essai</dt>
            <dd style="margin:0;color:{{ $tenant->trial_ends_at->isFuture() ? '#fde047' : '#fca5a5' }};">
                {{ $tenant->trial_ends_at->format('d/m/Y') }}
                @if($tenant->trial_ends_at->isPast()) <span style="font-size:.75rem;">(expiré)</span> @endif
            </dd>
            @endif

            @if($tenant->suspended_at)
            <dt style="color:#475569;">Suspendu le</dt>
            <dd style="margin:0;color:#fca5a5;">{{ $tenant->suspended_at->format('d/m/Y à H:i') }}</dd>
            @endif
        </dl>
    </div>

    {{-- Contact & Limites --}}
    <div style="display:flex;flex-direction:column;gap:1.5rem;">

        <div class="a-card">
            <h3 style="margin:0 0 1.25rem;font-size:.88rem;font-weight:700;color:#f1f5f9;">Contact</h3>
            <dl style="display:grid;grid-template-columns:max-content 1fr;gap:.6rem 1.5rem;font-size:.87rem;margin:0;">
                <dt style="color:#475569;">Nom</dt>
                <dd style="margin:0;color:#e2e8f0;">{{ $tenant->contact_name ?? '—' }}</dd>

                <dt style="color:#475569;">Email</dt>
                <dd style="margin:0;">
                    @if($tenant->contact_email)
                        <a href="mailto:{{ $tenant->contact_email }}" style="color:#3b82f6;text-decoration:none;">{{ $tenant->contact_email }}</a>
                    @else
                        <span style="color:#475569;">—</span>
                    @endif
                </dd>

                <dt style="color:#475569;">Téléphone</dt>
                <dd style="margin:0;color:#94a3b8;">{{ $tenant->contact_phone ?? '—' }}</dd>

                <dt style="color:#475569;">Pays</dt>
                <dd style="margin:0;color:#94a3b8;">{{ $tenant->country ?? '—' }}</dd>

                <dt style="color:#475569;">Fuseau horaire</dt>
                <dd style="margin:0;font-family:monospace;font-size:.82rem;color:#64748b;">{{ $tenant->timezone ?? '—' }}</dd>
            </dl>
        </div>

        <div class="a-card">
            <h3 style="margin:0 0 1rem;font-size:.88rem;font-weight:700;color:#f1f5f9;">Limites</h3>
            <div style="display:flex;gap:1.5rem;">
                <div style="text-align:center;">
                    <div style="font-size:1.6rem;font-weight:800;color:#f1f5f9;">{{ $tenant->max_vehicles ?? '∞' }}</div>
                    <div style="font-size:.75rem;color:#64748b;margin-top:.2rem;">véhicules</div>
                </div>
                <div style="text-align:center;">
                    <div style="font-size:1.6rem;font-weight:800;color:#f1f5f9;">{{ $tenant->max_users ?? '∞' }}</div>
                    <div style="font-size:.75rem;color:#64748b;margin-top:.2rem;">utilisateurs</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Changer de plan --}}
@php $allPlans = \App\Models\Plan::on('landlord')->where('is_active', true)->orderBy('sort_order')->get(); @endphp
<div class="a-card" style="margin-top:1.5rem;">
    <h3 style="margin:0 0 1rem;font-size:.88rem;font-weight:700;color:#f1f5f9;">Changer de plan</h3>
    <form method="POST" action="{{ route('admin.tenants.changePlan', $tenant) }}" style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
        @csrf
        <select name="plan_id" style="background:#0f172a;border:1px solid rgba(255,255,255,.1);border-radius:8px;color:#e2e8f0;padding:.5rem 1rem;font-size:.87rem;outline:none;font-family:inherit;">
            @foreach($allPlans as $p)
                <option value="{{ $p->id }}" {{ $tenant->plan_id === $p->id ? 'selected' : '' }}>
                    {{ $p->name }} — {{ number_format($p->price_monthly, 0, ',', ' ') }} FCFA/mois
                </option>
            @endforeach
        </select>
        <button type="submit" class="btn-sm btn-primary">Appliquer</button>
    </form>
</div>

{{-- Abonnements --}}
<div class="a-card" style="margin-top:1.5rem;">
    <h3 style="margin:0 0 1.25rem;font-size:.88rem;font-weight:700;color:#f1f5f9;">Historique des abonnements</h3>

    @if($tenant->subscriptions->isEmpty())
        <p style="color:#475569;font-size:.87rem;margin:0;">Aucun abonnement enregistré.</p>
    @else
        <table class="a-table">
            <thead>
                <tr>
                    <th>Plan</th>
                    <th>Statut</th>
                    <th>Début</th>
                    <th>Fin</th>
                    <th>Montant</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tenant->subscriptions->sortByDesc('starts_at') as $sub)
                @php
                    $subStatusMap = ['active'=>'badge-green','past_due'=>'badge-yellow','cancelled'=>'badge-slate','expired'=>'badge-red'];
                @endphp
                <tr>
                    <td style="color:#e2e8f0;">{{ $sub->plan?->name ?? '—' }}</td>
                    <td><span class="badge {{ $subStatusMap[$sub->status] ?? 'badge-slate' }}">{{ $sub->status }}</span></td>
                    <td>{{ $sub->starts_at?->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $sub->ends_at?->format('d/m/Y') ?? '—' }}</td>
                    <td style="font-family:monospace;font-size:.83rem;">{{ $sub->price ? number_format($sub->price, 0, ',', ' ').' FCFA' : '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

@endsection
