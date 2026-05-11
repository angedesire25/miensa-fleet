{{--
    Barre de navigation mobile — visible uniquement ≤ 768px
    Affichée pour : driver_user, collaborator, controller
--}}
@php
    $mobileRoles = ['driver_user', 'collaborator', 'controller', 'super_admin', 'admin', 'fleet_manager'];
    $showMobileNav = auth()->check() && auth()->user()->hasAnyRole($mobileRoles);
    $currentRoute  = request()->route()?->getName() ?? '';
@endphp

@if($showMobileNav)
<style>
.mobile-nav {
    display: none;
    position: fixed;
    bottom: 0; left: 0; right: 0;
    z-index: 8000;
    background: #0f172a;
    border-top: 1px solid rgba(255,255,255,.08);
    padding-bottom: env(safe-area-inset-bottom, 0);
    box-shadow: 0 -4px 20px rgba(0,0,0,.4);
}
@media (max-width: 768px) {
    .mobile-nav { display: flex; }
    /* Ajouter de l'espace en bas du contenu pour éviter la superposition */
    main { padding-bottom: 64px !important; }
}
.mobile-nav-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 8px 4px 6px;
    text-decoration: none;
    color: #64748b;
    font-size: 10px;
    font-weight: 500;
    transition: color .15s;
    min-height: 56px;
    gap: 3px;
    -webkit-tap-highlight-color: transparent;
}
.mobile-nav-item.active,
.mobile-nav-item:active {
    color: #10b981;
}
.mobile-nav-item svg {
    width: 22px; height: 22px;
    transition: transform .15s;
}
.mobile-nav-item.active svg {
    transform: scale(1.1);
}
.mobile-nav-dot {
    width: 4px; height: 4px;
    border-radius: 50%;
    background: #10b981;
    margin-top: 2px;
    opacity: 0;
    transition: opacity .15s;
}
.mobile-nav-item.active .mobile-nav-dot { opacity: 1; }
</style>

<nav class="mobile-nav" role="navigation" aria-label="Navigation mobile">

    {{-- 🏠 Accueil --}}
    <a href="{{ route('dashboard') }}"
       class="mobile-nav-item {{ str_starts_with($currentRoute, 'dashboard') ? 'active' : '' }}">
        <svg fill="none" viewBox="0 0 24 24">
            <path d="M3 12L12 3l9 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M9 21V12h6v9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            <path d="M5 10v11h14V10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <span>Accueil</span>
        <div class="mobile-nav-dot"></div>
    </a>

    {{-- 📋 Fiche de contrôle --}}
    @can('inspections.create')
    <a href="{{ route('inspections.create') }}"
       class="mobile-nav-item {{ str_starts_with($currentRoute, 'inspections') ? 'active' : '' }}">
        <svg fill="none" viewBox="0 0 24 24">
            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            <rect x="9" y="3" width="6" height="4" rx="1" stroke="currentColor" stroke-width="2"/>
            <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
        </svg>
        <span>Contrôle</span>
        <div class="mobile-nav-dot"></div>
    </a>
    @endcan

    {{-- 📅 Affectations --}}
    @can('assignments.view')
    <a href="{{ route('assignments.index') }}"
       class="mobile-nav-item {{ str_starts_with($currentRoute, 'assignments') ? 'active' : '' }}">
        <svg fill="none" viewBox="0 0 24 24">
            <rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
            <path d="M16 2v4M8 2v4M3 10h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            <path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <span>Affectations</span>
        <div class="mobile-nav-dot"></div>
    </a>
    @endcan

    {{-- 👤 Profil --}}
    <a href="{{ route('profile.edit') }}"
       class="mobile-nav-item {{ $currentRoute === 'profile.edit' ? 'active' : '' }}">
        <svg fill="none" viewBox="0 0 24 24">
            <circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="2"/>
            <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <span>Profil</span>
        <div class="mobile-nav-dot"></div>
    </a>

</nav>
@endif
