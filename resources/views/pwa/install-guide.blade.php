<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Installer Miensa Fleet</title>
    <meta name="theme-color" content="#10b981">
    <link rel="manifest" href="/manifest.json">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            color: #0f172a;
            min-height: 100vh;
        }
        .header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #fff;
            padding: 2rem 1.5rem 1.5rem;
            text-align: center;
        }
        .header-logo {
            width: 72px; height: 72px;
            border-radius: 16px;
            margin: 0 auto 1rem;
            display: block;
        }
        .header h1 { font-size: 1.4rem; font-weight: 800; }
        .header p  { font-size: .875rem; color: #94a3b8; margin-top: .4rem; }

        .tabs {
            display: flex;
            background: #fff;
            border-bottom: 2px solid #e2e8f0;
            position: sticky; top: 0; z-index: 10;
        }
        .tab {
            flex: 1;
            padding: .9rem .5rem;
            text-align: center;
            font-size: .85rem;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all .2s;
        }
        .tab.active {
            color: #10b981;
            border-bottom-color: #10b981;
        }

        .panel { display: none; padding: 1.5rem; max-width: 560px; margin: 0 auto; }
        .panel.active { display: block; }

        .step {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            align-items: flex-start;
        }
        .step-num {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: #10b981;
            color: #fff;
            font-size: .875rem;
            font-weight: 800;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .step-body { flex: 1; padding-top: .15rem; }
        .step-title { font-size: .93rem; font-weight: 700; margin-bottom: .3rem; color: #0f172a; }
        .step-desc  { font-size: .83rem; line-height: 1.5; color: #475569; }
        .step-hint  {
            display: inline-block;
            background: #f1f5f9;
            border-radius: .4rem;
            padding: .2rem .55rem;
            font-size: .78rem;
            font-weight: 700;
            color: #0f172a;
            margin: .3rem .15rem 0 0;
        }
        .step-icon {
            font-size: 1.25rem;
            display: inline-block;
            vertical-align: middle;
            margin-right: .2rem;
        }
        .tip-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: .6rem;
            padding: .85rem 1rem;
            font-size: .8rem;
            color: #166534;
            margin-top: 1.5rem;
            line-height: 1.5;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            color: #10b981;
            font-size: .85rem;
            font-weight: 600;
            text-decoration: none;
            margin-bottom: 1.25rem;
        }
    </style>
</head>
<body>

<div class="header">
    <img src="/icons/icon-192x192.png" alt="Miensa Fleet" class="header-logo">
    <h1>Installer Miensa Fleet</h1>
    <p>Accès rapide depuis votre écran d'accueil</p>
</div>

<div class="tabs">
    <div class="tab active" onclick="showTab('android')">
        <span class="step-icon">🤖</span> Android
    </div>
    <div class="tab" onclick="showTab('ios')">
        <span class="step-icon"></span> iPhone / iPad
    </div>
</div>

{{-- ── Android ── --}}
<div id="panel-android" class="panel active">
    @auth
    <a href="{{ route('dashboard') }}" class="back-link">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        Retour au tableau de bord
    </a>
    @endauth

    <div class="step">
        <div class="step-num">1</div>
        <div class="step-body">
            <div class="step-title">Ouvrez Chrome sur votre Android</div>
            <div class="step-desc">
                Naviguez vers <strong>{{ request()->getHost() }}</strong> dans Google Chrome.
                Assurez-vous d'être connecté à votre compte.
            </div>
        </div>
    </div>

    <div class="step">
        <div class="step-num">2</div>
        <div class="step-body">
            <div class="step-title">Appuyez sur le menu Chrome</div>
            <div class="step-desc">
                Appuyez sur les <span class="step-hint">⋮</span> trois points en haut à droite de Chrome.
            </div>
        </div>
    </div>

    <div class="step">
        <div class="step-num">3</div>
        <div class="step-body">
            <div class="step-title">Choisissez "Ajouter à l'écran d'accueil"</div>
            <div class="step-desc">
                Cherchez l'option <span class="step-hint">Ajouter à l'écran d'accueil</span> ou
                <span class="step-hint">Installer l'application</span> dans le menu.
            </div>
        </div>
    </div>

    <div class="step">
        <div class="step-num">4</div>
        <div class="step-body">
            <div class="step-title">Confirmez l'installation</div>
            <div class="step-desc">
                Appuyez sur <span class="step-hint">Installer</span> puis sur <span class="step-hint">Ajouter</span>.
                L'icône Miensa Fleet apparaîtra sur votre écran d'accueil.
            </div>
        </div>
    </div>

    <div class="tip-box">
        <strong>Conseil :</strong> Si vous voyez une bannière verte "Installer Miensa Fleet" en bas de l'écran,
        appuyez dessus directement — c'est le moyen le plus rapide !
    </div>
</div>

{{-- ── iOS ── --}}
<div id="panel-ios" class="panel">
    @auth
    <a href="{{ route('dashboard') }}" class="back-link">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        Retour au tableau de bord
    </a>
    @endauth

    <div class="step">
        <div class="step-num">1</div>
        <div class="step-body">
            <div class="step-title">Ouvrez Safari sur votre iPhone ou iPad</div>
            <div class="step-desc">
                L'installation PWA n'est disponible que via <strong>Safari</strong>.
                Naviguez vers <strong>{{ request()->getHost() }}</strong>.
            </div>
        </div>
    </div>

    <div class="step">
        <div class="step-num">2</div>
        <div class="step-body">
            <div class="step-title">Appuyez sur le bouton Partager</div>
            <div class="step-desc">
                En bas (iPhone) ou en haut (iPad) de Safari, appuyez sur l'icône
                <span class="step-hint">⬆</span> Partager.
            </div>
        </div>
    </div>

    <div class="step">
        <div class="step-num">3</div>
        <div class="step-body">
            <div class="step-title">Choisissez "Sur l'écran d'accueil"</div>
            <div class="step-desc">
                Faites défiler la liste et appuyez sur
                <span class="step-hint">Sur l'écran d'accueil</span>.
            </div>
        </div>
    </div>

    <div class="step">
        <div class="step-num">4</div>
        <div class="step-body">
            <div class="step-title">Confirmez en appuyant sur "Ajouter"</div>
            <div class="step-desc">
                Appuyez sur <span class="step-hint">Ajouter</span> en haut à droite.
                L'icône Miensa Fleet apparaîtra sur votre écran d'accueil.
            </div>
        </div>
    </div>

    <div class="tip-box">
        <strong>iOS 16.4+ :</strong> Les notifications push sont maintenant disponibles sur iPhone.
        Ouvrez l'app depuis l'écran d'accueil, et acceptez les notifications pour rester informé en temps réel.
    </div>
</div>

<script>
function showTab(name) {
    document.querySelectorAll('.tab').forEach(function(t, i) {
        t.classList.toggle('active', (i === 0 && name === 'android') || (i === 1 && name === 'ios'));
    });
    document.querySelectorAll('.panel').forEach(function(p) {
        p.classList.remove('active');
    });
    document.getElementById('panel-' + name).classList.add('active');
}

// Auto-detect platform
(function () {
    var ua = navigator.userAgent;
    if (/iphone|ipad|ipod/i.test(ua)) showTab('ios');
})();
</script>
</body>
</html>
