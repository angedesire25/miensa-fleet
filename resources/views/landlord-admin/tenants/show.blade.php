@extends('landlord-admin.layouts.app')
@section('page-title', $tenant->name)
@section('breadcrumb')
    <a href="{{ route('admin.tenants.index') }}" style="color:#64748b;text-decoration:none;">Tenants</a>
    / {{ $tenant->name }}
@endsection

@section('content')

{{-- ── En-tête ──────────────────────────────────────────────────────────── --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
    <div style="display:flex;align-items:center;gap:1rem;">
        <a href="{{ route('admin.tenants.index') }}" class="btn-sm btn-slate">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            Retour
        </a>
        @php $statusMap = ['active'=>'badge-green','trial'=>'badge-yellow','suspended'=>'badge-red','cancelled'=>'badge-slate']; @endphp
        <span class="badge {{ $statusMap[$tenant->status] ?? 'badge-slate' }}" style="font-size:.8rem;padding:.3rem .8rem;">{{ $tenant->status }}</span>
    </div>

    <div style="display:flex;gap:.5rem;align-items:center;">
        <a href="{{ route('admin.tenants.impersonate', $tenant) }}" class="btn-sm btn-slate" target="_blank">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            Accéder au panel
        </a>

        {{-- Valider (trial → active) ou Réactiver (suspended → active) --}}
        @if(in_array($tenant->status, ['suspended', 'trial']))
            <form id="activate-form" method="POST" action="{{ route('admin.tenants.activate', $tenant) }}">@csrf</form>
            <button type="button" class="btn-sm btn-green" onclick="confirmActivate()">
                {{ $tenant->status === 'trial' ? 'Valider l\'abonnement' : 'Réactiver' }}
            </button>
        @endif

        {{-- Suspendre --}}
        @if(!in_array($tenant->status, ['suspended', 'cancelled']))
            <form id="suspend-form" method="POST" action="{{ route('admin.tenants.suspend', $tenant) }}">
                @csrf
                <input type="hidden" name="reason" id="suspend-reason">
            </form>
            <button type="button" class="btn-sm btn-red" onclick="confirmSuspend()">Suspendre</button>
        @endif

        {{-- Réinitialiser les accès --}}
        <form id="reset-form" method="POST" action="{{ route('admin.tenants.resetAccess', $tenant) }}">@csrf</form>
        <button type="button" class="btn-sm btn-slate" onclick="confirmReset()"
                title="Générer un nouveau mot de passe pour l'admin du panel">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
            Réinitialiser accès
        </button>

        {{-- Supprimer --}}
        @if($tenant->status !== 'cancelled')
            <form id="delete-form" method="POST" action="{{ route('admin.tenants.destroy', $tenant) }}">
                @csrf
                @method('DELETE')
                <input type="hidden" name="confirm_slug" id="delete-slug">
                <input type="hidden" name="drop_database" id="delete-drop" value="0">
            </form>
            <button type="button" class="btn-sm"
                    style="background:rgba(239,68,68,.12);color:#fca5a5;border:1px solid rgba(239,68,68,.25);"
                    onclick="confirmDelete()">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                Supprimer
            </button>
        @endif
    </div>
</div>

{{-- Accès réinitialisés --}}
@if(session('access_reset'))
@php $reset = session('access_reset'); @endphp
<div style="background:rgba(59,130,246,.08);border:1px solid rgba(59,130,246,.25);border-radius:10px;padding:1.25rem 1.5rem;margin-bottom:1.5rem;">
    <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.75rem;">
        <svg width="16" height="16" fill="none" stroke="#60a5fa" stroke-width="2" viewBox="0 0 24 24"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
        <span style="font-size:.85rem;font-weight:700;color:#60a5fa;">Accès réinitialisés avec succès</span>
    </div>
    <div style="display:grid;grid-template-columns:max-content 1fr;gap:.4rem 1.25rem;font-size:.85rem;">
        <span style="color:#64748b;">Email</span>
        <code style="color:#f1f5f9;font-family:monospace;">{{ $reset['email'] }}</code>
        <span style="color:#64748b;">Nouveau mot de passe</span>
        <div style="display:flex;align-items:center;gap:.5rem;">
            <code id="reset-pwd" style="color:#fde047;font-family:monospace;font-size:.95rem;letter-spacing:.05em;">{{ $reset['password'] }}</code>
            <button onclick="navigator.clipboard.writeText('{{ $reset['password'] }}');this.textContent='✓ Copié';"
                    style="background:rgba(255,255,255,.07);border:none;color:#94a3b8;font-size:.75rem;padding:.2rem .5rem;border-radius:4px;cursor:pointer;font-family:inherit;">
                Copier
            </button>
        </div>
        <span style="color:#64748b;">Panel</span>
        <a href="http://{{ $reset['domain'] }}/login" target="_blank" style="color:#3b82f6;text-decoration:none;font-size:.83rem;">
            http://{{ $reset['domain'] }}/login
        </a>
    </div>
    <p style="font-size:.75rem;color:#475569;margin:.75rem 0 0;">
        Notez ces informations — elles ne seront plus affichées. Un email a été envoyé à l'administrateur si le service mail est configuré.
    </p>
</div>
@endif

{{-- Bannière suspension --}}
@if($tenant->status === 'suspended' && $tenant->suspension_reason)
<div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);border-radius:10px;padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;gap:.75rem;align-items:flex-start;">
    <svg width="16" height="16" fill="none" stroke="#fca5a5" stroke-width="2" viewBox="0 0 24 24" style="margin-top:.15rem;flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <div>
        <div style="font-size:.82rem;font-weight:600;color:#fca5a5;margin-bottom:.2rem;">
            Suspendu le {{ $tenant->suspended_at?->format('d/m/Y à H:i') }}
        </div>
        <div style="font-size:.85rem;color:#94a3b8;">{{ $tenant->suspension_reason }}</div>
    </div>
</div>
@endif

{{-- ── Corps ──────────────────────────────────────────────────────────────── --}}
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
                @if($tenant->trial_ends_at->isPast())<span style="font-size:.75rem;"> (expiré)</span>@endif
            </dd>
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
            <div style="display:flex;gap:2rem;">
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
                <tr><th>Plan</th><th>Statut</th><th>Début</th><th>Fin</th><th>Montant</th></tr>
            </thead>
            <tbody>
                @foreach($tenant->subscriptions->sortByDesc('starts_at') as $sub)
                @php $subMap = ['active'=>'badge-green','past_due'=>'badge-yellow','cancelled'=>'badge-slate','expired'=>'badge-red']; @endphp
                <tr>
                    <td style="color:#e2e8f0;">{{ $sub->plan?->name ?? '—' }}</td>
                    <td><span class="badge {{ $subMap[$sub->status] ?? 'badge-slate' }}">{{ $sub->status }}</span></td>
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

@push('scripts')
<script>
const TENANT_NAME   = @json($tenant->name);
const TENANT_SLUG   = @json($tenant->slug);
const TENANT_DB     = @json($tenant->database);
const TENANT_STATUS = @json($tenant->status);

function confirmActivate() {
    const label = TENANT_STATUS === 'trial' ? "Valider l'abonnement" : 'Réactiver';
    const text  = TENANT_STATUS === 'trial'
        ? `Le compte passera de <b>trial → actif</b> et <b>${TENANT_NAME}</b> sera facturé dès maintenant.`
        : `Le compte de <b>${TENANT_NAME}</b> sera réactivé immédiatement.`;
    Swal.fire({
        title: label + ' ?',
        html: text,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#22c55e',
        cancelButtonText: 'Annuler',
        confirmButtonText: label,
    }).then(r => { if (r.isConfirmed) document.getElementById('activate-form').submit(); });
}

function confirmSuspend() {
    Swal.fire({
        title: `Suspendre ${TENANT_NAME} ?`,
        html: `Les utilisateurs ne pourront plus se connecter. Saisissez le motif&nbsp;:`,
        input: 'textarea',
        inputPlaceholder: 'Motif de la suspension…',
        inputAttributes: { maxlength: 500, rows: 3 },
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonText: 'Annuler',
        confirmButtonText: 'Confirmer la suspension',
        preConfirm: (val) => {
            if (!val || !val.trim()) {
                Swal.showValidationMessage('Le motif de suspension est obligatoire.');
                return false;
            }
            return val.trim();
        },
    }).then(r => {
        if (r.isConfirmed) {
            document.getElementById('suspend-reason').value = r.value;
            document.getElementById('suspend-form').submit();
        }
    });
}

function confirmReset() {
    Swal.fire({
        title: 'Réinitialiser les accès ?',
        html: `
            <p style="color:#94a3b8;line-height:1.6;">
                Un nouveau mot de passe temporaire sera généré pour le compte
                <strong style="color:#f1f5f9;">administrateur</strong> de <strong style="color:#f1f5f9;">${TENANT_NAME}</strong>.
            </p>
            <p style="color:#fde047;font-size:.82rem;margin-top:.75rem;">
                ⚠ L'ancien mot de passe sera immédiatement invalidé.
            </p>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3b82f6',
        cancelButtonText: 'Annuler',
        confirmButtonText: 'Réinitialiser',
    }).then(r => { if (r.isConfirmed) document.getElementById('reset-form').submit(); });
}

function confirmDelete() {
    Swal.fire({
        title: 'Supprimer définitivement ?',
        html: `
            <p style="color:#94a3b8;margin:.5rem 0 1rem;">
                Tapez <code style="background:#0f172a;padding:.15rem .4rem;border-radius:4px;color:#fde047;font-family:monospace;">${TENANT_SLUG}</code>
                pour confirmer la suppression de <strong style="color:#f1f5f9;">${TENANT_NAME}</strong>.
            </p>
            <input id="swal-slug" class="swal2-input" placeholder="${TENANT_SLUG}" autocomplete="off" spellcheck="false">
            <label style="display:flex;align-items:center;gap:.6rem;margin-top:1.1rem;cursor:pointer;text-align:left;">
                <input type="checkbox" id="swal-drop" style="accent-color:#ef4444;width:16px;height:16px;flex-shrink:0;">
                <span style="font-size:.83rem;color:#fca5a5;">
                    Supprimer aussi la base de données
                    <code style="font-size:.77rem;background:#0f172a;padding:.1rem .3rem;border-radius:3px;">${TENANT_DB}</code>
                </span>
            </label>
            <p style="font-size:.75rem;color:#64748b;margin:.6rem 0 0;text-align:left;">
                ⚠ La suppression de la base efface toutes les données client de façon permanente.
            </p>`,
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonText: 'Annuler',
        confirmButtonText: 'Supprimer définitivement',
        focusConfirm: false,
        preConfirm: () => {
            const typed = document.getElementById('swal-slug').value;
            const drop  = document.getElementById('swal-drop').checked;
            if (typed !== TENANT_SLUG) {
                Swal.showValidationMessage('Le sous-domaine saisi ne correspond pas. Suppression annulée.');
                return false;
            }
            return { slug: typed, drop };
        },
    }).then(r => {
        if (r.isConfirmed) {
            document.getElementById('delete-slug').value = r.value.slug;
            document.getElementById('delete-drop').value = r.value.drop ? '1' : '0';
            document.getElementById('delete-form').submit();
        }
    });
}
</script>
@endpush
