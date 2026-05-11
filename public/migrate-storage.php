<?php
/**
 * Script de migration : storage/app/public/ → public/uploads/
 * À SUPPRIMER du serveur immédiatement après utilisation.
 */

$source = dirname(__DIR__) . '/storage/app/public';
$dest   = __DIR__ . '/uploads';

if (!is_dir($source)) {
    echo "❌ Dossier source introuvable : {$source}";
    exit(1);
}

if (!is_dir($dest)) {
    mkdir($dest, 0755, true);
}

$count  = 0;
$errors = 0;

function copyDir(string $src, string $dst): array
{
    $count  = 0;
    $errors = 0;

    if (!is_dir($dst)) {
        mkdir($dst, 0755, true);
    }

    $items = scandir($src);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;

        $srcPath = $src . '/' . $item;
        $dstPath = $dst . '/' . $item;

        if (is_dir($srcPath)) {
            [$c, $e] = copyDir($srcPath, $dstPath);
            $count  += $c;
            $errors += $e;
        } else {
            if (copy($srcPath, $dstPath)) {
                $count++;
                echo "✅ " . str_replace(dirname(__DIR__), '', $srcPath) . "<br>\n";
            } else {
                $errors++;
                echo "❌ ERREUR : " . str_replace(dirname(__DIR__), '', $srcPath) . "<br>\n";
            }
        }
    }

    return [$count, $errors];
}

echo "<pre>\n";
echo "Migration storage/app/public/ → public/uploads/\n";
echo str_repeat('─', 50) . "\n\n";

[$count, $errors] = copyDir($source, $dest);

echo "\n" . str_repeat('─', 50) . "\n";
echo "✅ {$count} fichier(s) copié(s)\n";
if ($errors > 0) {
    echo "❌ {$errors} erreur(s)\n";
}
echo "\n⚠ SUPPRIMEZ ce fichier du serveur immédiatement !\n";
echo "</pre>\n";
