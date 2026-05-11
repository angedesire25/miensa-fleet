<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
<meta name="theme-color" content="#10b981">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<title>Hors ligne — MiensaFleet</title>
<link rel="manifest" href="/manifest.json">
<link rel="apple-touch-icon" href="/icons/icon-192x192.png">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
    background: #0f172a;
    color: #f1f5f9;
    min-height: 100vh;
    min-height: 100dvh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 24px 20px;
    text-align: center;
  }

  /* ── Logo ── */
  .logo-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-bottom: 32px;
  }
  .logo-icon {
    width: 52px; height: 52px;
    background: linear-gradient(135deg, #10b981, #059669);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px;
    box-shadow: 0 4px 16px rgba(16,185,129,.35);
  }
  .logo-text {
    font-size: 22px; font-weight: 800; letter-spacing: -.3px;
  }
  .logo-text span { color: #10b981; }

  /* ── Icône WiFi animée ── */
  .wifi-container {
    position: relative;
    width: 100px; height: 100px;
    margin: 0 auto 28px;
  }
  .wifi-icon {
    animation: pulseWifi 2s ease-in-out infinite;
  }
  @keyframes pulseWifi {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: .4; transform: scale(.9); }
  }
  .slash-line {
    position: absolute;
    top: 50%; left: 50%;
    width: 4px; height: 110px;
    background: #ef4444;
    border-radius: 2px;
    transform: translate(-50%, -50%) rotate(-45deg);
    transform-origin: center;
    box-shadow: 0 0 8px rgba(239,68,68,.5);
  }

  /* ── Texte ── */
  h1 {
    font-size: 22px; font-weight: 700;
    margin-bottom: 10px;
  }
  .subtitle {
    font-size: 14px; color: #94a3b8;
    line-height: 1.6; margin-bottom: 32px;
    max-width: 300px;
  }
  .sync-info {
    display: flex; align-items: center; gap: 8px;
    background: #1e293b; border: 1px solid #334155;
    border-radius: 10px; padding: 12px 16px;
    margin-bottom: 28px; max-width: 320px;
    font-size: 13px; color: #94a3b8; text-align: left;
  }
  .sync-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: #10b981; flex-shrink: 0;
    animation: blink 1.5s ease-in-out infinite;
  }
  @keyframes blink {
    0%, 100% { opacity: 1; }
    50%       { opacity: .2; }
  }

  /* ── Bouton ── */
  .btn-retry {
    background: #10b981; color: #fff;
    border: none; border-radius: 12px;
    padding: 14px 32px; font-size: 16px; font-weight: 700;
    cursor: pointer; width: 100%; max-width: 280px;
    box-shadow: 0 4px 16px rgba(16,185,129,.35);
    transition: opacity .2s, transform .1s;
    display: flex; align-items: center; justify-content: center; gap: 8px;
  }
  .btn-retry:active { opacity: .85; transform: scale(.97); }

  /* ── Liste fonctions disponibles ── */
  .available {
    margin-top: 36px; padding: 16px;
    background: #1e293b; border-radius: 12px;
    max-width: 300px; width: 100%; text-align: left;
  }
  .available h3 {
    font-size: 11px; font-weight: 700; color: #10b981;
    letter-spacing: .08em; text-transform: uppercase;
    margin-bottom: 10px;
  }
  .available-item {
    display: flex; align-items: center; gap: 8px;
    font-size: 13px; color: #64748b; padding: 5px 0;
  }
  .available-item::before { content: '✓'; color: #10b981; font-weight: 700; }
</style>
</head>
<body>

  {{-- Logo --}}
  <div class="logo-wrap">
    <div class="logo-icon">🚗</div>
    <div class="logo-text">Miensa<span>Fleet</span></div>
  </div>

  {{-- Icône WiFi barré animée --}}
  <div class="wifi-container" role="img" aria-label="Sans connexion">
    <svg class="wifi-icon" width="100" height="100" viewBox="0 0 100 100" fill="none">
      <path d="M10 38C22 26 36 20 50 20c14 0 28 6 40 18" stroke="#334155" stroke-width="7" stroke-linecap="round"/>
      <path d="M20 52c8-8 18-13 30-13s22 5 30 13" stroke="#334155" stroke-width="7" stroke-linecap="round"/>
      <path d="M32 66c5-5 11-8 18-8s13 3 18 8" stroke="#475569" stroke-width="7" stroke-linecap="round"/>
      <circle cx="50" cy="80" r="5" fill="#64748b"/>
    </svg>
    <div class="slash-line"></div>
  </div>

  <h1>Vous êtes hors ligne</h1>

  <p class="subtitle">
    La connexion au serveur MiensaFleet est indisponible.<br>
    Votre travail reste accessible en mode hors ligne.
  </p>

  <div class="sync-info">
    <div class="sync-dot"></div>
    <span>Vos fiches seront synchronisées automatiquement au retour du réseau.</span>
  </div>

  <button class="btn-retry" onclick="retry()">
    <svg width="18" height="18" fill="none" viewBox="0 0 24 24">
      <path d="M23 4v6h-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
      <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    Réessayer
  </button>

  <div class="available">
    <h3>Disponible hors ligne</h3>
    <div class="available-item">Tableau de bord (dernière version)</div>
    <div class="available-item">Fiches de contrôle sauvegardées</div>
    <div class="available-item">Mes affectations en cache</div>
    <div class="available-item">Synchronisation automatique</div>
  </div>

<script>
  function retry() {
    var btn = document.querySelector('.btn-retry');
    btn.textContent = 'Vérification…';
    btn.disabled = true;
    setTimeout(function () {
      if (navigator.onLine) {
        window.location.href = '/dashboard';
      } else {
        window.location.reload();
      }
    }, 800);
  }

  // Auto-rediriger quand le réseau revient
  window.addEventListener('online', function () {
    window.location.href = '/dashboard';
  });
</script>
</body>
</html>
