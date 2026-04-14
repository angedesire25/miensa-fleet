@extends('landlord-admin.layouts.app')
@section('page-title', 'Nouvelle société')
@section('breadcrumb')
    <a href="{{ route('admin.tenants.index') }}" style="color:#64748b;text-decoration:none;">Tenants</a>
    / Créer
@endsection

@push('styles')
<style>
    .form-section { margin-bottom:2rem; }
    .form-section-title {
        font-size:.75rem; font-weight:700; color:#475569;
        text-transform:uppercase; letter-spacing:.08em;
        margin:0 0 1rem; padding-bottom:.5rem;
        border-bottom:1px solid rgba(255,255,255,.06);
    }
    .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
    .form-group { display:flex; flex-direction:column; gap:.3rem; }
    .form-group.full { grid-column:1/-1; }
    .form-label { font-size:.8rem; font-weight:600; color:#94a3b8; }
    .form-label span { color:#ef4444; }
    .form-control {
        background:#0f172a; border:1px solid rgba(255,255,255,.1);
        border-radius:8px; color:#f1f5f9; padding:.65rem 1rem;
        font-size:.88rem; outline:none; font-family:inherit; width:100%;
        transition:border-color .15s;
    }
    .form-control:focus { border-color:#3b82f6; }
    .form-control.is-invalid { border-color:#ef4444; }
    .invalid-feedback { font-size:.78rem; color:#fca5a5; }
    .slug-preview {
        font-size:.78rem; color:#64748b; margin-top:.25rem;
        font-family:monospace;
    }
    .slug-preview strong { color:#3b82f6; }

    .plan-cards { display:grid; grid-template-columns:repeat(3, 1fr); gap:1rem; margin-top:.5rem; }
    .plan-card {
        background:#0f172a; border:2px solid rgba(255,255,255,.08);
        border-radius:10px; padding:1rem; cursor:pointer; transition:border-color .15s;
        position:relative;
    }
    .plan-card:has(input:checked) { border-color:#3b82f6; }
    .plan-card input[type=radio] { position:absolute; opacity:0; width:0; height:0; }
    .plan-card-name { font-size:.9rem; font-weight:700; color:#f1f5f9; margin-bottom:.25rem; }
    .plan-card-price { font-size:.8rem; color:#64748b; }
    .plan-card-limits { font-size:.75rem; color:#94a3b8; margin-top:.5rem; }
    .plan-card-trial { font-size:.72rem; color:#fde047; margin-top:.3rem; }

    .btn-create {
        background:#3b82f6; color:white; border:none; border-radius:8px;
        padding:.75rem 2rem; font-size:.95rem; font-weight:700; cursor:pointer;
        font-family:inherit; transition:background .15s;
    }
    .btn-create:hover { background:#2563eb; }
    .btn-create:disabled { opacity:.6; cursor:not-allowed; }
</style>
@endpush

@section('content')

<div style="max-width:780px;">

    <a href="{{ route('admin.tenants.index') }}" class="btn-sm btn-slate" style="margin-bottom:1.5rem;display:inline-flex;">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
        Retour à la liste
    </a>

    <form method="POST" action="{{ route('admin.tenants.store') }}" id="createForm">
        @csrf

        {{-- Société --}}
        <div class="a-card form-section">
            <p class="form-section-title">Informations de la société</p>
            <div class="form-grid">
                <div class="form-group full">
                    <label class="form-label" for="name">Nom de la société <span>*</span></label>
                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" placeholder="Ex : Géomatos CI" required autofocus>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="slug">Sous-domaine <span>*</span></label>
                    <input type="text" id="slug" name="slug" class="form-control @error('slug') is-invalid @enderror"
                           value="{{ old('slug') }}" placeholder="ex: geomatos"
                           pattern="[a-z0-9][a-z0-9\-]*[a-z0-9]" required>
                    <div class="slug-preview" id="slugPreview">
                        Panel : <strong id="slugDomain">votre-slug</strong>.{{ config('multitenancy.landlord_domain') }}
                    </div>
                    @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="country">Pays</label>
                    <input type="text" id="country" name="country" class="form-control"
                           value="{{ old('country', 'Côte d\'Ivoire') }}" placeholder="Côte d'Ivoire">
                </div>

                <div class="form-group">
                    <label class="form-label" for="timezone">Fuseau horaire</label>
                    <input type="text" id="timezone" name="timezone" class="form-control"
                           value="{{ old('timezone', 'Africa/Abidjan') }}" placeholder="Africa/Abidjan">
                </div>
            </div>
        </div>

        {{-- Contact --}}
        <div class="a-card form-section">
            <p class="form-section-title">Administrateur du panel</p>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="contact_name">Nom complet <span>*</span></label>
                    <input type="text" id="contact_name" name="contact_name"
                           class="form-control @error('contact_name') is-invalid @enderror"
                           value="{{ old('contact_name') }}" placeholder="Prénom Nom" required>
                    @error('contact_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="contact_email">Email <span>*</span></label>
                    <input type="email" id="contact_email" name="contact_email"
                           class="form-control @error('contact_email') is-invalid @enderror"
                           value="{{ old('contact_email') }}" placeholder="admin@societe.ci" required>
                    @error('contact_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="contact_phone">Téléphone</label>
                    <input type="text" id="contact_phone" name="contact_phone" class="form-control"
                           value="{{ old('contact_phone') }}" placeholder="+225 07 00 00 00 00">
                </div>
            </div>
            <p style="font-size:.78rem;color:#475569;margin:1rem 0 0;">
                Un mot de passe temporaire sera généré automatiquement et affiché après la création.
                L'administrateur devra le changer à la première connexion.
            </p>
        </div>

        {{-- Plan --}}
        <div class="a-card form-section">
            <p class="form-section-title">Plan d'abonnement <span style="color:#ef4444;">*</span></p>
            @error('plan_id') <div class="a-alert-error">{{ $message }}</div> @enderror

            <div class="plan-cards">
                @foreach($plans as $plan)
                <label class="plan-card">
                    <input type="radio" name="plan_id" value="{{ $plan->id }}"
                           {{ old('plan_id', $plans->first()?->id) == $plan->id ? 'checked' : '' }}
                           required>
                    <div class="plan-card-name">{{ $plan->name }}</div>
                    <div class="plan-card-price">
                        @if($plan->price_monthly > 0)
                            {{ number_format($plan->price_monthly, 0, ',', ' ') }} FCFA / mois
                        @else
                            Gratuit
                        @endif
                    </div>
                    <div class="plan-card-limits">
                        {{ $plan->max_vehicles ?? '∞' }} véhicules
                        · {{ $plan->max_users ?? '∞' }} utilisateurs
                    </div>
                    @if($plan->trial_days > 0)
                        <div class="plan-card-trial">{{ $plan->trial_days }} jours d'essai</div>
                    @endif
                </label>
                @endforeach
            </div>
        </div>

        {{-- Submit --}}
        <div style="display:flex;align-items:center;gap:1rem;">
            <button type="submit" class="btn-create" id="submitBtn">
                Créer et initialiser le panel →
            </button>
            <span style="font-size:.78rem;color:#475569;">
                Cela prend quelques secondes (migrations + données initiales).
            </span>
        </div>

    </form>
</div>

@endsection

@push('scripts')
<script>
// Aperçu sous-domaine en temps réel
const slugInput   = document.getElementById('slug');
const slugDomain  = document.getElementById('slugDomain');
const nameInput   = document.getElementById('name');

function sanitizeSlug(val) {
    return val.toLowerCase()
              .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
              .replace(/[^a-z0-9-]/g, '-')
              .replace(/-+/g, '-')
              .replace(/^-|-$/g, '');
}

slugInput.addEventListener('input', () => {
    slugDomain.textContent = slugInput.value || 'votre-slug';
});

// Auto-remplir le slug depuis le nom (seulement si le slug est vide)
nameInput.addEventListener('input', () => {
    if (!slugInput.value) {
        slugInput.value = sanitizeSlug(nameInput.value);
        slugDomain.textContent = slugInput.value || 'votre-slug';
    }
});

// Désactiver le bouton pendant la soumission
document.getElementById('createForm').addEventListener('submit', function () {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.textContent = 'Création en cours…';
});
</script>
@endpush
