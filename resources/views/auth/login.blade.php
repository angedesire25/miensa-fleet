<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Miensa Fleet</title>
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

<div style="display:flex;min-height:100vh;">

    {{-- ── Panneau gauche : branding ──────────────────────────────────── --}}
    <div class="fleet-bg" style="width:45%;display:flex;flex-direction:column;justify-content:space-between;padding:2.5rem;position:relative;overflow:hidden;">

        {{-- Cercles décoratifs --}}
        <div style="position:absolute;top:-80px;right:-80px;width:320px;height:320px;border-radius:50%;background:rgba(16,185,129,0.08);"></div>
        <div style="position:absolute;bottom:-60px;left:-60px;width:240px;height:240px;border-radius:50%;background:rgba(16,185,129,0.06);"></div>

        {{-- Logo --}}
        <div style="position:relative;z-index:1;">
            <div style="display:flex;align-items:center;gap:.6rem;">
                <div style="width:40px;height:40px;background:linear-gradient(135deg,#10b981,#059669);border-radius:.6rem;display:flex;align-items:center;justify-content:center;">
                    <svg width="22" height="22" fill="none" viewBox="0 0 24 24">
                        <path d="M3 17h2l1-3h12l1 3h2a1 1 0 000-2H20l-2-6H6L4 15H2a1 1 0 000 2z" fill="white"/>
                        <circle cx="7.5" cy="18.5" r="1.5" fill="white"/>
                        <circle cx="16.5" cy="18.5" r="1.5" fill="white"/>
                        <path d="M6.5 9l1-3h9l1 3" stroke="white" stroke-width="1.5" fill="none"/>
                    </svg>
                </div>
                <span style="color:#fff;font-size:1.4rem;font-weight:700;letter-spacing:.01em;">Miensa<span style="color:#10b981;">Fleet</span></span>
            </div>
        </div>

        {{-- Message central --}}
        <div style="position:relative;z-index:1;">
            <p style="color:rgba(255,255,255,.65);font-size:.85rem;margin-bottom:.5rem;letter-spacing:.05em;text-transform:uppercase;">Bienvenue sur</p>
            <h1 style="color:#fff;font-size:2rem;font-weight:700;line-height:1.25;margin:0 0 1rem;">Votre gestionnaire<br>de flotte intelligent.</h1>
            <p style="color:rgba(255,255,255,.6);font-size:.9rem;line-height:1.65;max-width:320px;">
                Suivez vos véhicules, chauffeurs, affectations et sinistres depuis un seul espace unifié.
            </p>

            {{-- Stats rapides --}}
            <div style="display:flex;gap:1.5rem;margin-top:2rem;">
                <div style="text-align:center;">
                    <div style="color:#10b981;font-size:1.6rem;font-weight:700;">10+</div>
                    <div style="color:rgba(255,255,255,.5);font-size:.75rem;">Véhicules</div>
                </div>
                <div style="width:1px;background:rgba(255,255,255,.1);"></div>
                <div style="text-align:center;">
                    <div style="color:#10b981;font-size:1.6rem;font-weight:700;">7</div>
                    <div style="color:rgba(255,255,255,.5);font-size:.75rem;">Rôles</div>
                </div>
                <div style="width:1px;background:rgba(255,255,255,.1);"></div>
                <div style="text-align:center;">
                    <div style="color:#10b981;font-size:1.6rem;font-weight:700;">24/7</div>
                    <div style="color:rgba(255,255,255,.5);font-size:.75rem;">Alertes</div>
                </div>
            </div>
        </div>

        {{-- Footer gauche --}}
        <div style="position:relative;z-index:1;">
            <p style="color:rgba(255,255,255,.45);font-size:.8rem;">
                Accès réservé au personnel autorisé.<br>
                Contactez votre administrateur pour un compte.
            </p>
        </div>
    </div>

    {{-- ── Panneau droit : formulaire ──────────────────────────────────── --}}
    <div style="flex:1;display:flex;align-items:center;justify-content:center;padding:2rem;background:#fff;">
        <div style="width:100%;max-width:420px;">

            <h2 style="font-size:1.9rem;font-weight:700;color:#0f172a;margin-bottom:.35rem;">Connexion</h2>
            <p style="color:#64748b;font-size:.9rem;margin-bottom:2rem;">Connectez-vous à votre espace de gestion de flotte</p>

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

            {{-- ── Panneau de test rapide (local uniquement) ────────────── --}}
            @if(app()->isLocal())
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
