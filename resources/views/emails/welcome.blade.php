<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue sur Miensa Fleet</title>
    <style>
        body { margin:0; padding:0; background:#f1f5f9; font-family:'Segoe UI',Arial,sans-serif; color:#0f172a; }
        .wrapper { max-width:600px; margin:32px auto; background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,.08); }
        .header { padding:28px 32px; background:linear-gradient(135deg,#0f172a 0%,#1e293b 100%); }
        .header h1 { margin:0; font-size:1.3rem; color:#fff; font-weight:800; }
        .header p  { margin:.4rem 0 0; color:#94a3b8; font-size:.875rem; }
        .body { padding:28px 32px; }
        .greeting { font-size:1rem; font-weight:700; margin-bottom:.75rem; }
        p { font-size:.875rem; line-height:1.6; color:#374151; margin:0 0 .85rem; }
        .cred-box { background:#f8fafc; border:1px solid #e2e8f0; border-radius:.6rem; padding:1rem 1.25rem; margin:1rem 0; }
        .cred-row { display:flex; justify-content:space-between; align-items:center; font-size:.85rem; padding:.35rem 0; border-bottom:1px solid #f1f5f9; }
        .cred-row:last-child { border-bottom:none; }
        .cred-label { color:#64748b; font-weight:600; }
        .cred-value { font-weight:700; color:#0f172a; font-family:monospace; }
        .btn { display:inline-block; background:linear-gradient(135deg,#10b981,#059669); color:#fff; text-decoration:none; padding:.7rem 1.6rem; border-radius:.5rem; font-weight:700; font-size:.875rem; margin:.5rem .35rem .5rem 0; }
        .btn-outline { display:inline-block; border:2px solid #e2e8f0; color:#374151; text-decoration:none; padding:.65rem 1.4rem; border-radius:.5rem; font-weight:600; font-size:.875rem; margin:.5rem 0; }
        .install-section { background:#f0fdf4; border:1px solid #bbf7d0; border-radius:.65rem; padding:1.1rem 1.25rem; margin:1.25rem 0; }
        .install-section h3 { font-size:.9rem; font-weight:700; color:#166534; margin:0 0 .5rem; }
        .install-section p  { color:#166534; font-size:.82rem; margin:0 0 .75rem; }
        .footer { padding:16px 32px; background:#f8fafc; border-top:1px solid #e2e8f0; font-size:.75rem; color:#94a3b8; text-align:center; }
    </style>
</head>
<body>
<div class="wrapper">

    <div class="header">
        <h1>Bienvenue sur Miensa Fleet !</h1>
        <p>Votre accès à la plateforme de gestion de flotte est prêt.</p>
    </div>

    <div class="body">
        <div class="greeting">Bonjour {{ $user->name }} 👋</div>

        <p>
            Votre compte sur <strong>Miensa Fleet</strong> a été créé. Vous pouvez dès maintenant vous connecter
            et commencer à utiliser la plateforme.
        </p>

        @if($temporaryPassword)
        <div class="cred-box">
            <div class="cred-row">
                <span class="cred-label">Email</span>
                <span class="cred-value">{{ $user->email }}</span>
            </div>
            <div class="cred-row">
                <span class="cred-label">Mot de passe temporaire</span>
                <span class="cred-value">{{ $temporaryPassword }}</span>
            </div>
        </div>
        <p style="font-size:.8rem;color:#ef4444;">
            Pour votre sécurité, modifiez ce mot de passe dès votre première connexion depuis votre profil.
        </p>
        @endif

        <p>
            <a href="{{ $loginUrl }}" class="btn">Se connecter maintenant</a>
        </p>

        <div class="install-section">
            <h3>Installez l'application sur votre téléphone</h3>
            <p>
                Miensa Fleet fonctionne comme une application native sur Android et iPhone.
                Installez-la en quelques secondes pour un accès rapide et des notifications en temps réel.
            </p>
            <a href="{{ $installUrl }}" class="btn-outline">Guide d'installation</a>
        </div>

        <p style="font-size:.8rem;color:#94a3b8;">
            Si vous n'êtes pas à l'origine de la création de ce compte, veuillez contacter votre administrateur.
        </p>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} Miensa Fleet &mdash; Développé par ADN
    </div>
</div>
</body>
</html>
