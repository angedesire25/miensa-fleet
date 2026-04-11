@extends('landlord.layouts.app')

@section('title', 'Créer votre compte')
@section('meta_description', 'Créez votre espace MiensaFleet en moins de 2 minutes. Aucune carte bancaire requise.')

@push('styles')
<style>
    .signup-wrap {
        max-width: 980px; margin: 3rem auto; padding: 0 1.5rem;
        display: grid; grid-template-columns: 1fr 1.2fr; gap: 3rem; align-items: start;
    }

    /* ── Panneau gauche ────────────────────────────────── */
    .signup-left h1 { font-size: 1.9rem; font-weight: 800; color: #0f172a; margin: 0 0 .75rem; line-height: 1.2; }
    .signup-left p { color: #64748b; font-size: .95rem; line-height: 1.7; margin-bottom: 1.5rem; }
    .plan-selector { display: flex; flex-direction: column; gap: .6rem; margin-bottom: 2rem; }
    .plan-opt {
        border: 1.5px solid #e2e8f0; border-radius: 10px; padding: .9rem 1rem;
        cursor: pointer; transition: border-color .15s, background .15s;
        display: flex; align-items: center; gap: .75rem;
    }
    .plan-opt:hover { border-color: #3b82f6; background: #eff6ff; }
    .plan-opt.selected { border-color: #3b82f6; background: #eff6ff; }
    .plan-opt input[type=radio] { accent-color: #3b82f6; width: 16px; height: 16px; flex-shrink: 0; }
    .plan-opt-info { flex: 1; }
    .plan-opt-name { font-weight: 700; color: #0f172a; font-size: .9rem; }
    .plan-opt-desc { font-size: .8rem; color: #64748b; }
    .plan-opt-price { font-weight: 700; color: #1d4ed8; font-size: .9rem; white-space: nowrap; }

    .signup-perks { list-style: none; padding: 0; margin: 0; }
    .signup-perks li { display: flex; align-items: flex-start; gap: .6rem; font-size: .88rem; color: #374151; padding: .35rem 0; }
    .perk-icon { color: #22c55e; flex-shrink: 0; margin-top: 1px; }

    /* ── Formulaire ────────────────────────────────────── */
    .signup-card { background: white; border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 2rem; }
    .signup-card h2 { font-size: 1.15rem; font-weight: 700; color: #0f172a; margin: 0 0 1.5rem; }

    .form-group { margin-bottom: 1.15rem; }
    .form-label { display: block; font-size: .83rem; font-weight: 600; color: #374151; margin-bottom: .35rem; }
    .form-label .required { color: #ef4444; }
    .form-control {
        display: block; width: 100%; padding: .65rem .9rem;
        border: 1.5px solid #e2e8f0; border-radius: 8px;
        font-size: .92rem; color: #0f172a; outline: none;
        transition: border-color .15s, box-shadow .15s;
        font-family: inherit;
    }
    .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
    .form-control.is-invalid { border-color: #ef4444; }

    .slug-wrap { position: relative; }
    .slug-prefix {
        position: absolute; left: 0; top: 0; bottom: 0;
        display: flex; align-items: center; padding: 0 .75rem;
        background: #f8fafc; border: 1.5px solid #e2e8f0;
        border-right: none; border-radius: 8px 0 0 8px;
        font-size: .82rem; color: #64748b; white-space: nowrap;
    }
    .slug-wrap .form-control { border-radius: 0 8px 8px 0; }

    .invalid-feedback { font-size: .8rem; color: #ef4444; margin-top: .3rem; }
    .form-hint { font-size: .79rem; color: #94a3b8; margin-top: .3rem; }

    .btn-submit {
        width: 100%; padding: .875rem; background: #1d4ed8; color: white;
        border: none; border-radius: 10px; font-size: 1rem; font-weight: 700;
        cursor: pointer; transition: background .15s; font-family: inherit;
        margin-top: .5rem;
    }
    .btn-submit:hover { background: #1e40af; }
    .btn-submit:active { transform: scale(.99); }
    .btn-submit:disabled { opacity: .6; cursor: not-allowed; }

    .form-footer { text-align: center; font-size: .82rem; color: #94a3b8; margin-top: 1rem; }
    .form-footer a { color: #3b82f6; text-decoration: none; }

    @media (max-width: 800px) {
        .signup-wrap { grid-template-columns: 1fr; }
        .signup-left { order: 2; }
        .signup-card { order: 1; }
    }
</style>
@endpush

@section('content')
<div class="signup-wrap">

    {{-- ── Panneau gauche : arguments ────────────────────────────────── --}}
    <div class="signup-left">
        <h1>Créez votre espace MiensaFleet</h1>
        <p>Provisionnement automatique en moins de 30 secondes. Votre base de données est isolée, sécurisée et prête à l'emploi.</p>

        {{-- Sélecteur de plan (synchronisé avec le formulaire) --}}
        <div class="plan-selector" id="planSelector">
            @foreach($plans as $plan)
            <label class="plan-opt {{ $selectedPlan === $plan->slug ? 'selected' : '' }}" data-slug="{{ $plan->slug }}">
                <input type="radio" name="plan_display" value="{{ $plan->slug }}" form="signupForm"
                    {{ $selectedPlan === $plan->slug ? 'checked' : '' }}>
                <div class="plan-opt-info">
                    <div class="plan-opt-name">{{ $plan->name }}</div>
                    <div class="plan-opt-desc">
                        {{ $plan->max_vehicles >= 999 ? 'Illimité' : $plan->max_vehicles . ' veh.' }}
                        · {{ $plan->max_users >= 999 ? 'Illimité' : $plan->max_users . ' users' }}
                        @if($plan->trial_days > 0) · {{ $plan->trial_days }}j d'essai @endif
                    </div>
                </div>
                <span class="plan-opt-price">
                    {{ $plan->price_monthly == 0 ? 'Gratuit' : number_format($plan->price_monthly, 0, ',', ' ') . ' FCFA/mois' }}
                </span>
            </label>
            @endforeach
        </div>

        <ul class="signup-perks">
            <li><svg class="perk-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>Base de données dédiée & isolée</li>
            <li><svg class="perk-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>Sous-domaine personnalisé inclus</li>
            <li><svg class="perk-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>Aucune carte bancaire requise</li>
            <li><svg class="perk-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>Résiliation possible à tout moment</li>
            <li><svg class="perk-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>Support en français inclus</li>
        </ul>
    </div>

    {{-- ── Formulaire ────────────────────────────────────────────────── --}}
    <div class="signup-card">
        <h2>Informations de votre entreprise</h2>

        @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:.75rem 1rem;margin-bottom:1rem;font-size:.86rem;color:#dc2626;">
            <strong>Veuillez corriger les erreurs suivantes :</strong>
            <ul style="margin:.4rem 0 0;padding-left:1.25rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form id="signupForm" action="{{ route('landlord.signup.store') }}" method="POST">
            @csrf

            {{-- Plan (caché, piloté par le sélecteur à gauche) --}}
            <input type="hidden" name="plan" id="planInput" value="{{ old('plan', $selectedPlan) }}">

            <div class="form-group">
                <label class="form-label" for="company_name">Nom de la société <span class="required">*</span></label>
                <input type="text" id="company_name" name="company_name" class="form-control {{ $errors->has('company_name') ? 'is-invalid' : '' }}"
                    value="{{ old('company_name') }}" placeholder="Ex : Geomatos SARL" required autofocus>
                @error('company_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="slug">Identifiant (sous-domaine) <span class="required">*</span></label>
                <div class="slug-wrap">
                    <span class="slug-prefix">miensafleet.ci/</span>
                    <input type="text" id="slug" name="slug" class="form-control {{ $errors->has('slug') ? 'is-invalid' : '' }}"
                        value="{{ old('slug') }}" placeholder="geomatos" required
                        style="padding-left: calc(1rem + 155px);"
                        pattern="[a-z0-9\-]+" title="Lettres minuscules, chiffres et tirets uniquement">
                </div>
                <div class="form-hint">Lettres minuscules, chiffres et tirets uniquement. Exemple : geomatos-sarl</div>
                @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label class="form-label" for="contact_name">Nom du contact <span class="required">*</span></label>
                    <input type="text" id="contact_name" name="contact_name" class="form-control {{ $errors->has('contact_name') ? 'is-invalid' : '' }}"
                        value="{{ old('contact_name') }}" placeholder="Jean Kouassi" required>
                    @error('contact_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="contact_phone">Téléphone</label>
                    <input type="tel" id="contact_phone" name="contact_phone" class="form-control"
                        value="{{ old('contact_phone') }}" placeholder="+225 07 00 00 00 00">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="contact_email">Email professionnel <span class="required">*</span></label>
                <input type="email" id="contact_email" name="contact_email" class="form-control {{ $errors->has('contact_email') ? 'is-invalid' : '' }}"
                    value="{{ old('contact_email') }}" placeholder="contact@geomatos.ci" required>
                @error('contact_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn-submit" id="submitBtn">
                Créer mon espace MiensaFleet →
            </button>

            <p class="form-footer">
                En vous inscrivant, vous acceptez nos
                <a href="#">conditions d'utilisation</a> et notre
                <a href="#">politique de confidentialité</a>.
            </p>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Sync plan selector with hidden input
    document.querySelectorAll('#planSelector .plan-opt').forEach(function(opt) {
        opt.addEventListener('click', function() {
            document.querySelectorAll('#planSelector .plan-opt').forEach(o => o.classList.remove('selected'));
            opt.classList.add('selected');
            var slug = opt.dataset.slug;
            document.getElementById('planInput').value = slug;
            opt.querySelector('input[type=radio]').checked = true;
        });
    });

    // Auto-generate slug from company name
    var slugInput = document.getElementById('slug');
    var slugModified = slugInput.value.length > 0;
    document.getElementById('company_name').addEventListener('input', function() {
        if (slugModified) return;
        var val = this.value
            .toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9\s-]/g, '')
            .trim()
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
        slugInput.value = val;
    });
    slugInput.addEventListener('input', function() {
        slugModified = this.value.length > 0;
    });

    // Disable submit on submit
    document.getElementById('signupForm').addEventListener('submit', function() {
        var btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.textContent = 'Provisionnement en cours…';
    });
</script>
@endpush
