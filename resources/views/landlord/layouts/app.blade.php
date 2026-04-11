<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MiensaFleet') — Gestion de flotte intelligente</title>
    <meta name="description" content="@yield('meta_description', 'MiensaFleet, la solution SaaS de gestion de flotte automobile pour les entreprises africaines.')">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; background: #f8fafc; color: #1e293b; }

        /* ── Navbar ──────────────────────────────────────────────────────── */
        .lnav {
            position: sticky; top: 0; z-index: 50;
            background: rgba(255,255,255,.95);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid #e2e8f0;
            padding: 0 2rem;
            display: flex; align-items: center; justify-content: space-between;
            height: 64px;
        }
        .lnav-brand {
            display: flex; align-items: center; gap: .6rem;
            text-decoration: none; color: #0f172a; font-weight: 700; font-size: 1.1rem;
        }
        .lnav-brand-icon {
            width: 36px; height: 36px; background: linear-gradient(135deg,#3b82f6,#1d4ed8);
            border-radius: 9px; display: flex; align-items: center; justify-content: center;
        }
        .lnav-brand-icon svg { color: white; }
        .lnav-links { display: flex; align-items: center; gap: 1.5rem; }
        .lnav-links a {
            text-decoration: none; color: #475569; font-size: .92rem; font-weight: 500;
            transition: color .15s;
        }
        .lnav-links a:hover { color: #1d4ed8; }
        .lnav-cta {
            background: #1d4ed8; color: white; padding: .5rem 1.25rem;
            border-radius: 8px; font-size: .9rem; font-weight: 600;
            text-decoration: none; transition: background .15s;
        }
        .lnav-cta:hover { background: #1e40af; color: white; }

        /* ── Footer ──────────────────────────────────────────────────────── */
        .lfooter {
            background: #0f172a; color: #94a3b8;
            padding: 3rem 2rem 1.5rem;
            margin-top: auto;
        }
        .lfooter-inner {
            max-width: 1100px; margin: 0 auto;
            display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 3rem;
        }
        .lfooter-brand { color: white; font-weight: 700; font-size: 1rem; margin-bottom: .5rem; }
        .lfooter-desc { font-size: .87rem; line-height: 1.6; }
        .lfooter-col h4 { color: #e2e8f0; font-size: .85rem; font-weight: 600; margin: 0 0 .75rem; text-transform: uppercase; letter-spacing: .05em; }
        .lfooter-col a { display: block; color: #94a3b8; font-size: .87rem; text-decoration: none; margin-bottom: .4rem; transition: color .15s; }
        .lfooter-col a:hover { color: white; }
        .lfooter-bottom { max-width: 1100px; margin: 2rem auto 0; padding-top: 1.5rem; border-top: 1px solid #1e293b; display: flex; justify-content: space-between; align-items: center; font-size: .82rem; }

        @media (max-width: 768px) {
            .lnav-links { display: none; }
            .lfooter-inner { grid-template-columns: 1fr; gap: 2rem; }
            .lfooter-bottom { flex-direction: column; gap: .5rem; text-align: center; }
        }
    </style>
    @stack('styles')
</head>
<body style="display:flex;flex-direction:column;min-height:100vh;">

    {{-- ── Navbar ─────────────────────────────────────────────────────── --}}
    <nav class="lnav">
        <a href="{{ route('landlord.home') }}" class="lnav-brand">
            <span class="lnav-brand-icon">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 17H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3"/>
                    <path d="M13 13h8m0 0-3-3m3 3-3 3"/>
                </svg>
            </span>
            MiensaFleet
        </a>

        <div class="lnav-links">
            <a href="{{ route('landlord.pricing') }}">Tarifs</a>
            <a href="{{ route('landlord.contact') }}">Contact</a>
        </div>

        <a href="{{ route('landlord.signup') }}" class="lnav-cta">Essai gratuit</a>
    </nav>

    {{-- ── Contenu principal ───────────────────────────────────────────── --}}
    <main style="flex:1;">
        @yield('content')
    </main>

    {{-- ── Footer ─────────────────────────────────────────────────────── --}}
    <footer class="lfooter">
        <div class="lfooter-inner">
            <div>
                <div class="lfooter-brand">MiensaFleet</div>
                <p class="lfooter-desc">La solution de gestion de flotte conçue pour les entreprises ivoiriennes et africaines. Suivi des véhicules, chauffeurs, maintenances et bien plus.</p>
            </div>
            <div class="lfooter-col">
                <h4>Produit</h4>
                <a href="{{ route('landlord.pricing') }}">Tarifs</a>
                <a href="{{ route('landlord.signup') }}">Commencer</a>
                <a href="{{ route('landlord.contact') }}">Contact</a>
            </div>
            <div class="lfooter-col">
                <h4>Légal</h4>
                <a href="#">Conditions d'utilisation</a>
                <a href="#">Politique de confidentialité</a>
            </div>
        </div>
        <div class="lfooter-bottom">
            <span>&copy; {{ date('Y') }} MiensaFleet. Tous droits réservés.</span>
            <span>Fait avec ❤ en Côte d'Ivoire</span>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
