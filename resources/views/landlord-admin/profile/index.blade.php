@extends('landlord-admin.layouts.app')

@section('title', 'Mon profil')
@section('page-title', 'Mon profil')
@section('breadcrumb', 'Paramètres du compte')

@push('styles')
<style>
    .profile-grid { display: grid; grid-template-columns: 280px 1fr; gap: 1.5rem; max-width: 900px; align-items: start; }
    .profile-card {
        background: #1e293b; border: 1px solid rgba(255,255,255,.07);
        border-radius: 12px; padding: 1.75rem; text-align: center;
    }
    .profile-avatar {
        width: 80px; height: 80px; border-radius: 50%;
        background: linear-gradient(135deg,#7c3aed,#4f46e5);
        display: flex; align-items: center; justify-content: center;
        font-size: 2rem; font-weight: 800; color: white;
        margin: 0 auto 1rem;
    }
    .profile-name  { font-size: 1.05rem; font-weight: 700; color: #f1f5f9; }
    .profile-email { font-size: .82rem; color: #64748b; margin-top: .25rem; word-break: break-all; }
    .profile-role  {
        display: inline-block; margin-top: .75rem;
        background: rgba(239,68,68,.1); color: #fca5a5;
        font-size: .72rem; font-weight: 700; padding: .2rem .65rem;
        border-radius: 10px; text-transform: uppercase; letter-spacing: .05em;
    }
    .profile-since { font-size: .78rem; color: #475569; margin-top: 1rem; }

    .form-block {
        background: #1e293b; border: 1px solid rgba(255,255,255,.07);
        border-radius: 12px; padding: 1.5rem; margin-bottom: 1.25rem;
    }
    .form-block-title {
        font-size: .78rem; font-weight: 700; color: #475569;
        text-transform: uppercase; letter-spacing: .07em;
        margin: 0 0 1.1rem; padding-bottom: .65rem;
        border-bottom: 1px solid rgba(255,255,255,.06);
        display: flex; align-items: center; gap: .5rem;
    }
    .field { display: flex; flex-direction: column; gap: .35rem; margin-bottom: .9rem; }
    .field:last-of-type { margin-bottom: 0; }
    .field label { font-size: .82rem; font-weight: 600; color: #94a3b8; }
    .field input {
        background: #0f172a; border: 1px solid rgba(255,255,255,.1);
        border-radius: 7px; padding: .6rem .9rem;
        color: #e2e8f0; font-size: .875rem; font-family: inherit;
        transition: border-color .15s; outline: none; width: 100%; box-sizing: border-box;
    }
    .field input:focus { border-color: #3b82f6; }
    .field input.is-invalid { border-color: #ef4444; }
    .field .error { font-size: .75rem; color: #fca5a5; margin-top: .2rem; }
    .field .hint  { font-size: .75rem; color: #475569; margin-top: .2rem; }

    .strength-bar { height: 4px; border-radius: 2px; background: rgba(255,255,255,.08); margin-top: .4rem; overflow: hidden; }
    .strength-fill { height: 100%; border-radius: 2px; transition: width .3s, background .3s; width: 0; }

    @media (max-width: 800px) { .profile-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')

<div class="profile-grid">

    {{-- ── Carte identité ──────────────────────────────────────────── --}}
    <div>
        <div class="profile-card">
            <div class="profile-avatar">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="profile-name">{{ $user->name }}</div>
            <div class="profile-email">{{ $user->email }}</div>
            <div class="profile-role">Super Admin</div>
            <div class="profile-since">
                Compte créé le {{ $user->created_at->format('d/m/Y') }}
            </div>
        </div>

        <div style="margin-top:1rem;background:#1e293b;border:1px solid rgba(255,255,255,.07);border-radius:12px;padding:1.1rem;">
            <div style="font-size:.75rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.75rem;">Sécurité</div>
            <div style="display:flex;align-items:center;gap:.5rem;font-size:.82rem;color:#64748b;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                Authentification par mot de passe
            </div>
            <div style="display:flex;align-items:center;gap:.5rem;font-size:.82rem;color:#64748b;margin-top:.4rem;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
                Dernière connexion : {{ now()->format('d/m/Y') }}
            </div>
        </div>
    </div>

    {{-- ── Formulaires ─────────────────────────────────────────────── --}}
    <div>

        {{-- Infos personnelles ────────────────────────────────────── --}}
        @if(session('success_info'))
        <div class="a-alert-success" style="margin-bottom:1rem;">{{ session('success_info') }}</div>
        @endif

        <div class="form-block">
            <div class="form-block-title">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Informations personnelles
            </div>

            <form method="POST" action="{{ route('admin.profile.info') }}">
                @csrf @method('PUT')

                <div class="field">
                    <label for="name">Nom complet</label>
                    <input type="text" id="name" name="name"
                           value="{{ old('name', $user->name) }}" required maxlength="100"
                           class="{{ $errors->has('name') ? 'is-invalid' : '' }}">
                    @error('name')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div class="field">
                    <label for="email">Adresse e-mail</label>
                    <input type="email" id="email" name="email"
                           value="{{ old('email', $user->email) }}" required maxlength="150"
                           class="{{ $errors->has('email') ? 'is-invalid' : '' }}">
                    @error('email')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div style="display:flex;justify-content:flex-end;margin-top:1rem;">
                    <button type="submit"
                            style="padding:.55rem 1.35rem;background:linear-gradient(135deg,#3b82f6,#1d4ed8);color:white;border:none;border-radius:8px;font-size:.875rem;font-weight:600;cursor:pointer;">
                        Enregistrer les infos
                    </button>
                </div>
            </form>
        </div>

        {{-- Mot de passe ───────────────────────────────────────────── --}}
        @if(session('success_password'))
        <div class="a-alert-success" style="margin-bottom:1rem;">{{ session('success_password') }}</div>
        @endif

        <div class="form-block">
            <div class="form-block-title">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Changer le mot de passe
            </div>

            <form method="POST" action="{{ route('admin.profile.password') }}">
                @csrf @method('PUT')

                <div class="field">
                    <label for="current_password">Mot de passe actuel</label>
                    <input type="password" id="current_password" name="current_password" required
                           autocomplete="current-password"
                           class="{{ $errors->has('current_password') ? 'is-invalid' : '' }}">
                    @error('current_password')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div class="field">
                    <label for="password">Nouveau mot de passe</label>
                    <input type="password" id="password" name="password" required
                           autocomplete="new-password" oninput="checkStrength(this.value)"
                           class="{{ $errors->has('password') ? 'is-invalid' : '' }}">
                    <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                    <span class="hint" id="strengthLabel">Saisissez un mot de passe.</span>
                    @error('password')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div class="field">
                    <label for="password_confirmation">Confirmer le nouveau mot de passe</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           required autocomplete="new-password">
                </div>

                <div style="display:flex;justify-content:flex-end;margin-top:1rem;">
                    <button type="submit"
                            style="padding:.55rem 1.35rem;background:linear-gradient(135deg,#7c3aed,#4f46e5);color:white;border:none;border-radius:8px;font-size:.875rem;font-weight:600;cursor:pointer;">
                        Changer le mot de passe
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
function checkStrength(value) {
    const fill  = document.getElementById('strengthFill');
    const label = document.getElementById('strengthLabel');
    let score = 0;
    if (value.length >= 8)  score++;
    if (value.length >= 12) score++;
    if (/[A-Z]/.test(value)) score++;
    if (/[0-9]/.test(value)) score++;
    if (/[^A-Za-z0-9]/.test(value)) score++;

    const levels = [
        { pct: '0%',   color: 'transparent', text: 'Saisissez un mot de passe.' },
        { pct: '25%',  color: '#ef4444',      text: 'Trop faible' },
        { pct: '50%',  color: '#f97316',      text: 'Faible' },
        { pct: '75%',  color: '#eab308',      text: 'Moyen' },
        { pct: '90%',  color: '#22c55e',      text: 'Fort' },
        { pct: '100%', color: '#10b981',      text: 'Très fort' },
    ];
    const lvl = levels[Math.min(score, 5)];
    fill.style.width      = lvl.pct;
    fill.style.background = lvl.color;
    label.textContent     = lvl.text;
    label.style.color     = score >= 3 ? '#86efac' : '#94a3b8';
}
</script>
@endpush
