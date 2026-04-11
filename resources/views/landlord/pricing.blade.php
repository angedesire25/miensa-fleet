@extends('landlord.layouts.app')

@section('title', 'Tarifs')
@section('meta_description', 'Choisissez le plan MiensaFleet adapté à votre flotte. Gratuit, Essentiel ou Pro — commencez dès aujourd\'hui.')

@push('styles')
<style>
    /* ── Hero ────────────────────────────────────────────────────────── */
    .hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
        color: white;
        padding: 5rem 2rem 4rem;
        text-align: center;
    }
    .hero-badge {
        display: inline-flex; align-items: center; gap: .4rem;
        background: rgba(59,130,246,.2); border: 1px solid rgba(59,130,246,.4);
        color: #93c5fd; border-radius: 20px; padding: .3rem .9rem;
        font-size: .8rem; font-weight: 600; letter-spacing: .04em;
        text-transform: uppercase; margin-bottom: 1.5rem;
    }
    .hero h1 { font-size: 2.8rem; font-weight: 800; margin: 0 0 1rem; line-height: 1.15; }
    .hero h1 span { color: #60a5fa; }
    .hero p { font-size: 1.1rem; color: #94a3b8; max-width: 540px; margin: 0 auto 2rem; line-height: 1.7; }

    /* ── Pricing cards ───────────────────────────────────────────────── */
    .pricing-section { padding: 4rem 2rem; max-width: 1100px; margin: 0 auto; }
    .pricing-grid {
        display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;
        align-items: stretch;
    }
    .pricing-card {
        background: white; border: 1.5px solid #e2e8f0; border-radius: 16px;
        padding: 2rem; display: flex; flex-direction: column;
        transition: box-shadow .2s, transform .2s;
    }
    .pricing-card:hover { box-shadow: 0 8px 30px rgba(0,0,0,.1); transform: translateY(-3px); }
    .pricing-card.featured {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,.15), 0 8px 30px rgba(59,130,246,.15);
        position: relative;
    }
    .featured-badge {
        position: absolute; top: -12px; left: 50%; transform: translateX(-50%);
        background: linear-gradient(90deg,#3b82f6,#2563eb); color: white;
        font-size: .75rem; font-weight: 700; padding: .25rem .85rem;
        border-radius: 12px; white-space: nowrap; letter-spacing: .04em;
        text-transform: uppercase;
    }
    .plan-name { font-size: 1.1rem; font-weight: 700; color: #0f172a; margin-bottom: .25rem; }
    .plan-desc { font-size: .87rem; color: #64748b; line-height: 1.5; margin-bottom: 1.5rem; }
    .plan-price { margin-bottom: 1.5rem; }
    .plan-amount { font-size: 2.2rem; font-weight: 800; color: #0f172a; }
    .plan-amount sup { font-size: 1rem; vertical-align: top; margin-top: .5rem; font-weight: 600; }
    .plan-period { font-size: .85rem; color: #94a3b8; }
    .plan-free { font-size: 2rem; font-weight: 800; color: #0f172a; }

    .plan-features { list-style: none; padding: 0; margin: 0 0 2rem; flex: 1; }
    .plan-features li {
        display: flex; align-items: flex-start; gap: .6rem;
        font-size: .88rem; color: #374151; padding: .45rem 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .plan-features li:last-child { border-bottom: none; }
    .feat-icon { width: 18px; height: 18px; flex-shrink: 0; margin-top: 1px; }
    .feat-icon.ok { color: #22c55e; }
    .feat-icon.no { color: #cbd5e1; }

    .plan-limits {
        background: #f8fafc; border-radius: 8px; padding: .75rem 1rem;
        margin-bottom: 1.5rem; font-size: .83rem; color: #475569;
        display: flex; flex-direction: column; gap: .3rem;
    }
    .plan-limits span { display: flex; align-items: center; gap: .4rem; }

    .plan-cta {
        display: block; text-align: center; padding: .75rem 1.5rem;
        border-radius: 10px; font-weight: 600; font-size: .95rem;
        text-decoration: none; transition: background .15s, transform .1s;
    }
    .plan-cta:active { transform: scale(.98); }
    .plan-cta-primary { background: #1d4ed8; color: white; }
    .plan-cta-primary:hover { background: #1e40af; color: white; }
    .plan-cta-outline { border: 1.5px solid #e2e8f0; color: #374151; }
    .plan-cta-outline:hover { border-color: #1d4ed8; color: #1d4ed8; }

    /* ── Trial note ──────────────────────────────────────────────────── */
    .trial-note {
        text-align: center; color: #64748b; font-size: .87rem; margin-top: 1.5rem;
        display: flex; align-items: center; justify-content: center; gap: .4rem;
    }

    /* ── Features comparison ─────────────────────────────────────────── */
    .compare-section { padding: 2rem 2rem 4rem; max-width: 1100px; margin: 0 auto; }
    .compare-section h2 { font-size: 1.6rem; font-weight: 700; text-align: center; margin-bottom: 2rem; }
    .compare-table { width: 100%; border-collapse: collapse; font-size: .9rem; }
    .compare-table th { background: #f8fafc; padding: .75rem 1rem; text-align: left; font-weight: 600; color: #475569; font-size: .82rem; text-transform: uppercase; letter-spacing: .04em; border-bottom: 2px solid #e2e8f0; }
    .compare-table th:not(:first-child) { text-align: center; }
    .compare-table td { padding: .7rem 1rem; border-bottom: 1px solid #f1f5f9; color: #374151; }
    .compare-table td:not(:first-child) { text-align: center; }
    .compare-table tr:hover td { background: #f8fafc; }
    .compare-table .category td { font-weight: 700; color: #0f172a; background: #f1f5f9; }
    .check { color: #22c55e; font-size: 1.1rem; }
    .cross { color: #e2e8f0; font-size: 1.1rem; }

    /* ── FAQ ─────────────────────────────────────────────────────────── */
    .faq-section { background: white; padding: 4rem 2rem; }
    .faq-inner { max-width: 720px; margin: 0 auto; }
    .faq-inner h2 { font-size: 1.6rem; font-weight: 700; text-align: center; margin-bottom: 2rem; }
    .faq-item { border-bottom: 1px solid #f1f5f9; padding: 1.1rem 0; }
    .faq-q { font-weight: 600; color: #0f172a; font-size: .95rem; cursor: pointer; display: flex; justify-content: space-between; }
    .faq-a { color: #64748b; font-size: .9rem; line-height: 1.7; margin-top: .5rem; }

    /* ── CTA bottom ──────────────────────────────────────────────────── */
    .cta-section {
        background: linear-gradient(135deg, #1e3a5f 0%, #0f172a 100%);
        color: white; text-align: center; padding: 5rem 2rem;
    }
    .cta-section h2 { font-size: 2rem; font-weight: 800; margin: 0 0 1rem; }
    .cta-section p { color: #94a3b8; margin-bottom: 2rem; font-size: 1rem; }
    .cta-btn {
        display: inline-block; background: #3b82f6; color: white;
        padding: .875rem 2.5rem; border-radius: 10px; font-weight: 700;
        font-size: 1rem; text-decoration: none; transition: background .15s;
    }
    .cta-btn:hover { background: #2563eb; color: white; }

    @media (max-width: 900px) {
        .pricing-grid { grid-template-columns: 1fr; max-width: 400px; margin: 0 auto; }
        .hero h1 { font-size: 2rem; }
    }
</style>
@endpush

@section('content')

{{-- ── Hero ──────────────────────────────────────────────────────────────── --}}
<section class="hero">
    <div class="hero-badge">
        <svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
        Tarification simple & transparente
    </div>
    <h1>La gestion de flotte<br><span>à portée de toutes les entreprises</span></h1>
    <p>Choisissez le plan adapté à votre taille. Commencez gratuitement, évoluez selon vos besoins. Aucune carte bancaire requise.</p>
</section>

{{-- ── Pricing cards ──────────────────────────────────────────────────────── --}}
<section class="pricing-section">
    <div class="pricing-grid">
        @foreach ($plans as $plan)
        <div class="pricing-card {{ $plan->is_featured ? 'featured' : '' }}">
            @if($plan->is_featured)
                <div class="featured-badge">Le plus populaire</div>
            @endif

            <div class="plan-name">{{ $plan->name }}</div>
            <div class="plan-desc">{{ $plan->description }}</div>

            <div class="plan-price">
                @if($plan->price_monthly == 0)
                    <div class="plan-free">Gratuit</div>
                    <div class="plan-period">Pour toujours</div>
                @else
                    <div class="plan-amount">
                        <sup>FCFA</sup>{{ number_format($plan->price_monthly, 0, ',', ' ') }}
                    </div>
                    <div class="plan-period">/ mois · {{ number_format($plan->price_yearly, 0, ',', ' ') }} FCFA/an</div>
                @endif
            </div>

            <div class="plan-limits">
                <span>
                    <svg class="feat-icon ok" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 17H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3"/><rect x="9" y="11" width="13" height="10" rx="2"/></svg>
                    {{ $plan->max_vehicles >= 999 ? 'Véhicules illimités' : $plan->max_vehicles . ' véhicules max' }}
                </span>
                <span>
                    <svg class="feat-icon ok" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    {{ $plan->max_users >= 999 ? 'Utilisateurs illimités' : $plan->max_users . ' utilisateurs max' }}
                </span>
                <span>
                    <svg class="feat-icon ok" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    {{ $plan->trial_days > 0 ? $plan->trial_days . ' jours d\'essai gratuit' : 'Accès immédiat' }}
                </span>
            </div>

            <ul class="plan-features">
                @php
                    $features = [
                        ['label' => 'Suivi des véhicules', 'available' => true],
                        ['label' => 'Gestion des chauffeurs', 'available' => true],
                        ['label' => 'Visites techniques & documents', 'available' => $plan->has_inspections],
                        ['label' => 'Réparations & maintenances', 'available' => $plan->has_repairs],
                        ['label' => 'Infractions & amendes', 'available' => $plan->has_infractions],
                        ['label' => 'Sinistres & incidents', 'available' => $plan->has_incidents],
                        ['label' => 'Rapports avancés', 'available' => $plan->has_reports],
                        ['label' => 'Accès API', 'available' => $plan->has_api],
                    ];
                @endphp
                @foreach($features as $feat)
                <li>
                    @if($feat['available'])
                        <svg class="feat-icon ok" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
                    @else
                        <svg class="feat-icon no" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    @endif
                    <span style="{{ $feat['available'] ? '' : 'color:#94a3b8' }}">{{ $feat['label'] }}</span>
                </li>
                @endforeach
            </ul>

            <a href="{{ route('landlord.signup', ['plan' => $plan->slug]) }}"
               class="plan-cta {{ $plan->is_featured ? 'plan-cta-primary' : 'plan-cta-outline' }}">
                @if($plan->trial_days > 0)
                    Essayer {{ $plan->trial_days }} jours gratuitement
                @elseif($plan->price_monthly == 0)
                    Commencer gratuitement
                @else
                    Choisir ce plan
                @endif
            </a>
        </div>
        @endforeach
    </div>

    <p class="trial-note">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        Aucune carte bancaire requise · Résiliation à tout moment · Données hébergées en sécurité
    </p>
</section>

{{-- ── Tableau comparatif ─────────────────────────────────────────────────── --}}
<section class="compare-section">
    <h2>Comparatif détaillé des plans</h2>
    <table class="compare-table">
        <thead>
            <tr>
                <th style="width:40%">Fonctionnalité</th>
                <th>Gratuit</th>
                <th>Essentiel</th>
                <th>Pro</th>
            </tr>
        </thead>
        <tbody>
            <tr class="category"><td colspan="4">Gestion de base</td></tr>
            <tr><td>Suivi des véhicules</td><td><span class="check">✓</span></td><td><span class="check">✓</span></td><td><span class="check">✓</span></td></tr>
            <tr><td>Gestion des chauffeurs</td><td><span class="check">✓</span></td><td><span class="check">✓</span></td><td><span class="check">✓</span></td></tr>
            <tr><td>Affectations véhicule ↔ chauffeur</td><td><span class="check">✓</span></td><td><span class="check">✓</span></td><td><span class="check">✓</span></td></tr>
            <tr><td>Alertes automatiques (expiration docs)</td><td><span class="check">✓</span></td><td><span class="check">✓</span></td><td><span class="check">✓</span></td></tr>

            <tr class="category"><td colspan="4">Maintenance</td></tr>
            <tr><td>Inspections & visites techniques</td><td><span class="check">✓</span></td><td><span class="check">✓</span></td><td><span class="check">✓</span></td></tr>
            <tr><td>Réparations & maintenances</td><td><span class="cross">—</span></td><td><span class="check">✓</span></td><td><span class="check">✓</span></td></tr>
            <tr><td>Suivi des pièces & garanties</td><td><span class="cross">—</span></td><td><span class="check">✓</span></td><td><span class="check">✓</span></td></tr>

            <tr class="category"><td colspan="4">Conformité & incidents</td></tr>
            <tr><td>Gestion des infractions</td><td><span class="cross">—</span></td><td><span class="check">✓</span></td><td><span class="check">✓</span></td></tr>
            <tr><td>Gestion des sinistres</td><td><span class="cross">—</span></td><td><span class="check">✓</span></td><td><span class="check">✓</span></td></tr>
            <tr><td>Gestion des garages partenaires</td><td><span class="cross">—</span></td><td><span class="check">✓</span></td><td><span class="check">✓</span></td></tr>

            <tr class="category"><td colspan="4">Analyse & intégrations</td></tr>
            <tr><td>Tableau de bord & indicateurs</td><td>Basique</td><td>Avancé</td><td>Complet</td></tr>
            <tr><td>Rapports exportables (PDF, Excel)</td><td><span class="cross">—</span></td><td><span class="cross">—</span></td><td><span class="check">✓</span></td></tr>
            <tr><td>Accès API REST</td><td><span class="cross">—</span></td><td><span class="cross">—</span></td><td><span class="check">✓</span></td></tr>

            <tr class="category"><td colspan="4">Limites</td></tr>
            <tr><td>Véhicules</td><td>3</td><td>15</td><td>Illimité</td></tr>
            <tr><td>Utilisateurs</td><td>2</td><td>5</td><td>Illimité</td></tr>
            <tr><td>Chauffeurs</td><td>5</td><td>20</td><td>Illimité</td></tr>
            <tr><td>Support</td><td>Email</td><td>Email prioritaire</td><td>Dédié</td></tr>
        </tbody>
    </table>
</section>

{{-- ── FAQ ───────────────────────────────────────────────────────────────── --}}
<section class="faq-section">
    <div class="faq-inner">
        <h2>Questions fréquentes</h2>

        <div class="faq-item">
            <div class="faq-q">Puis-je changer de plan à tout moment ?</div>
            <div class="faq-a">Oui. Vous pouvez passer à un plan supérieur ou inférieur à tout moment depuis votre tableau de bord. La facturation est ajustée au prorata.</div>
        </div>
        <div class="faq-item">
            <div class="faq-q">Que se passe-t-il à la fin de l'essai gratuit ?</div>
            <div class="faq-a">À la fin des 14 jours d'essai, votre compte passe automatiquement en mode limité. Vous gardez l'accès en lecture à vos données. Il suffit de souscrire pour retrouver toutes les fonctionnalités.</div>
        </div>
        <div class="faq-item">
            <div class="faq-q">Comment fonctionne la facturation ?</div>
            <div class="faq-a">La facturation est mensuelle ou annuelle (avec 2 mois offerts). Nous acceptons les virements bancaires et le paiement mobile (Orange Money, MTN Mobile Money, Wave).</div>
        </div>
        <div class="faq-item">
            <div class="faq-q">Mes données sont-elles en sécurité ?</div>
            <div class="faq-a">Chaque client dispose de sa propre base de données isolée. Vos données ne sont jamais mélangées avec celles d'autres entreprises. Des sauvegardes quotidiennes sont effectuées.</div>
        </div>
        <div class="faq-item">
            <div class="faq-q">Y a-t-il une application mobile ?</div>
            <div class="faq-a">MiensaFleet est une application web responsive, utilisable depuis n'importe quel smartphone ou tablette. Une application mobile native est prévue pour les plans Essentiel et Pro.</div>
        </div>
    </div>
</section>

{{-- ── CTA final ─────────────────────────────────────────────────────────── --}}
<section class="cta-section">
    <h2>Prêt à moderniser votre flotte ?</h2>
    <p>Rejoignez les entreprises ivoiriennes qui font confiance à MiensaFleet.</p>
    <a href="{{ route('landlord.signup') }}" class="cta-btn">Démarrer l'essai gratuit →</a>
</section>

@endsection
