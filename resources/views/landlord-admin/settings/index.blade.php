@extends('landlord-admin.layouts.app')

@section('title', 'Paramètres du site')
@section('page-title', 'Paramètres')
@section('breadcrumb', 'Site public')

@push('styles')
<style>
    .settings-grid { display: grid; grid-template-columns: 220px 1fr; gap: 1.5rem; align-items: start; }
    .settings-nav  { position: sticky; top: 80px; }
    .settings-nav a {
        display: flex; align-items: center; gap: .55rem;
        padding: .5rem .85rem; border-radius: 6px; font-size: .83rem;
        color: #94a3b8; text-decoration: none; transition: background .15s, color .15s;
        margin-bottom: .15rem;
    }
    .settings-nav a:hover { background: rgba(255,255,255,.04); color: #e2e8f0; }
    .settings-nav a.active { background: rgba(59,130,246,.1); color: #93c5fd; }

    .settings-section {
        background: #1e293b; border: 1px solid rgba(255,255,255,.07);
        border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem;
    }
    .settings-section-title {
        font-size: .8rem; font-weight: 700; color: #475569;
        text-transform: uppercase; letter-spacing: .07em;
        margin: 0 0 1.25rem; padding-bottom: .75rem;
        border-bottom: 1px solid rgba(255,255,255,.06);
        display: flex; align-items: center; gap: .5rem;
    }
    .field-group { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .field-group.full { grid-template-columns: 1fr; }
    .field { display: flex; flex-direction: column; gap: .35rem; }
    .field label { font-size: .82rem; font-weight: 600; color: #94a3b8; }
    .field input[type="text"],
    .field input[type="email"],
    .field input[type="url"],
    .field input[type="tel"],
    .field textarea {
        background: #0f172a; border: 1px solid rgba(255,255,255,.1);
        border-radius: 7px; padding: .55rem .85rem;
        color: #e2e8f0; font-size: .875rem; font-family: inherit;
        transition: border-color .15s; outline: none; width: 100%; box-sizing: border-box;
    }
    .field input:focus, .field textarea:focus { border-color: #3b82f6; }
    .field textarea { resize: vertical; min-height: 80px; }
    .field .hint { font-size: .75rem; color: #475569; margin-top: .2rem; }

    .logo-preview {
        display: flex; align-items: center; gap: 1rem;
        background: #0f172a; border: 1px solid rgba(255,255,255,.08);
        border-radius: 8px; padding: .75rem 1rem; margin-bottom: .75rem;
    }
    .logo-preview img { max-height: 48px; max-width: 160px; object-fit: contain; }
    .logo-placeholder {
        width: 48px; height: 48px; border-radius: 8px;
        background: linear-gradient(135deg,#3b82f6,#1d4ed8);
        display: flex; align-items: center; justify-content: center;
        font-size: .75rem; font-weight: 800; color: white;
    }

    .toggle-wrap {
        display: flex; align-items: center; gap: .75rem;
        background: #0f172a; border: 1px solid rgba(255,255,255,.08);
        border-radius: 8px; padding: .75rem 1rem;
    }
    .toggle-wrap label { font-size: .875rem; color: #94a3b8; cursor: pointer; }
    input[type="checkbox"].toggle { width: 16px; height: 16px; accent-color: #ef4444; cursor: pointer; }

    .save-bar {
        position: sticky; bottom: 0;
        background: rgba(15,23,42,.95); backdrop-filter: blur(8px);
        border-top: 1px solid rgba(255,255,255,.06);
        padding: 1rem 0; display: flex; justify-content: flex-end; gap: .75rem;
        margin: 0 -2rem -2rem; padding: 1rem 2rem;
        z-index: 30;
    }
    .btn-save {
        background: linear-gradient(135deg,#3b82f6,#1d4ed8); color: white;
        border: none; border-radius: 8px; padding: .6rem 1.5rem;
        font-size: .88rem; font-weight: 600; cursor: pointer; transition: opacity .15s;
    }
    .btn-save:hover { opacity: .9; }

    /* Compense la topbar sticky lors du scroll vers une ancre */
    .settings-section { scroll-margin-top: 80px; }

    @media (max-width: 900px) {
        .settings-grid { grid-template-columns: 1fr; }
        .settings-nav { position: static; display: flex; gap: .25rem; flex-wrap: wrap; }
        .field-group { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')

<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
@csrf
@method('PUT')

<div class="settings-grid">

    {{-- ── Navigation latérale ──────────────────────────────────────── --}}
    <nav class="settings-nav a-card" style="padding:1rem;">
        <div style="font-size:.7rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.07em;padding:.25rem .85rem .5rem;">Sections</div>
        <a href="#identite" class="active">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
            Identité
        </a>
        <a href="#contact">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.15 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.06 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21 16.92z"/></svg>
            Contact
        </a>
        <a href="#reseaux">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
            Réseaux sociaux
        </a>
        <a href="#landing">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
            Page d'accueil
        </a>
        <a href="#maintenance">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Maintenance
        </a>
    </nav>

    {{-- ── Formulaire ────────────────────────────────────────────────── --}}
    <div>

        {{-- IDENTITÉ ─────────────────────────────────────────────────── --}}
        <div class="settings-section" id="identite">
            <h2 class="settings-section-title">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
                Identité du site
            </h2>

            {{-- Logo --}}
            <div class="field" style="margin-bottom:1rem;">
                <label>Logo du site</label>
                <div class="logo-preview">
                    @if(!empty($s['logo_path']))
                        <img src="{{ asset('storage/'.$s['logo_path']) }}" alt="Logo actuel" id="logoImg">
                        <div>
                            <div style="font-size:.82rem;color:#e2e8f0;font-weight:600;">Logo actuel</div>
                            <label style="display:inline-flex;align-items:center;gap:.4rem;margin-top:.35rem;cursor:pointer;">
                                <input type="checkbox" name="remove_logo" value="1" style="accent-color:#ef4444;">
                                <span style="font-size:.78rem;color:#fca5a5;">Supprimer ce logo</span>
                            </label>
                        </div>
                    @else
                        <div class="logo-placeholder" id="logoImg">MF</div>
                        <div style="font-size:.82rem;color:#64748b;">Aucun logo personnalisé</div>
                    @endif
                </div>
                <input type="file" name="logo" accept="image/*" id="logoInput"
                       style="background:#0f172a;border:1px solid rgba(255,255,255,.1);border-radius:7px;padding:.5rem;font-size:.82rem;color:#94a3b8;width:100%;box-sizing:border-box;"
                       onchange="previewLogo(this)">
                <span class="hint">PNG, SVG ou JPG · max 2 Mo. Format recommandé : 200×50 px, fond transparent.</span>
            </div>

            <div class="field-group">
                <div class="field">
                    <label>Nom du site <span style="color:#ef4444">*</span></label>
                    <input type="text" name="site_name" value="{{ $s['site_name'] ?? 'MiensaFleet' }}" required maxlength="80">
                    <span class="hint">Affiché dans la barre de titre et le header.</span>
                </div>
                <div class="field">
                    <label>Slogan</label>
                    <input type="text" name="site_tagline" value="{{ $s['site_tagline'] ?? '' }}" maxlength="150"
                           placeholder="Ex: La gestion de flotte intelligente">
                </div>
            </div>

            <div class="field" style="margin-top:1rem;">
                <label>Meta description (SEO)</label>
                <textarea name="site_description" maxlength="300"
                          placeholder="Description courte affichée par Google (150–160 caractères recommandé)">{{ $s['site_description'] ?? '' }}</textarea>
                <span class="hint">Utilisée pour le référencement. 150–160 caractères idéalement.</span>
            </div>
        </div>

        {{-- CONTACT ──────────────────────────────────────────────────── --}}
        <div class="settings-section" id="contact">
            <h2 class="settings-section-title">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.15 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.06 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21 16.92z"/></svg>
                Informations de contact
            </h2>
            <div class="field-group">
                <div class="field">
                    <label>Email de contact</label>
                    <input type="email" name="contact_email" value="{{ $s['contact_email'] ?? '' }}"
                           placeholder="contact@miensafleet.ci" maxlength="120">
                </div>
                <div class="field">
                    <label>Téléphone</label>
                    <input type="tel" name="contact_phone" value="{{ $s['contact_phone'] ?? '' }}"
                           placeholder="+225 07 XX XX XX XX" maxlength="30">
                </div>
                <div class="field">
                    <label>WhatsApp (numéro)</label>
                    <input type="tel" name="contact_whatsapp" value="{{ $s['contact_whatsapp'] ?? '' }}"
                           placeholder="+225 07 XX XX XX XX" maxlength="30">
                    <span class="hint">Utilisé pour le bouton WhatsApp si affiché.</span>
                </div>
                <div class="field">
                    <label>Adresse</label>
                    <input type="text" name="contact_address" value="{{ $s['contact_address'] ?? '' }}"
                           placeholder="Abidjan, Côte d'Ivoire" maxlength="200">
                </div>
            </div>
        </div>

        {{-- RÉSEAUX SOCIAUX ───────────────────────────────────────────── --}}
        <div class="settings-section" id="reseaux">
            <h2 class="settings-section-title">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                Réseaux sociaux
            </h2>
            <div class="field-group">
                <div class="field">
                    <label>Facebook</label>
                    <input type="url" name="social_facebook" value="{{ $s['social_facebook'] ?? '' }}"
                           placeholder="https://facebook.com/miensafleet" maxlength="250">
                </div>
                <div class="field">
                    <label>LinkedIn</label>
                    <input type="url" name="social_linkedin" value="{{ $s['social_linkedin'] ?? '' }}"
                           placeholder="https://linkedin.com/company/miensafleet" maxlength="250">
                </div>
                <div class="field">
                    <label>Twitter / X</label>
                    <input type="url" name="social_twitter" value="{{ $s['social_twitter'] ?? '' }}"
                           placeholder="https://twitter.com/miensafleet" maxlength="250">
                </div>
            </div>
        </div>

        {{-- PAGE D'ACCUEIL ────────────────────────────────────────────── --}}
        <div class="settings-section" id="landing">
            <h2 class="settings-section-title">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                Page d'accueil publique
            </h2>

            <div class="field" style="margin-bottom:1rem;">
                <label>Titre principal (Hero)</label>
                <input type="text" name="hero_title" value="{{ $s['hero_title'] ?? '' }}"
                       placeholder="La gestion de flotte à portée de toutes les entreprises" maxlength="120">
                <span class="hint">Grand titre visible en haut de la page d'accueil.</span>
            </div>

            <div class="field" style="margin-bottom:1rem;">
                <label>Sous-titre (Hero)</label>
                <textarea name="hero_subtitle" maxlength="300"
                          placeholder="Choisissez le plan adapté à votre taille...">{{ $s['hero_subtitle'] ?? '' }}</textarea>
            </div>

            <div class="field-group" style="margin-bottom:1rem;">
                <div class="field">
                    <label>Mention pied de page</label>
                    <input type="text" name="footer_tagline" value="{{ $s['footer_tagline'] ?? 'Fait avec ❤ en Côte d\'Ivoire' }}"
                           maxlength="100" placeholder="Fait avec ❤ en Côte d'Ivoire">
                </div>
            </div>

            {{-- Image OG --}}
            <div class="field">
                <label>Image de partage (Open Graph)</label>
                @if(!empty($s['og_image_path']))
                    <div class="logo-preview">
                        <img src="{{ asset('storage/'.$s['og_image_path']) }}" alt="Image OG" style="max-height:60px;max-width:200px;object-fit:contain;">
                        <span style="font-size:.82rem;color:#64748b;">Image actuelle pour le partage sur les réseaux.</span>
                    </div>
                @endif
                <input type="file" name="og_image" accept="image/*"
                       style="background:#0f172a;border:1px solid rgba(255,255,255,.1);border-radius:7px;padding:.5rem;font-size:.82rem;color:#94a3b8;width:100%;box-sizing:border-box;">
                <span class="hint">Affichée lors du partage du lien (Facebook, WhatsApp…). 1200×630 px recommandé. Max 4 Mo.</span>
            </div>
        </div>

        {{-- MAINTENANCE ────────────────────────────────────────────────── --}}
        <div class="settings-section" id="maintenance">
            <h2 class="settings-section-title">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                Mode maintenance
            </h2>

            <div class="toggle-wrap" style="margin-bottom:1rem;">
                <input type="checkbox" class="toggle" name="maintenance_mode" id="maintenanceToggle"
                       value="1" {{ ($s['maintenance_mode'] ?? '0') === '1' ? 'checked' : '' }}>
                <label for="maintenanceToggle">
                    <strong style="color:#fca5a5;">Activer le mode maintenance</strong>
                    <span style="display:block;font-size:.78rem;margin-top:.1rem;">Le site public affichera une page de maintenance au lieu du contenu habituel.</span>
                </label>
            </div>

            <div class="field">
                <label>Message affiché aux visiteurs</label>
                <textarea name="maintenance_message" maxlength="500"
                          placeholder="Notre site est en cours de maintenance. Nous revenons très vite !">{{ $s['maintenance_message'] ?? '' }}</textarea>
            </div>
        </div>

        {{-- Barre de sauvegarde ─────────────────────────────────────── --}}
        <div class="save-bar">
            <a href="{{ route('admin.dashboard') }}"
               style="padding:.6rem 1.1rem;border:1px solid rgba(255,255,255,.1);border-radius:8px;font-size:.875rem;color:#64748b;text-decoration:none;font-weight:500;">
                Annuler
            </a>
            <button type="submit" class="btn-save">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:.3rem;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Enregistrer les paramètres
            </button>
        </div>
    </div>

</div>
</form>

@endsection

@push('scripts')
<script>
// Prévisualisation du logo avant upload
function previewLogo(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        const el = document.getElementById('logoImg');
        if (el.tagName === 'IMG') {
            el.src = e.target.result;
        } else {
            // Remplace le placeholder par un vrai img
            const img = document.createElement('img');
            img.id = 'logoImg';
            img.src = e.target.result;
            img.style.cssText = 'max-height:48px;max-width:160px;object-fit:contain;';
            el.replaceWith(img);
        }
    };
    reader.readAsDataURL(input.files[0]);
}

// Surlignage de la section active dans la nav
const sections = document.querySelectorAll('.settings-section');
const navLinks  = document.querySelectorAll('.settings-nav a');
let pauseObserver = false;

// Clic → active immédiat + pause de l'observer le temps du scroll animé
navLinks.forEach(link => {
    link.addEventListener('click', () => {
        navLinks.forEach(a => a.classList.remove('active'));
        link.classList.add('active');
        pauseObserver = true;
        setTimeout(() => { pauseObserver = false; }, 900);
    });
});

// Scroll → active via IntersectionObserver (ignoré pendant 900ms après un clic)
const observer = new IntersectionObserver(entries => {
    if (pauseObserver) return;
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            navLinks.forEach(a => a.classList.remove('active'));
            const id = entry.target.id;
            const link = document.querySelector(`.settings-nav a[href="#${id}"]`);
            if (link) link.classList.add('active');
        }
    });
}, { threshold: 0.15, rootMargin: '-75px 0px -40% 0px' });

sections.forEach(s => observer.observe(s));
</script>
@endpush
