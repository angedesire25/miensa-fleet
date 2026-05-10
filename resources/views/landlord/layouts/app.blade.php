@php
    use App\Models\LandlordSetting;
    $siteName    = LandlordSetting::get('site_name',    'MiensaFleet');
    $siteDesc    = LandlordSetting::get('site_description', 'MiensaFleet, la solution SaaS de gestion de flotte automobile pour les entreprises africaines.');
    $logoPath    = LandlordSetting::get('logo_path');
    $ogImagePath = LandlordSetting::get('og_image_path');
    $footerTag   = LandlordSetting::get('footer_tagline', 'Fait avec ❤ en Côte d\'Ivoire');
    $socialFb    = LandlordSetting::get('social_facebook');
    $socialLi    = LandlordSetting::get('social_linkedin');
    $socialTw    = LandlordSetting::get('social_twitter');
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', $siteName) — Gestion de flotte intelligente</title>
    <meta name="description" content="@yield('meta_description', $siteDesc)">
    @if($ogImagePath)
    <meta property="og:image" content="{{ asset('storage/'.$ogImagePath) }}">
    @endif
    <meta property="og:site_name" content="{{ $siteName }}">
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
            @if($logoPath)
                <img src="{{ asset('storage/'.$logoPath) }}" alt="{{ $siteName }}" style="height:36px;max-width:140px;object-fit:contain;">
            @else
                <span class="lnav-brand-icon">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 17H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3"/>
                        <path d="M13 13h8m0 0-3-3m3 3-3 3"/>
                    </svg>
                </span>
                {{ $siteName }}
            @endif
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
                <div class="lfooter-brand">{{ $siteName }}</div>
                <p class="lfooter-desc">{{ $siteDesc }}</p>
                @if($socialFb || $socialLi || $socialTw)
                <div style="display:flex;gap:.75rem;margin-top:1rem;">
                    @if($socialFb)
                    <a href="{{ $socialFb }}" target="_blank" rel="noopener" style="color:#94a3b8;" title="Facebook">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                    </a>
                    @endif
                    @if($socialLi)
                    <a href="{{ $socialLi }}" target="_blank" rel="noopener" style="color:#94a3b8;" title="LinkedIn">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>
                    </a>
                    @endif
                    @if($socialTw)
                    <a href="{{ $socialTw }}" target="_blank" rel="noopener" style="color:#94a3b8;" title="Twitter / X">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                    @endif
                </div>
                @endif
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
            <span>&copy; {{ date('Y') }} {{ $siteName }}. Tous droits réservés.</span>
            <span>{{ $footerTag }}</span>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
