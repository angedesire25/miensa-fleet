<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Accès refusé · Miensa Fleet</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* ── Cercles décoratifs en arrière-plan ── */
        .bg-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: .15;
            pointer-events: none;
        }
        .bg-orb-1 {
            width: 500px; height: 500px;
            background: #ef4444;
            top: -120px; right: -80px;
        }
        .bg-orb-2 {
            width: 350px; height: 350px;
            background: #10b981;
            bottom: -60px; left: -60px;
        }
        .bg-orb-3 {
            width: 200px; height: 200px;
            background: #f59e0b;
            top: 60%; left: 55%;
        }

        /* ── Grille de points décoratifs ── */
        .bg-dots {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255,255,255,.04) 1px, transparent 1px);
            background-size: 28px 28px;
        }

        /* ── Carte centrale ── */
        .card {
            position: relative;
            z-index: 10;
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 1.25rem;
            padding: 3rem 3.5rem;
            text-align: center;
            max-width: 520px;
            width: 90%;
            backdrop-filter: blur(12px);
            box-shadow: 0 32px 80px rgba(0,0,0,.4);
        }

        /* ── Icône principale ── */
        .icon-wrap {
            width: 88px;
            height: 88px;
            background: linear-gradient(135deg, rgba(239,68,68,.25), rgba(239,68,68,.1));
            border: 1.5px solid rgba(239,68,68,.35);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.75rem;
            animation: pulse-ring 2.5s ease-in-out infinite;
        }

        @keyframes pulse-ring {
            0%, 100% { box-shadow: 0 0 0 0 rgba(239,68,68,.3); }
            50%       { box-shadow: 0 0 0 14px rgba(239,68,68,0); }
        }

        /* ── Code 403 ── */
        .code {
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .12em;
            color: #ef4444;
            text-transform: uppercase;
            margin-bottom: .6rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
        }
        .code::before,
        .code::after {
            content: '';
            display: block;
            height: 1px;
            width: 32px;
            background: rgba(239,68,68,.4);
        }

        /* ── Titre ── */
        .title {
            font-size: 1.75rem;
            font-weight: 800;
            color: #f1f5f9;
            margin-bottom: .75rem;
            line-height: 1.2;
        }

        /* ── Message ── */
        .message {
            font-size: .875rem;
            color: #94a3b8;
            line-height: 1.65;
            margin-bottom: 2rem;
        }
        .message strong {
            color: #cbd5e1;
        }

        /* ── Permissions manquantes (si disponibles) ── */
        .permission-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .3rem .75rem;
            background: rgba(239,68,68,.12);
            border: 1px solid rgba(239,68,68,.25);
            border-radius: 2rem;
            font-size: .75rem;
            font-weight: 600;
            color: #fca5a5;
            margin-bottom: 1.75rem;
            font-family: 'JetBrains Mono', 'Fira Code', ui-monospace, monospace;
        }

        /* ── Séparateur ── */
        .divider {
            height: 1px;
            background: rgba(255,255,255,.07);
            margin: 0 auto 1.75rem;
        }

        /* ── Boutons ── */
        .actions {
            display: flex;
            gap: .75rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .65rem 1.4rem;
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            border-radius: .55rem;
            font-size: .84rem;
            font-weight: 700;
            text-decoration: none;
            transition: opacity .15s, transform .1s;
            border: none;
            cursor: pointer;
        }
        .btn-primary:hover { opacity: .88; transform: translateY(-1px); }

        .btn-ghost {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .65rem 1.4rem;
            background: rgba(255,255,255,.06);
            color: #94a3b8;
            border-radius: .55rem;
            font-size: .84rem;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid rgba(255,255,255,.1);
            transition: background .15s, color .15s;
        }
        .btn-ghost:hover { background: rgba(255,255,255,.1); color: #cbd5e1; }

        /* ── Logo en haut ── */
        .logo {
            position: absolute;
            top: 2rem;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            align-items: center;
            gap: .65rem;
            text-decoration: none;
            z-index: 10;
        }
        .logo-icon {
            width: 34px; height: 34px;
            background: linear-gradient(135deg,#10b981,#059669);
            border-radius: .45rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logo-text {
            font-size: .92rem;
            font-weight: 800;
            color: #f1f5f9;
            letter-spacing: -.01em;
        }
        .logo-text span { color: #10b981; }

        /* ── Infos utilisateur en bas de carte ── */
        .user-info {
            margin-top: 1.5rem;
            padding-top: 1.25rem;
            border-top: 1px solid rgba(255,255,255,.06);
            font-size: .78rem;
            color: #64748b;
        }
        .user-info strong { color: #94a3b8; }
    </style>
</head>
<body>

    {{-- Éléments décoratifs ──────────────────────────── --}}
    <div class="bg-orb bg-orb-1"></div>
    <div class="bg-orb bg-orb-2"></div>
    <div class="bg-orb bg-orb-3"></div>
    <div class="bg-dots"></div>

    {{-- Logo Miensa Fleet en haut ──────────────────────── --}}
    <a href="{{ route('dashboard') }}" class="logo">
        <div class="logo-icon">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24">
                <path d="M3 17h2l1-3h12l1 3h2" stroke="#fff" stroke-width="2" stroke-linecap="round"/>
                <circle cx="7.5" cy="18.5" r="1.5" stroke="#fff" stroke-width="1.5"/>
                <circle cx="16.5" cy="18.5" r="1.5" stroke="#fff" stroke-width="1.5"/>
                <path d="M5 14l2-6h10l2 6" stroke="#fff" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
        </div>
        <span class="logo-text">Miensa<span>Fleet</span></span>
    </a>

    {{-- Carte principale ───────────────────────────────── --}}
    <div class="card">

        {{-- Icône cadenas ── --}}
        <div class="icon-wrap">
            <svg width="38" height="38" fill="none" viewBox="0 0 24 24">
                <rect x="5" y="11" width="14" height="10" rx="2" stroke="#ef4444" stroke-width="1.8"/>
                <path d="M8 11V7a4 4 0 118 0v4" stroke="#ef4444" stroke-width="1.8" stroke-linecap="round"/>
                <circle cx="12" cy="16" r="1.5" fill="#ef4444"/>
                <path d="M12 17.5v1.5" stroke="#ef4444" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
        </div>

        {{-- Code erreur ── --}}
        <div class="code">Erreur 403</div>

        {{-- Titre ── --}}
        <h1 class="title">Accès non autorisé</h1>

        {{-- Message ── --}}
        <p class="message">
            Vous n'avez pas les <strong>permissions nécessaires</strong> pour accéder à cette page.<br>
            Si vous pensez qu'il s'agit d'une erreur, contactez votre
            <strong>responsable de flotte</strong> ou un administrateur.
        </p>

        {{-- Permission manquante ── --}}
        @php
            $missingPermission = $exception->getMessage() ?? null;
        @endphp
        @if($missingPermission && str_contains($missingPermission, '.'))
            <div class="permission-badge">
                <svg width="12" height="12" fill="none" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
                    <path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Permission requise : {{ $missingPermission }}
            </div>
        @endif

        <div class="divider"></div>

        {{-- Actions ── --}}
        <div class="actions">
            @auth
                <a href="{{ route('dashboard') }}" class="btn-primary">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/>
                        <rect x="14" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/>
                        <rect x="3" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/>
                        <rect x="14" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    Tableau de bord
                </a>
                <a href="javascript:history.back()" class="btn-ghost">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24">
                        <path d="M19 12H5M12 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Retour
                </a>
            @else
                <a href="{{ route('login') }}" class="btn-primary">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24">
                        <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Se connecter
                </a>
            @endauth
        </div>

        {{-- Infos utilisateur connecté ── --}}
        @auth
            @php
                $user   = auth()->user();
                $labels = [
                    'super_admin'   => 'Super Administrateur',
                    'admin'         => 'Administrateur',
                    'fleet_manager' => 'Responsable Flotte',
                    'controller'    => 'Contrôleur',
                    'director'      => 'Directeur',
                    'collaborator'  => 'Collaborateur',
                    'driver_user'   => 'Chauffeur',
                ];
                $roleName  = $user->getRoleNames()->first() ?? '';
                $roleLabel = $labels[$roleName] ?? $roleName;
            @endphp
            <div class="user-info">
                Connecté en tant que <strong>{{ $user->name }}</strong>
                @if($roleLabel)
                    — rôle <strong>{{ $roleLabel }}</strong>
                @endif
            </div>
        @endauth
    </div>

</body>
</html>
