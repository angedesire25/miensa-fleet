<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — MiensaFleet Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; background: #0f172a; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .login-card { width: 100%; max-width: 400px; }
        .login-logo { text-align: center; margin-bottom: 2rem; }
        .login-badge { width: 52px; height: 52px; background: linear-gradient(135deg,#ef4444,#b91c1c); border-radius: 14px; display: flex; align-items: center; justify-content: center; margin: 0 auto .75rem; font-size: 1.1rem; font-weight: 800; color: white; }
        .login-title { color: #f1f5f9; font-size: 1.25rem; font-weight: 700; margin: 0 0 .25rem; }
        .login-sub   { color: #64748b; font-size: .85rem; }

        .login-form  { background: #1e293b; border: 1px solid rgba(255,255,255,.07); border-radius: 14px; padding: 2rem; }
        .form-group  { margin-bottom: 1.1rem; }
        .form-label  { display: block; font-size: .82rem; font-weight: 600; color: #94a3b8; margin-bottom: .35rem; }
        .form-control { display: block; width: 100%; padding: .7rem 1rem; background: #0f172a; border: 1px solid rgba(255,255,255,.1); border-radius: 8px; color: #f1f5f9; font-size: .92rem; outline: none; font-family: inherit; transition: border-color .15s; }
        .form-control:focus { border-color: #ef4444; }
        .form-control::placeholder { color: #475569; }
        .invalid-feedback { font-size: .8rem; color: #fca5a5; margin-top: .3rem; }

        .btn-login { width: 100%; padding: .8rem; background: #ef4444; color: white; border: none; border-radius: 8px; font-size: .95rem; font-weight: 700; cursor: pointer; font-family: inherit; margin-top: .5rem; transition: background .15s; }
        .btn-login:hover { background: #dc2626; }

        .remember-row { display: flex; align-items: center; gap: .5rem; margin-bottom: 1rem; }
        .remember-row input { accent-color: #ef4444; }
        .remember-row label { font-size: .83rem; color: #64748b; cursor: pointer; }

        .restricted-note { margin-top: 1.5rem; text-align: center; font-size: .78rem; color: #475569; display: flex; align-items: center; justify-content: center; gap: .4rem; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="login-logo">
        <div class="login-badge">MF</div>
        <h1 class="login-title">MiensaFleet Admin</h1>
        <p class="login-sub">Accès réservé au propriétaire</p>
    </div>

    <div class="login-form">
        @if($errors->any())
            <div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);border-radius:8px;padding:.75rem 1rem;margin-bottom:1rem;font-size:.85rem;color:#fca5a5;">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.post') }}">
            @csrf
            <div class="form-group">
                <label class="form-label" for="email">Adresse email</label>
                <input type="email" id="email" name="email" class="form-control"
                    value="{{ old('email') }}" required autofocus placeholder="admin@miensafleet.ci">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Mot de passe</label>
                <input type="password" id="password" name="password" class="form-control"
                    required placeholder="••••••••••">
            </div>

            <div class="remember-row">
                <input type="checkbox" id="remember" name="remember" value="1">
                <label for="remember">Rester connecté</label>
            </div>

            <button type="submit" class="btn-login">Connexion →</button>
        </form>

        <div class="restricted-note">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Zone sécurisée — accès propriétaire uniquement
        </div>
    </div>
</div>
</body>
</html>
