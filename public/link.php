<?php
$target = dirname(__DIR__) . '/storage/app/public';
$link   = __DIR__ . '/storage';

if (is_link($link)) {
    echo "✅ Lien déjà existant : public/storage → " . readlink($link);
} elseif (symlink($target, $link)) {
    echo "✅ Lien créé avec succès : public/storage → " . $target;
} else {
    echo "❌ Échec de la création du lien symbolique. Essaie via SSH : php artisan storage:link";
}
