<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Page introuvable · Miensa Fleet</title>
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
        .bg-orb-1 { width: 500px; height: 500px; background: #3b82f6; top: -120px; right: -80px; }
        .bg-orb-2 { width: 350px; height: 350px; background: #8b5cf6; bottom: -60px; left: -60px; }
        .bg-orb-3 { width: 200px; height: 200px; background: #06b6d4; top: 60%; left: 55%; }

        .bg-dots {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255,255,255,.04) 1px, transparent 1px);
            background-size: 28px 28px;
        }

        /* ── 404 flottant en arrière-plan ── */
        .bg-404 {
            position: absolute;
            font-size: clamp(140px, 22vw, 280px);
            font-weight: 900;
            color: rgba(59,130,246,.05);
            letter-spacing: -.04em;
            user-select: none;
            pointer-events: none;
            z-index: 1;
            line-height: 1;
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

        .icon-wrap {
            width: 88px;
            height: 88px;
            background: linear-gradient(135deg, rgba(59,130,246,.25), rgba(59,130,246,.1));
            border: 1.5px solid rgba(59,130,246,.35);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.75rem;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-6px); }
        }

        .code {
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .12em;
            color: #3b82f6;
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
            background: rgba(59,130,246,.4);
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
            margin-bottom: 2rem;
        }
        .message strong { color: #cbd5e1; }

        .url-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .3rem .75rem;
            background: rgba(59,130,246,.1);
            border: 1px solid rgba(59,130,246,.2);
            border-radius: 2rem;
            font-size: .72rem;
            font-weight: 500;
            color: #93c5fd;
            margin-bottom: 1.75rem;
            font-family: 'JetBrains Mono', 'Fira Code', ui-monospace, monospace;
            word-break: break-all;
            max-width: 100%;
        }

        .divider { height: 1px; background: rgba(255,255,255,.07); margin: 0 auto 1.75rem; }

        .actions { display: flex; gap: .75rem; justify-content: center; flex-wrap: wrap; }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .65rem 1.4rem;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
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
            background: linear-gradient(135deg,#3b82f6,#2563eb);
            border-radius: .45rem;
            display: flex; align-items: center; justify-content: center;
        }
        .logo-text { font-size: .92rem; font-weight: 800; color: #f1f5f9; letter-spacing: -.01em; }
        .logo-text span { color: #3b82f6; }

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
    <div class="bg-404">404</div>

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

        <div class="icon-wrap">
            <svg width="38" height="38" fill="none" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="7" stroke="#3b82f6" stroke-width="1.8"/>
                <path d="M21 21l-4.35-4.35" stroke="#3b82f6" stroke-width="1.8" stroke-linecap="round"/>
                <path d="M8.5 11h5M11 8.5v5" stroke="#3b82f6" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
        </div>

        <div class="code">Erreur 404</div>

        <h1 class="title">Page introuvable</h1>

        <p class="message">
            La page que vous cherchez <strong>n'existe pas</strong> ou a été déplacée.<br>
            Vérifiez l'URL ou revenez à la page précédente.
        </p>

        {{-- URL demandée ── --}}
        <div class="url-badge">
            <svg width="11" height="11" fill="none" viewBox="0 0 24 24"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            {{ request()->path() }}
        </div>

        <div class="divider"></div>

        <div class="actions">
            @auth
                <a href="{{ route('dashboard') }}" class="btn-primary">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/><rect x="14" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/><rect x="3" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/><rect x="14" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/></svg>
                    Tableau de bord
                </a>
            @else
                <a href="{{ route('login') }}" class="btn-primary">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Se connecter
                </a>
            @endauth
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

</body>
</html>
