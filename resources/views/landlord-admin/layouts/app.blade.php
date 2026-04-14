<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — MiensaFleet</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; background: #0f172a; color: #e2e8f0; }

        .admin-layout { display: flex; min-height: 100vh; }

        /* ── Sidebar ──────────────────────────────────────────────────── */
        .admin-sidebar {
            width: 240px; flex-shrink: 0;
            background: #0a0f1e;
            border-right: 1px solid rgba(255,255,255,.06);
            display: flex; flex-direction: column;
            position: fixed; top: 0; left: 0; bottom: 0;
            overflow-y: auto;
        }
        .admin-logo {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,.06);
            display: flex; align-items: center; gap: .65rem;
        }
        .admin-logo-badge {
            width: 32px; height: 32px;
            background: linear-gradient(135deg,#ef4444,#b91c1c);
            border-radius: 8px; display: flex; align-items: center; justify-content: center;
            font-size: .7rem; font-weight: 800; color: white; letter-spacing: .02em;
        }
        .admin-logo-text { font-size: .85rem; font-weight: 700; color: #f1f5f9; line-height: 1.2; }
        .admin-logo-sub  { font-size: .68rem; color: #94a3b8; font-weight: 400; }

        .admin-nav { padding: 1rem 0; flex: 1; }
        .admin-nav-label {
            font-size: .65rem; font-weight: 700; color: #475569;
            text-transform: uppercase; letter-spacing: .08em;
            padding: .5rem 1.25rem .25rem;
        }
        .admin-nav a {
            display: flex; align-items: center; gap: .65rem;
            padding: .55rem 1.25rem; font-size: .85rem; color: #94a3b8;
            text-decoration: none; border-radius: 0; transition: background .15s, color .15s;
            border-left: 2px solid transparent;
        }
        .admin-nav a:hover { background: rgba(255,255,255,.04); color: #e2e8f0; }
        .admin-nav a.active { background: rgba(239,68,68,.08); color: #fca5a5; border-left-color: #ef4444; }
        .admin-nav a svg { flex-shrink: 0; }

        .admin-user {
            padding: 1rem 1.25rem;
            border-top: 1px solid rgba(255,255,255,.06);
            display: flex; align-items: center; gap: .65rem;
        }
        .admin-user-avatar {
            width: 32px; height: 32px; border-radius: 50%;
            background: linear-gradient(135deg,#7c3aed,#4f46e5);
            display: flex; align-items: center; justify-content: center;
            font-size: .75rem; font-weight: 700; color: white; flex-shrink: 0;
        }
        .admin-user-info { flex: 1; min-width: 0; }
        .admin-user-name { font-size: .8rem; font-weight: 600; color: #f1f5f9; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .admin-user-role { font-size: .7rem; color: #64748b; }
        .admin-logout { color: #475569; transition: color .15s; flex-shrink: 0; }
        .admin-logout:hover { color: #ef4444; }

        /* ── Main ──────────────────────────────────────────────────────── */
        .admin-main { margin-left: 240px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }

        .admin-topbar {
            background: rgba(15,23,42,.8); backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(255,255,255,.06);
            padding: .75rem 2rem; display: flex; align-items: center;
            position: sticky; top: 0; z-index: 40;
        }
        .admin-topbar h1 { font-size: 1rem; font-weight: 600; color: #f1f5f9; margin: 0; }
        .admin-breadcrumb { font-size: .8rem; color: #64748b; margin-left: .75rem; }

        .admin-content { padding: 2rem; flex: 1; }

        /* ── Cards ─────────────────────────────────────────────────────── */
        .a-card {
            background: #1e293b; border: 1px solid rgba(255,255,255,.07);
            border-radius: 12px; padding: 1.5rem;
        }
        .a-card-title { font-size: .75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: .06em; margin: 0 0 .35rem; }
        .a-card-value { font-size: 2rem; font-weight: 800; color: #f1f5f9; margin: 0; }
        .a-card-sub   { font-size: .8rem; color: #64748b; margin-top: .25rem; }

        /* ── Alerts ────────────────────────────────────────────────────── */
        .a-alert-success { background: rgba(34,197,94,.1); border: 1px solid rgba(34,197,94,.2); border-radius: 8px; padding: .75rem 1rem; font-size: .87rem; color: #86efac; margin-bottom: 1rem; }
        .a-alert-error   { background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.2); border-radius: 8px; padding: .75rem 1rem; font-size: .87rem; color: #fca5a5; margin-bottom: 1rem; }

        /* ── Table ─────────────────────────────────────────────────────── */
        .a-table { width: 100%; border-collapse: collapse; font-size: .87rem; }
        .a-table th { padding: .65rem 1rem; text-align: left; font-size: .72rem; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: .05em; border-bottom: 1px solid rgba(255,255,255,.06); }
        .a-table td { padding: .75rem 1rem; border-bottom: 1px solid rgba(255,255,255,.04); color: #94a3b8; }
        .a-table tr:last-child td { border-bottom: none; }
        .a-table tr:hover td { background: rgba(255,255,255,.02); }

        /* ── Badges ────────────────────────────────────────────────────── */
        .badge { display: inline-flex; align-items: center; padding: .2rem .6rem; border-radius: 20px; font-size: .72rem; font-weight: 600; }
        .badge-green  { background: rgba(34,197,94,.15);  color: #86efac; }
        .badge-yellow { background: rgba(234,179,8,.15);  color: #fde047; }
        .badge-red    { background: rgba(239,68,68,.15);  color: #fca5a5; }
        .badge-slate  { background: rgba(100,116,139,.15); color: #94a3b8; }

        /* ── Buttons ───────────────────────────────────────────────────── */
        .btn-sm { padding: .3rem .75rem; border-radius: 6px; font-size: .8rem; font-weight: 600; cursor: pointer; border: none; text-decoration: none; display: inline-flex; align-items: center; gap: .35rem; transition: opacity .15s; }
        .btn-sm:hover { opacity: .85; }
        .btn-red    { background: rgba(239,68,68,.15); color: #fca5a5; }
        .btn-green  { background: rgba(34,197,94,.15); color: #86efac; }
        .btn-slate  { background: rgba(100,116,139,.15); color: #94a3b8; }
        .btn-primary { background: #3b82f6; color: white; }
    </style>
    @stack('styles')
</head>
<body>
<div class="admin-layout">

    {{-- ── Sidebar ───────────────────────────────────────────────────── --}}
    <aside class="admin-sidebar">
        <div class="admin-logo">
            <div class="admin-logo-badge">MF</div>
            <div>
                <div class="admin-logo-text">MiensaFleet</div>
                <div class="admin-logo-sub">Panel propriétaire</div>
            </div>
        </div>

        <nav class="admin-nav">
            <div class="admin-nav-label">Vue d'ensemble</div>
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                Tableau de bord
            </a>

            <div class="admin-nav-label" style="margin-top:.5rem;">Clients</div>
            <a href="{{ route('admin.tenants.index') }}" class="{{ request()->routeIs('admin.tenants.*') ? 'active' : '' }}">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Tenants
            </a>
        </nav>

        <div class="admin-user">
            <div class="admin-user-avatar">
                {{ strtoupper(substr(Auth::guard('landlord')->user()?->name ?? 'A', 0, 1)) }}
            </div>
            <div class="admin-user-info">
                <div class="admin-user-name">{{ Auth::guard('landlord')->user()?->name }}</div>
                <div class="admin-user-role">Super Admin</div>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="admin-logout" title="Déconnexion" style="background:none;border:none;cursor:pointer;padding:.25rem;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </button>
            </form>
        </div>
    </aside>

    {{-- ── Contenu ────────────────────────────────────────────────────── --}}
    <main class="admin-main">
        <div class="admin-topbar">
            <h1>@yield('page-title', 'Dashboard')</h1>
            @hasSection('breadcrumb')
                <span class="admin-breadcrumb">/ @yield('breadcrumb')</span>
            @endif
        </div>

        <div class="admin-content">
            @if(session('success'))
                <div class="a-alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="a-alert-error">{{ session('error') }}</div>
            @endif

            @yield('content')
        </div>
    </main>
</div>
@stack('scripts')
</body>
</html>
