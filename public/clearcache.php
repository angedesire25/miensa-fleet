<?php
define('LARAVEL_START', microtime(true));
require_once dirname(__DIR__) . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

\Illuminate\Support\Facades\Artisan::call('route:clear');
\Illuminate\Support\Facades\Artisan::call('config:clear');
\Illuminate\Support\Facades\Artisan::call('view:clear');

echo "✅ Caches vidés (routes, config, vues). Supprimez ce fichier maintenant.";
