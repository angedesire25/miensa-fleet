<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Tableau de bord') — Miensa Fleet</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; background: #f1f5f9; }

        /* ── Sidebar ─────────────────────────────────────────────────── */
        .sidebar {
            position: fixed; top: 0; left: 0; bottom: 0; width: 256px;
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            display: flex; flex-direction: column; z-index: 50;
            overflow-y: auto;
        }
        .sidebar-logo {
            padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,.07);
            display: flex; align-items: center; gap: .65rem;
        }
        .sidebar-logo-icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg,#10b981,#059669);
            border-radius: .5rem; display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .sidebar-logo-text { color: #fff; font-size: 1.15rem; font-weight: 700; }
        .sidebar-logo-text span { color: #10b981; }

        .nav-section { padding: .75rem 1rem .25rem; }
        .nav-label {
            color: rgba(255,255,255,.3); font-size: .65rem;
            font-weight: 600; letter-spacing: .08em; text-transform: uppercase;
            padding: 0 .5rem; margin-bottom: .35rem;
        }
        .nav-item {
            display: flex; align-items: center; gap: .7rem;
            padding: .6rem .75rem; border-radius: .45rem; margin-bottom: .1rem;
            color: rgba(255,255,255,.58); font-size: .875rem; font-weight: 500;
            text-decoration: none; transition: background .15s, color .15s;
            cursor: pointer;
        }
        .nav-item:hover { background: rgba(255,255,255,.07); color: rgba(255,255,255,.9); }
        .nav-item.active { background: rgba(16,185,129,.18); color: #10b981; }
        .nav-item.active .nav-icon { color: #10b981; }
        .nav-icon { width: 18px; height: 18px; flex-shrink: 0; }
        .nav-badge {
            margin-left: auto; background: #ef4444; color: #fff;
            font-size: .65rem; font-weight: 700; padding: .1rem .4rem;
            border-radius: 99px; min-width: 18px; text-align: center;
        }

        /* ── Topbar ──────────────────────────────────────────────────── */
        .topbar {
            position: fixed; top: 0; left: 256px; right: 0; height: 60px;
            background: #fff; border-bottom: 1px solid #e2e8f0;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 1.5rem; z-index: 40;
        }
        .topbar-title { font-size: 1rem; font-weight: 600; color: #0f172a; }
        .topbar-right { display: flex; align-items: center; gap: 1rem; }
        .topbar-btn {
            position: relative; background: none; border: none; cursor: pointer;
            padding: .4rem; border-radius: .4rem; color: #64748b;
            transition: background .15s;
        }
        .topbar-btn:hover { background: #f1f5f9; color: #0f172a; }
        .topbar-notif-dot {
            position: absolute; top: 4px; right: 4px; width: 8px; height: 8px;
            background: #ef4444; border-radius: 50%; border: 2px solid #fff;
        }
        .avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: linear-gradient(135deg,#10b981,#059669);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: .8rem; font-weight: 700; cursor: pointer;
        }

        /* ── Main content ─────────────────────────────────────────────── */
        .main { margin-left: 256px; padding-top: 60px; min-height: 100vh; }
        .page-content { padding: 1.75rem; }
    </style>
</head>
<body>

{{-- ── SIDEBAR ──────────────────────────────────────────────────────────── --}}
<aside class="sidebar">

    {{-- Logo --}}
    <div class="sidebar-logo">
        <div class="sidebar-logo-icon">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24">
                <path d="M3 17h2l1-3h12l1 3h2" stroke="white" stroke-width="1.8" stroke-linecap="round"/>
                <circle cx="7.5" cy="18.5" r="1.5" fill="white"/>
                <circle cx="16.5" cy="18.5" r="1.5" fill="white"/>
                <path d="M6.5 9l1-3h9l1 3" stroke="white" stroke-width="1.5" fill="none"/>
            </svg>
        </div>
        <span class="sidebar-logo-text">Miensa<span>Fleet</span></span>
    </div>

    {{-- Navigation principale filtrée par permissions ──────────────────── --}}
    <div class="nav-section" style="flex:1;">

        @php
            $user = auth()->user();
            // Pré-calcul des groupes visibles pour éviter d'afficher
            // un label de section sans aucun élément en dessous
            $showFlotte      = $user->canAny(['vehicles.view','drivers.view','assignments.view','vehicle_requests.view']);
            $showMaintenance = $user->canAny(['incidents.view','repairs.view','garages.view']);
            $showSuivi       = $user->canAny(['alerts.view','reports.view','infractions.view']);

            // Badge alertes (calculé une seule fois)
            $newAlerts = \App\Models\Alert::where('status','new')->count();

            // Badge contrôles : fiches du jour en brouillon ou soumises (non encore validées)
            $pendingControls = 0;
            if (class_exists(\App\Models\Inspection::class)) {
                $pendingControls = \App\Models\Inspection::whereDate('inspected_at', today())
                    ->whereIn('status', ['draft', 'submitted'])
                    ->when(!$user->hasAnyRole(['super_admin','admin','fleet_manager','controller','director']),
                        fn($q) => $q->where('inspector_id', $user->id))
                    ->count();
            }

            // Libellé rôle en français
            $roleLabels = [
                'super_admin'  => 'Super Administrateur',
                'admin'        => 'Administrateur',
                'fleet_manager'=> 'Responsable Flotte',
                'controller'   => 'Contrôleur',
                'director'     => 'Directeur',
                'collaborator' => 'Collaborateur',
                'driver_user'  => 'Chauffeur',
            ];
            $roleName  = $user->getRoleNames()->first() ?? '';
            $roleLabel = $roleLabels[$roleName] ?? $roleName;
        @endphp

        {{-- ── PRINCIPAL (toujours visible) ─────────────────────────────── --}}
        <div class="nav-label">Principal</div>
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8"/><rect x="14" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8"/><rect x="3" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8"/><rect x="14" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8"/></svg>
            Tableau de bord
        </a>

        {{-- Contrôles journaliers — visible par tous les rôles --}}
        <a href="{{ route('inspections.index') }}" class="nav-item {{ request()->routeIs('inspections.*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24">
                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                <rect x="9" y="3" width="6" height="4" rx="1" stroke="currentColor" stroke-width="1.8"/>
                <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Contrôles
            @if($pendingControls > 0)
                <span class="nav-badge" style="background:#f59e0b;">{{ $pendingControls > 99 ? '99+' : $pendingControls }}</span>
            @endif
        </a>

        {{-- ── FLOTTE ─────────────────────────────────────────────────────── --}}
        @if($showFlotte)
            <div class="nav-label" style="margin-top:.75rem;">Flotte</div>

            @can('vehicles.view')
            <a href="{{ route('vehicles.index') }}" class="nav-item {{ request()->routeIs('vehicles.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24"><path d="M3 17h2l1-3h12l1 3h2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="7.5" cy="18.5" r="1.5" stroke="currentColor" stroke-width="1.5"/><circle cx="16.5" cy="18.5" r="1.5" stroke="currentColor" stroke-width="1.5"/><path d="M6.5 9l1-3h9l1 3" stroke="currentColor" stroke-width="1.5" fill="none"/></svg>
                Véhicules
            </a>
            @endcan

            @can('drivers.view')
            <a href="{{ route('drivers.index') }}" class="nav-item {{ request()->routeIs('drivers.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="1.8"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                Chauffeurs
            </a>
            @endcan

            @can('assignments.view')
            <a href="{{ route('assignments.index') }}" class="nav-item {{ request()->routeIs('assignments.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M8 2v4M16 2v4M3 10h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                Affectations
            </a>
            @endcan

            @can('vehicle_requests.view')
            <a href="{{ route('requests.index') }}" class="nav-item {{ request()->routeIs('requests.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M20 12c0 4.418-3.582 8-8 8s-8-3.582-8-8 3.582-8 8-8 8 3.582 8 8z" stroke="currentColor" stroke-width="1.8"/></svg>
                Demandes
            </a>
            @endcan
        @endif

        {{-- ── MAINTENANCE ─────────────────────────────────────────────────── --}}
        @if($showMaintenance)
            <div class="nav-label" style="margin-top:.75rem;">Maintenance</div>

            @can('incidents.view')
            <a href="#" class="nav-item {{ request()->routeIs('incidents.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="1.8"/></svg>
                Sinistres
            </a>
            @endcan

            @can('repairs.view')
            <a href="#" class="nav-item {{ request()->routeIs('repairs.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.77 3.77z" stroke="currentColor" stroke-width="1.8"/></svg>
                Réparations
            </a>
            @endcan

            @can('garages.view')
            <a href="#" class="nav-item {{ request()->routeIs('garages.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke="currentColor" stroke-width="1.8"/><polyline points="9,22 9,12 15,12 15,22" stroke="currentColor" stroke-width="1.8"/></svg>
                Garages
            </a>
            @endcan
        @endif

        {{-- ── SUIVI ───────────────────────────────────────────────────────── --}}
        @if($showSuivi)
            <div class="nav-label" style="margin-top:.75rem;">Suivi</div>

            @can('alerts.view')
            <a href="#" class="nav-item {{ request()->routeIs('alerts.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="1.8"/><path d="M13.73 21a2 2 0 01-3.46 0" stroke="currentColor" stroke-width="1.8"/></svg>
                Alertes
                @if($newAlerts > 0)
                    <span class="nav-badge">{{ $newAlerts > 99 ? '99+' : $newAlerts }}</span>
                @endif
            </a>
            @endcan

            @can('reports.view')
            <a href="#" class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24"><path d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" stroke="currentColor" stroke-width="1.8"/></svg>
                Rapports
            </a>
            @endcan

            @can('infractions.view')
            <a href="#" class="nav-item {{ request()->routeIs('infractions.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 000 4h6a2 2 0 000-4M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke="currentColor" stroke-width="1.8"/></svg>
                Infractions
            </a>
            @endcan
        @endif

        {{-- ── ADMINISTRATION (super_admin & admin uniquement) ─────────────── --}}
        @if($user->hasAnyRole(['super_admin','admin']))
            <div class="nav-label" style="margin-top:.75rem;">Administration</div>
            <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="1.8"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="1.8"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke="currentColor" stroke-width="1.8"/></svg>
                Utilisateurs
                @php $totalUsers = \App\Models\User::where('status','suspended')->count(); @endphp
                @if($totalUsers > 0)
                    <span class="nav-badge" style="background:#f59e0b;">{{ $totalUsers }}</span>
                @endif
            </a>
        @endif

    </div>

    {{-- Utilisateur connecté --}}
    <div style="padding:.75rem 1rem;border-top:1px solid rgba(255,255,255,.07);">
        <a href="{{ route('profile.edit') }}"
           style="display:flex;align-items:center;gap:.7rem;padding:.45rem .5rem;border-radius:.45rem;text-decoration:none;transition:background .15s;{{ request()->routeIs('profile.*') ? 'background:rgba(16,185,129,.15);' : '' }}"
           onmouseover="this.style.background='rgba(255,255,255,.06)'"
           onmouseout="this.style.background='{{ request()->routeIs('profile.*') ? 'rgba(16,185,129,.15)' : 'transparent' }}'">
            @if(auth()->user()->avatar)
                <img src="{{ Storage::url(auth()->user()->avatar) }}"
                     style="width:34px;height:34px;border-radius:50%;object-fit:cover;border:2px solid rgba(16,185,129,.5);flex-shrink:0;"
                     alt="">
            @else
                <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#10b981,#059669);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.75rem;font-weight:700;flex-shrink:0;">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                </div>
            @endif
            <div style="flex:1;min-width:0;">
                <div style="color:#fff;font-size:.8rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ auth()->user()->name ?? 'Utilisateur' }}</div>
                <div style="color:rgba(255,255,255,.4);font-size:.7rem;">{{ $roleLabel }}</div>
            </div>
        </a>
        <form method="POST" action="{{ route('logout') }}" style="margin-top:.3rem;">
            @csrf
            <button type="submit"
                    style="width:100%;display:flex;align-items:center;gap:.6rem;padding:.45rem .5rem;border-radius:.45rem;background:none;border:none;cursor:pointer;color:rgba(255,255,255,.35);font-size:.8rem;transition:color .15s,background .15s;text-align:left;"
                    onmouseover="this.style.color='#ef4444';this.style.background='rgba(239,68,68,.08)'"
                    onmouseout="this.style.color='rgba(255,255,255,.35)';this.style.background='transparent'">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                Déconnexion
            </button>
        </form>
    </div>

</aside>

{{-- ── TOPBAR ───────────────────────────────────────────────────────────── --}}
<header class="topbar">
    <div>
        <span class="topbar-title">@yield('page-title', 'Tableau de bord')</span>
        @hasSection('breadcrumb')
            <span style="color:#94a3b8;margin:0 .4rem;">·</span>
            <span style="color:#94a3b8;font-size:.85rem;">@yield('breadcrumb')</span>
        @endif
    </div>
    <div class="topbar-right">
        {{-- Notifications --}}
        <button class="topbar-btn" title="Alertes">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="1.8"/><path d="M13.73 21a2 2 0 01-3.46 0" stroke="currentColor" stroke-width="1.8"/></svg>
            @if(isset($alertsCount) && $alertsCount > 0)
                <span class="topbar-notif-dot"></span>
            @endif
        </button>
        {{-- Paramètres --}}
        <button class="topbar-btn" title="Paramètres">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z" stroke="currentColor" stroke-width="1.8"/></svg>
        </button>
        {{-- Avatar cliquable → profil --}}
        <a href="{{ route('profile.edit') }}" title="Mon profil" style="text-decoration:none;">
            @if(auth()->user()->avatar)
                <img src="{{ Storage::url(auth()->user()->avatar) }}"
                     style="width:34px;height:34px;border-radius:50%;object-fit:cover;border:2px solid #e2e8f0;transition:border-color .15s;"
                     onmouseover="this.style.borderColor='#10b981'"
                     onmouseout="this.style.borderColor='#e2e8f0'"
                     alt="">
            @else
                <div class="avatar" style="border:2px solid transparent;transition:border-color .15s;"
                     onmouseover="this.style.borderColor='#10b981'"
                     onmouseout="this.style.borderColor='transparent'">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                </div>
            @endif
        </a>
    </div>
</header>

{{-- ── MAIN CONTENT ─────────────────────────────────────────────────────── --}}
<main class="main">
    <div class="page-content">
        @yield('content')
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ── Configuration globale SweetAlert2 ──────────────────────────────────────
const SwalToast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 4500,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
});

const SwalConfirm = Swal.mixin({
    confirmButtonColor: '#10b981',
    cancelButtonColor:  '#64748b',
    confirmButtonText:  'Confirmer',
    cancelButtonText:   'Annuler',
    showCancelButton:   true,
    reverseButtons:     true,
    focusCancel:        true,
});

// ── Affichage automatique des messages flash ───────────────────────────────
@if(session('swal_success'))
SwalToast.fire({ icon: 'success', title: @json(session('swal_success')) });
@endif
@if(session('swal_error'))
SwalToast.fire({ icon: 'error', title: @json(session('swal_error')), timer: 6000 });
@endif
@if(session('swal_warning'))
SwalToast.fire({ icon: 'warning', title: @json(session('swal_warning')), timer: 6000 });
@endif
@if(session('swal_info'))
SwalToast.fire({ icon: 'info', title: @json(session('swal_info')) });
@endif
@if(session('success'))
SwalToast.fire({ icon: 'success', title: @json(session('success')) });
@endif
@if(session('error'))
SwalToast.fire({ icon: 'error', title: @json(session('error')), timer: 6000 });
@endif

// Erreurs de validation groupées
@if($errors->any())
SwalToast.fire({
    icon:  'error',
    title: 'Formulaire invalide',
    text:  @json($errors->first()),
    timer: 6000,
});
@endif

// ── Gestionnaire de confirmations (data-confirm="texte") ───────────────────
document.addEventListener('DOMContentLoaded', () => {

    // Formulaires avec attribut data-confirm
    document.querySelectorAll('form[data-confirm]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const msg   = this.dataset.confirm   || 'Confirmer cette action ?';
            const title = this.dataset.title     || 'Confirmation';
            const icon  = this.dataset.icon      || 'warning';
            const btn   = this.dataset.btnText   || 'Confirmer';
            const color = this.dataset.btnColor  || '#ef4444';

            SwalConfirm.fire({
                title,
                text: msg,
                icon,
                confirmButtonText: btn,
                confirmButtonColor: color,
            }).then(result => {
                if (result.isConfirmed) HTMLFormElement.prototype.submit.call(this);
            });
        });
    });

    // Liens/boutons avec attribut data-confirm (cas rares)
    document.querySelectorAll('[data-swal-confirm]:not(form)').forEach(el => {
        el.addEventListener('click', function(e) {
            e.preventDefault();
            const href = this.getAttribute('href') || '#';
            SwalConfirm.fire({
                title: 'Confirmation',
                text:  this.dataset.swalConfirm,
                icon:  'warning',
            }).then(result => {
                if (result.isConfirmed) window.location.href = href;
            });
        });
    });
});
</script>
</body>
</html>
