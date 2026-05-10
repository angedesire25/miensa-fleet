<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>419 — Session expirée · Miensa Fleet</title>
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

        .bg-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: .15;
            pointer-events: none;
        }
        .bg-orb-1 { width: 500px; height: 500px; background: #f59e0b; top: -120px; right: -80px; }
        .bg-orb-2 { width: 350px; height: 350px; background: #ef4444; bottom: -60px; left: -60px; }
        .bg-orb-3 { width: 200px; height: 200px; background: #f97316; top: 60%; left: 55%; }

        .bg-dots {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255,255,255,.04) 1px, transparent 1px);
            background-size: 28px 28px;
        }

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

        /* ── Horloge animée ── */
        .icon-wrap {
            width: 88px;
            height: 88px;
            background: linear-gradient(135deg, rgba(245,158,11,.25), rgba(245,158,11,.1));
            border: 1.5px solid rgba(245,158,11,.35);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.75rem;
            position: relative;
            animation: pulse-amber 2.5s ease-in-out infinite;
        }

        @keyframes pulse-amber {
            0%, 100% { box-shadow: 0 0 0 0 rgba(245,158,11,.3); }
            50%       { box-shadow: 0 0 0 14px rgba(245,158,11,0); }
        }

        /* Aiguille des secondes tourne */
        .clock-hand {
            transform-origin: 50% 80%;
            animation: tick 1s steps(60) infinite;
        }
        @keyframes tick {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
        }

        .code {
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .12em;
            color: #f59e0b;
            text-transform: uppercase;
            margin-bottom: .6rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
        }
        .code::before, .code::after {
            content: '';
            display: block;
            height: 1px;
            width: 32px;
            background: rgba(245,158,11,.4);
        }

        .title {
            font-size: 1.75rem;
            font-weight: 800;
            color: #f1f5f9;
            margin-bottom: .75rem;
            line-height: 1.2;
        }

        .message {
            font-size: .875rem;
            color: #94a3b8;
            line-height: 1.65;
            margin-bottom: 1.5rem;
        }
        .message strong { color: #cbd5e1; }

        /* ── Explication CSRF ── */
        .info-box {
            background: rgba(245,158,11,.07);
            border: 1px solid rgba(245,158,11,.18);
            border-radius: .75rem;
            padding: .9rem 1.1rem;
            margin-bottom: 1.75rem;
            text-align: left;
            display: flex;
            gap: .65rem;
            align-items: flex-start;
        }
        .info-box svg { flex-shrink: 0; margin-top: .05rem; }
        .info-box p { font-size: .8rem; color: #94a3b8; line-height: 1.55; }
        .info-box p strong { color: #fde68a; }

        /* ── Timer de décompte ── */
        .countdown-wrap {
            margin-bottom: 1.5rem;
        }
        .countdown-label { font-size: .75rem; color: #64748b; margin-bottom: .35rem; }
        .countdown {
            font-size: 2.5rem;
            font-weight: 800;
            color: #fbbf24;
            letter-spacing: .04em;
            font-variant-numeric: tabular-nums;
        }

        .divider { height: 1px; background: rgba(255,255,255,.07); margin: 0 auto 1.75rem; }

        .actions { display: flex; gap: .75rem; justify-content: center; flex-wrap: wrap; }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .65rem 1.4rem;
            background: linear-gradient(135deg, #f59e0b, #d97706);
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
            cursor: pointer;
        }
        .btn-ghost:hover { background: rgba(255,255,255,.1); color: #cbd5e1; }

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
            background: linear-gradient(135deg,#f59e0b,#d97706);
            border-radius: .45rem;
            display: flex; align-items: center; justify-content: center;
        }
        .logo-text { font-size: .92rem; font-weight: 800; color: #f1f5f9; letter-spacing: -.01em; }
        .logo-text span { color: #f59e0b; }

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

    <div class="bg-orb bg-orb-1"></div>
    <div class="bg-orb bg-orb-2"></div>
    <div class="bg-orb bg-orb-3"></div>
    <div class="bg-dots"></div>

    <a href="{{ url('/') }}" class="logo">
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

    <div class="card">

        {{-- Icône horloge ── --}}
        <div class="icon-wrap">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none">
                {{-- Cadran --}}
                <circle cx="12" cy="12" r="9" stroke="#f59e0b" stroke-width="1.8"/>
                {{-- Graduations 12h, 3h, 6h, 9h --}}
                <line x1="12" y1="4"  x2="12" y2="5.5" stroke="#f59e0b" stroke-width="1.5" stroke-linecap="round"/>
                <line x1="12" y1="18.5" x2="12" y2="20" stroke="#f59e0b" stroke-width="1.5" stroke-linecap="round"/>
                <line x1="4"  y1="12" x2="5.5" y2="12" stroke="#f59e0b" stroke-width="1.5" stroke-linecap="round"/>
                <line x1="18.5" y1="12" x2="20" y2="12" stroke="#f59e0b" stroke-width="1.5" stroke-linecap="round"/>
                {{-- Aiguille des minutes (fixe à 10h) --}}
                <line x1="12" y1="12" x2="8.5" y2="7" stroke="#f59e0b" stroke-width="1.8" stroke-linecap="round"/>
                {{-- Aiguille des secondes (tourne) --}}
                <g class="clock-hand">
                    <line x1="12" y1="12" x2="12" y2="5.5" stroke="#fbbf24" stroke-width="1.2" stroke-linecap="round"/>
                </g>
                {{-- Centre --}}
                <circle cx="12" cy="12" r="1.2" fill="#f59e0b"/>
            </svg>
        </div>

        <div class="code">Erreur 419</div>

        <h1 class="title">Session expirée</h1>

        <p class="message">
            Votre session a expiré par <strong>inactivité</strong>.<br>
            Rechargez la page pour continuer où vous en étiez.
        </p>

        {{-- Explication ── --}}
        <div class="info-box">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="9" stroke="#f59e0b" stroke-width="1.8"/>
                <path d="M12 8v4M12 16h.01" stroke="#f59e0b" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
            <p>
                Pour des raisons de <strong>sécurité</strong>, les formulaires sont protégés par un
                jeton CSRF qui expire après une période d'inactivité.
                Rechargez simplement la page pour en obtenir un nouveau.
            </p>
        </div>

        {{-- Décompte auto-refresh ── --}}
        <div class="countdown-wrap">
            <div class="countdown-label">Rechargement automatique dans</div>
            <div class="countdown" id="countdown">30</div>
        </div>

        <div class="divider"></div>

        <div class="actions">
            <button onclick="window.location.reload()" class="btn-primary">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24">
                    <path d="M23 4v6h-6M1 20v-6h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Recharger maintenant
            </button>
            <a href="javascript:history.back()" class="btn-ghost">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Retour
            </a>
        </div>

        @auth
        @php
            $user   = auth()->user();
            $labels = ['super_admin'=>'Super Administrateur','admin'=>'Administrateur','fleet_manager'=>'Responsable Flotte','controller'=>'Contrôleur','director'=>'Directeur','collaborator'=>'Collaborateur','driver_user'=>'Chauffeur'];
            $roleName  = $user->getRoleNames()->first() ?? '';
            $roleLabel = $labels[$roleName] ?? $roleName;
        @endphp
        <div class="user-info">
            Connecté en tant que <strong>{{ $user->name }}</strong>
            @if($roleLabel) — rôle <strong>{{ $roleLabel }}</strong>@endif
        </div>
        @endauth
    </div>

    <script>
        // Décompte 30s puis rechargement automatique
        let s = 30;
        const el = document.getElementById('countdown');
        const timer = setInterval(() => {
            s--;
            el.textContent = s;
            if (s <= 0) { clearInterval(timer); window.location.reload(); }
        }, 1000);
    </script>

</body>
</html>
