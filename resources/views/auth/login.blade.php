@php
    use App\Models\AppSetting;
    $appLogo        = AppSetting::get('logo');
    $carouselImages = array_filter([
        ['src' => AppSetting::get('carousel_image_1'), 'caption' => AppSetting::get('carousel_caption_1', '')],
        ['src' => AppSetting::get('carousel_image_2'), 'caption' => AppSetting::get('carousel_caption_2', '')],
        ['src' => AppSetting::get('carousel_image_3'), 'caption' => AppSetting::get('carousel_caption_3', '')],
    ], fn($s) => !empty($s['src']));
    $carouselImages = array_values($carouselImages);
    $hasCarousel    = count($carouselImages) > 0;
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Miensa Fleet</title>
    @if($appLogo)
    <link rel="icon" type="image/png" href="{{ Storage::url($appLogo) }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .fleet-bg {
            background-image:
                linear-gradient(135deg, rgba(3,25,50,0.88) 0%, rgba(4,78,70,0.82) 100%),
                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='400'%3E%3Crect width='400' height='400' fill='%23064e3b'/%3E%3Ccircle cx='200' cy='200' r='140' fill='none' stroke='%2310b981' stroke-width='1' opacity='0.15'/%3E%3Ccircle cx='200' cy='200' r='100' fill='none' stroke='%2310b981' stroke-width='1' opacity='0.1'/%3E%3C/svg%3E");
            background-size: cover;
            background-position: center;
        }
        /* ── Carousel ───────────────────────────────────────────────────── */
        .carousel-wrap {
            position:relative; width:45%; flex-shrink:0;
            height:100vh; overflow:hidden;
        }
        .carousel-track {
            display:flex; height:100%;
            transition:transform .65s cubic-bezier(.4,0,.2,1);
            will-change:transform;
        }
        .carousel-slide {
            min-width:100%; height:100%; position:relative;
            flex-shrink:0; background:#0f172a; overflow:hidden;
        }
        .carousel-slide img {
            position:absolute; inset:0;
            width:100%; height:100%; object-fit:cover;
            filter:brightness(.52);
        }
        .carousel-overlay {
            position:absolute; inset:0; z-index:1;
            background:linear-gradient(to bottom,
                rgba(0,0,0,.2) 0%,
                rgba(3,35,25,.55) 55%,
                rgba(3,35,25,.88) 100%);
            display:flex; flex-direction:column; justify-content:space-between;
            padding:2.5rem;
        }
        .carousel-dots { display:flex; gap:.45rem; }
        .carousel-dot {
            width:8px; height:8px; border-radius:50%; background:rgba(255,255,255,.35);
            cursor:pointer; transition:background .3s, transform .3s; border:none;
        }
        .carousel-dot.active { background:#10b981; transform:scale(1.3); }
        /* ── Brand logo+nom ─────────────────────────────────────────────── */
        .app-brand {
            display: flex;
            align-items: center;
            gap: .65rem;
        }
        .app-brand-icon {
            width: 36px;
            height: 36px;
            flex-shrink: 0;
            border-radius: .45rem;
            object-fit: contain;
        }
        .app-brand-icon-default {
            width: 36px;
            height: 36px;
            flex-shrink: 0;
            border-radius: .5rem;
            background: linear-gradient(135deg, #10b981, #059669);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .app-brand-name {
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: .01em;
            color: #fff;
            white-space: nowrap;
        }
        .app-brand-name em { font-style: normal; color: #10b981; }
        .carousel-caption {
            margin-top:.65rem; color:rgba(255,255,255,.7); font-size:.85rem;
            font-style:italic; min-height:1.2em;
        }
        .input-field {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1.5px solid #e5e7eb;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            color: #111827;
            background: #fff;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }
        .input-field:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16,185,129,0.15);
        }
        .input-field::placeholder { color: #9ca3af; }
        .btn-primary {
            width: 100%;
            padding: 0.85rem;
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            font-weight: 600;
            font-size: 1rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: opacity .2s, transform .1s;
            letter-spacing: .02em;
        }
        .btn-primary:hover { opacity: .93; }
        .btn-primary:active { transform: scale(.99); }
    </style>
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:'Inter',ui-sans-serif,system-ui,sans-serif;">

<div style="display:flex;min-height:100vh;align-items:stretch;">

    {{-- ── Panneau gauche ───────────────────────────────────────────────── --}}
    @if($hasCarousel)
    {{-- ── Mode carousel (images configurées par le super admin) ──────── --}}
    <div class="carousel-wrap" id="carousel">

        {{-- Slides --}}
        <div class="carousel-track" id="carousel-track">
            @foreach($carouselImages as $slide)
            <div class="carousel-slide">
                <img src="{{ Storage::url($slide['src']) }}" alt="">
                <div class="carousel-overlay">

                    {{-- Logo en haut --}}
                    <div class="app-brand">
                        @if($appLogo)
                            <img src="{{ Storage::url($appLogo) }}" alt="Logo"
                                 style="width:36px;height:36px;max-width:36px;max-height:36px;object-fit:contain;flex-shrink:0;border-radius:.45rem;">
                        @else
                            <div class="app-brand-icon-default">
                                <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M3 17h2l1-3h12l1 3h2a1 1 0 000-2H20l-2-6H6L4 15H2a1 1 0 000 2z" fill="white"/><circle cx="7.5" cy="18.5" r="1.5" fill="white"/><circle cx="16.5" cy="18.5" r="1.5" fill="white"/></svg>
                            </div>
                        @endif
                        <span class="app-brand-name">Miensa<em>Fleet</em></span>
                    </div>

                    {{-- Caption + dots en bas --}}
                    <div>
                        @if($slide['caption'])
                        <p class="carousel-caption">{{ $slide['caption'] }}</p>
                        @endif
                        <div class="carousel-dots" id="carousel-dots">
                            @foreach($carouselImages as $j => $__)
                            <button class="carousel-dot {{ $j === 0 ? 'active' : '' }}" onclick="goToSlide({{ $j }})"></button>
                            @endforeach
                        </div>
                        <p style="color:rgba(255,255,255,.4);font-size:.75rem;margin-top:1.25rem;">
                            Accès réservé au personnel autorisé.
                        </p>
                    </div>

                </div>
            </div>
            @endforeach
        </div>

    </div>
    @else
    {{-- ── Mode statique (pas d'images configurées) ─────────────────────── --}}
    <div class="fleet-bg" style="width:45%;display:flex;flex-direction:column;justify-content:space-between;padding:2.5rem;position:relative;overflow:hidden;">

        <div style="position:absolute;top:-80px;right:-80px;width:320px;height:320px;border-radius:50%;background:rgba(16,185,129,0.08);"></div>
        <div style="position:absolute;bottom:-60px;left:-60px;width:240px;height:240px;border-radius:50%;background:rgba(16,185,129,0.06);"></div>

        {{-- Logo --}}
        <div class="app-brand" style="position:relative;z-index:1;">
            @if($appLogo)
                <img src="{{ Storage::url($appLogo) }}" alt="Logo"
                     style="width:36px;height:36px;max-width:36px;max-height:36px;object-fit:contain;flex-shrink:0;border-radius:.45rem;">
            @else
                <div class="app-brand-icon-default">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24">
                        <path d="M3 17h2l1-3h12l1 3h2a1 1 0 000-2H20l-2-6H6L4 15H2a1 1 0 000 2z" fill="white"/>
                        <circle cx="7.5" cy="18.5" r="1.5" fill="white"/>
                        <circle cx="16.5" cy="18.5" r="1.5" fill="white"/>
                    </svg>
                </div>
            @endif
            <span class="app-brand-name">Miensa<em>Fleet</em></span>
        </div>

        <div style="position:relative;z-index:1;">
            <p style="color:rgba(255,255,255,.65);font-size:.85rem;margin-bottom:.5rem;letter-spacing:.05em;text-transform:uppercase;">Bienvenue sur</p>
            <h1 style="color:#fff;font-size:2rem;font-weight:700;line-height:1.25;margin:0 0 1rem;">Votre gestionnaire<br>de flotte intelligent.</h1>
            <p style="color:rgba(255,255,255,.6);font-size:.9rem;line-height:1.65;max-width:320px;">
                Suivez vos véhicules, chauffeurs, affectations et sinistres depuis un seul espace unifié.
            </p>
            <div style="display:flex;gap:1.5rem;margin-top:2rem;">
                <div style="text-align:center;"><div style="color:#10b981;font-size:1.6rem;font-weight:700;">10+</div><div style="color:rgba(255,255,255,.5);font-size:.75rem;">Véhicules</div></div>
                <div style="width:1px;background:rgba(255,255,255,.1);"></div>
                <div style="text-align:center;"><div style="color:#10b981;font-size:1.6rem;font-weight:700;">7</div><div style="color:rgba(255,255,255,.5);font-size:.75rem;">Rôles</div></div>
                <div style="width:1px;background:rgba(255,255,255,.1);"></div>
                <div style="text-align:center;"><div style="color:#10b981;font-size:1.6rem;font-weight:700;">24/7</div><div style="color:rgba(255,255,255,.5);font-size:.75rem;">Alertes</div></div>
            </div>
        </div>

        <div style="position:relative;z-index:1;">
            <p style="color:rgba(255,255,255,.45);font-size:.8rem;">
                Accès réservé au personnel autorisé.<br>
                Contactez votre administrateur pour un compte.
            </p>
        </div>
    </div>
    @endif

    {{-- ── Panneau droit : formulaire ──────────────────────────────────── --}}
    <div style="flex:1;display:flex;align-items:center;justify-content:center;padding:2rem;background:#fff;overflow-y:auto;">
        <div style="width:100%;max-width:420px;">

            {{-- Badge sécurité --}}
            <div style="display:inline-flex;align-items:center;gap:.45rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:99px;padding:.3rem .9rem;margin-bottom:1.1rem;">
                <svg width="12" height="12" fill="none" viewBox="0 0 24 24">
                    <rect x="3" y="11" width="18" height="11" rx="2" stroke="#16a34a" stroke-width="2"/>
                    <path d="M7 11V7a5 5 0 0110 0v4" stroke="#16a34a" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <span style="font-size:.68rem;font-weight:700;color:#16a34a;letter-spacing:.07em;text-transform:uppercase;">Espace sécurisé</span>
            </div>

            {{-- Titre avec accent décoratif --}}
            <div style="margin-bottom:1.65rem;">
                <h2 style="font-size:2.15rem;font-weight:800;color:#0f172a;margin:0 0 .3rem;line-height:1.1;letter-spacing:-.02em;">
                    Connexion
                </h2>
                <div style="display:flex;align-items:center;gap:.75rem;">
                    <div style="width:40px;height:3px;background:linear-gradient(90deg,#10b981,#059669);border-radius:99px;"></div>
                    <p style="color:#94a3b8;font-size:.85rem;margin:0;">Connectez-vous à votre espace de gestion de flotte</p>
                </div>
            </div>

            {{-- Erreurs --}}
            @if ($errors->any())
                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:.5rem;padding:.85rem 1rem;margin-bottom:1.25rem;display:flex;gap:.6rem;align-items:flex-start;">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:.05rem;">
                        <circle cx="12" cy="12" r="10" fill="#ef4444"/>
                        <path d="M12 8v4m0 4h.01" stroke="white" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <span style="color:#dc2626;font-size:.875rem;">{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">
                @csrf

                {{-- Email --}}
                <div style="margin-bottom:1.25rem;">
                    <label style="display:block;font-weight:600;font-size:.875rem;color:#374151;margin-bottom:.45rem;">
                        Adresse email
                    </label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="prenom.nom@entreprise.ci"
                        required
                        autofocus
                        class="input-field"
                    >
                </div>

                {{-- Mot de passe --}}
                <div style="margin-bottom:.5rem;">
                    <label style="display:block;font-weight:600;font-size:.875rem;color:#374151;margin-bottom:.45rem;">
                        Mot de passe
                    </label>
                    <div style="position:relative;">
                        <input
                            type="password"
                            name="password"
                            id="password-input"
                            placeholder="••••••••"
                            required
                            class="input-field"
                            style="padding-right:3rem;"
                        >
                        <button type="button" onclick="togglePassword()" style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:.2rem;color:#9ca3af;">
                            <svg id="eye-icon" width="20" height="20" fill="none" viewBox="0 0 24 24">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="1.8"/>
                                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Se souvenir + oublié --}}
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.75rem;">
                    <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;">
                        <input type="checkbox" name="remember" style="accent-color:#10b981;width:15px;height:15px;">
                        <span style="font-size:.85rem;color:#64748b;">Se souvenir de moi</span>
                    </label>
                    <a href="#" style="color:#10b981;font-size:.85rem;font-weight:500;text-decoration:none;">Mot de passe oublié ?</a>
                </div>

                {{-- Bouton connexion --}}
                <button type="submit" class="btn-primary">
                    Se connecter
                </button>

            </form>

            {{-- Séparateur --}}
            <div style="display:flex;align-items:center;gap:1rem;margin:1.5rem 0;">
                <div style="flex:1;height:1px;background:#e5e7eb;"></div>
                <span style="color:#9ca3af;font-size:.8rem;">OU</span>
                <div style="flex:1;height:1px;background:#e5e7eb;"></div>
            </div>

            {{-- ── Panneau de test rapide (local, tenant dev uniquement) ─── --}}
            @if(app()->isLocal() && \App\Models\Tenant::current()?->slug === 'dev')
            <div style="border:1.5px dashed #d1d5db;border-radius:.6rem;overflow:hidden;">
                <div style="background:#fafafa;padding:.55rem .85rem;display:flex;align-items:center;gap:.5rem;border-bottom:1px solid #e5e7eb;">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="#f59e0b" stroke-width="2"/><path d="M12 8v4m0 4h.01" stroke="#f59e0b" stroke-width="2" stroke-linecap="round"/></svg>
                    <span style="font-size:.72rem;font-weight:700;color:#92400e;letter-spacing:.04em;text-transform:uppercase;">Environnement local — Connexion rapide</span>
                </div>
                <div style="padding:.65rem .75rem;display:flex;flex-direction:column;gap:.3rem;">
                    @php
                    $testProfiles = [
                        ['role'=>'super_admin',  'color'=>'#B91C1C','email'=>'superadmin@miensafleet.ci',  'label'=>'Super Admin',       'icon'=>'⚙️'],
                        ['role'=>'admin',         'color'=>'#1D4ED8','email'=>'admin@miensafleet.ci',       'label'=>'Admin',              'icon'=>'🛡'],
                        ['role'=>'fleet_manager', 'color'=>'#047857','email'=>'kofi.asante@miensafleet.ci', 'label'=>'Resp. Flotte (Kofi)','icon'=>'🚗'],
                        ['role'=>'fleet_manager', 'color'=>'#047857','email'=>'amina.diallo@miensafleet.ci','label'=>'Resp. Flotte (Amina)','icon'=>'🚗'],
                        ['role'=>'controller',    'color'=>'#D97706','email'=>'djibril.traore@miensafleet.ci','label'=>'Contrôleur',       'icon'=>'📋'],
                        ['role'=>'director',      'color'=>'#7C3AED','email'=>'fatou.sidibe@miensafleet.ci','label'=>'Directrice',         'icon'=>'📊'],
                        ['role'=>'collaborator',  'color'=>'#0891B2','email'=>'jb.yao@miensafleet.ci',     'label'=>'Collaborateur',       'icon'=>'📝'],
                        ['role'=>'driver_user',   'color'=>'#64748B','email'=>'sekou.ouattara@miensafleet.ci','label'=>'Chauffeur',        'icon'=>'🧑‍✈️'],
                    ];
                    @endphp
                    @foreach($testProfiles as $p)
                        <button type="button"
                            onclick="quickLogin('{{ $p['email'] }}')"
                            style="display:flex;align-items:center;gap:.6rem;width:100%;padding:.45rem .65rem;border-radius:.4rem;border:1px solid #e5e7eb;background:#fff;cursor:pointer;text-align:left;transition:background .15s;"
                            onmouseover="this.style.background='#f8fafc';this.style.borderColor='{{ $p['color'] }}'"
                            onmouseout="this.style.background='#fff';this.style.borderColor='#e5e7eb'">
                            <span style="font-size:.85rem;">{{ $p['icon'] }}</span>
                            <div style="flex:1;">
                                <span style="font-size:.78rem;font-weight:600;color:#0f172a;">{{ $p['label'] }}</span>
                                <span style="font-size:.7rem;color:#94a3b8;margin-left:.4rem;">{{ $p['email'] }}</span>
                            </div>
                            <span style="font-size:.65rem;font-weight:700;padding:.15rem .5rem;border-radius:99px;color:#fff;background:{{ $p['color'] }};">
                                {{ $p['role'] }}
                            </span>
                        </button>
                    @endforeach
                </div>
                <div style="padding:.4rem .85rem;background:#fafafa;border-top:1px solid #e5e7eb;">
                    <span style="font-size:.68rem;color:#94a3b8;">Mot de passe universel : <strong style="color:#374151;font-family:monospace;">Password@123</strong></span>
                </div>
            </div>
            @else
            {{-- Info --}}
            <div style="text-align:center;padding:.9rem;background:#f0fdf4;border-radius:.5rem;border:1px solid #bbf7d0;">
                <p style="color:#166534;font-size:.825rem;margin:0;">
                    🔒 Accès sécurisé — Pour tout problème de connexion,<br>
                    contactez <strong>superadmin@miensafleet.ci</strong>
                </p>
            </div>
            @endif

            {{-- Footer --}}
            <p style="text-align:center;color:#94a3b8;font-size:.75rem;margin-top:2rem;">
                © {{ date('Y') }} Miensa Fleet · Tous droits réservés
            </p>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ── SweetAlert2 — notifications login ─────────────────────────────────────
const SwalToast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 5000,
    timerProgressBar: true,
    didOpen: (t) => {
        t.addEventListener('mouseenter', Swal.stopTimer);
        t.addEventListener('mouseleave', Swal.resumeTimer);
    }
});

document.addEventListener('DOMContentLoaded', () => {
    @if(session('swal_info'))
        SwalToast.fire({ icon: 'info', title: @json(session('swal_info')) });
    @endif

    @if(session('swal_success'))
        SwalToast.fire({ icon: 'success', title: @json(session('swal_success')) });
    @endif

    @if(session('swal_error') || $errors->has('email'))
        Swal.fire({
            icon:             'error',
            title:            'Connexion échouée',
            text:             @json(session('swal_error') ?? $errors->first('email') ?? 'Identifiants incorrects.'),
            confirmButtonText:'Réessayer',
            confirmButtonColor:'#10b981',
            background:       '#fff',
        });
    @endif
});

function quickLogin(email) {
    document.querySelector('input[name="email"]').value = email;
    document.querySelector('input[name="password"]').value = 'Password@123';
    // Petit délai visuel pour voir le remplissage avant soumission
    setTimeout(() => document.querySelector('form').submit(), 150);
}

// ── Carousel auto-slide ────────────────────────────────────────────────────
@if($hasCarousel)
(function () {
    const total   = {{ count($carouselImages) }};
    const track   = document.getElementById('carousel-track');
    const dotsEl  = document.getElementById('carousel-dots');
    let   current = 0;
    let   timer   = null;

    window.goToSlide = function (i) {
        current = (i + total) % total;
        track.style.transform = `translateX(-${current * 100}%)`;
        document.querySelectorAll('.carousel-dot').forEach((d, idx) => {
            d.classList.toggle('active', idx === current);
        });
    };

    function next() { goToSlide(current + 1); }

    function startTimer() { timer = setInterval(next, 5000); }
    function resetTimer()  { clearInterval(timer); startTimer(); }

    // Pause on hover
    const wrap = document.getElementById('carousel');
    if (wrap) {
        wrap.addEventListener('mouseenter', () => clearInterval(timer));
        wrap.addEventListener('mouseleave', startTimer);
    }

    startTimer();
})();
@endif

function togglePassword() {
    const input = document.getElementById('password-input');
    const icon  = document.getElementById('eye-icon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24" stroke="currentColor" stroke-width="1.8" fill="none"/><line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="1.8"/>';
    } else {
        input.type = 'password';
        icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/>';
    }
}
</script>

</body>
</html>
