@extends('landlord.layouts.app')

@section('title', 'Compte créé avec succès')

@push('styles')
<style>
    .success-wrap {
        max-width: 560px; margin: 5rem auto; padding: 0 1.5rem; text-align: center;
    }
    .success-icon {
        width: 80px; height: 80px;
        background: linear-gradient(135deg, #22c55e, #16a34a);
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        margin: 0 auto 1.5rem; box-shadow: 0 8px 24px rgba(34,197,94,.3);
    }
    .success-wrap h1 { font-size: 1.8rem; font-weight: 800; color: #0f172a; margin: 0 0 .75rem; }
    .success-wrap p { color: #64748b; font-size: .95rem; line-height: 1.7; margin-bottom: 1.5rem; }

    .info-card {
        background: white; border: 1.5px solid #e2e8f0; border-radius: 14px;
        padding: 1.5rem; margin-bottom: 1.5rem; text-align: left;
    }
    .info-card h3 { font-size: .85rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .05em; margin: 0 0 1rem; }
    .info-row { display: flex; justify-content: space-between; align-items: center; padding: .5rem 0; border-bottom: 1px solid #f1f5f9; font-size: .9rem; }
    .info-row:last-child { border-bottom: none; }
    .info-row-label { color: #64748b; }
    .info-row-value { font-weight: 600; color: #0f172a; }
    .info-url { color: #1d4ed8; word-break: break-all; }

    .trial-banner {
        background: linear-gradient(135deg, #fffbeb, #fef3c7);
        border: 1.5px solid #fcd34d; border-radius: 10px;
        padding: 1rem 1.25rem; display: flex; align-items: flex-start; gap: .75rem;
        font-size: .88rem; color: #92400e; margin-bottom: 1.5rem; text-align: left;
    }
    .trial-banner svg { flex-shrink: 0; margin-top: 1px; }

    .btn-go {
        display: inline-block; background: #1d4ed8; color: white;
        padding: .875rem 2.5rem; border-radius: 10px; font-weight: 700;
        font-size: 1rem; text-decoration: none; transition: background .15s;
        margin-bottom: .75rem;
    }
    .btn-go:hover { background: #1e40af; color: white; }
    .back-link { display: block; font-size: .85rem; color: #94a3b8; text-decoration: none; margin-top: .5rem; }
    .back-link:hover { color: #64748b; }
</style>
@endpush

@section('content')
<div class="success-wrap">

    <div class="success-icon">
        <svg width="38" height="38" fill="none" stroke="white" stroke-width="2.5" viewBox="0 0 24 24">
            <path d="M20 6 9 17l-5-5"/>
        </svg>
    </div>

    <h1>Votre espace est prêt !</h1>
    <p>Le compte <strong>{{ $tenantName }}</strong> a été provisionné avec succès. Votre base de données est isolée et vos rôles sont configurés.</p>

    <div class="info-card">
        <h3>Informations de connexion</h3>
        <div class="info-row">
            <span class="info-row-label">URL de votre espace</span>
            <a href="https://{{ $domain }}" class="info-row-value info-url" target="_blank">https://{{ $domain }}</a>
        </div>
        <div class="info-row">
            <span class="info-row-label">Plan souscrit</span>
            <span class="info-row-value">{{ $planName }}</span>
        </div>
        @if($trialEndsAt)
        <div class="info-row">
            <span class="info-row-label">Essai gratuit jusqu'au</span>
            <span class="info-row-value">{{ $trialEndsAt }}</span>
        </div>
        @endif
    </div>

    @if($trialEndsAt)
    <div class="trial-banner">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
        <div>Votre période d'essai est active jusqu'au <strong>{{ $trialEndsAt }}</strong>. Profitez de toutes les fonctionnalités sans restriction pendant cette période.</div>
    </div>
    @endif

    <div>
        <a href="https://{{ $domain }}" class="btn-go" target="_blank">
            Accéder à mon espace →
        </a>
        <p style="font-size:.84rem;color:#94a3b8;margin:.5rem 0 0;">
            Un email avec vos identifiants vous a été envoyé.
        </p>
        <a href="{{ route('landlord.home') }}" class="back-link">← Retour à l'accueil</a>
    </div>

</div>
@endsection
