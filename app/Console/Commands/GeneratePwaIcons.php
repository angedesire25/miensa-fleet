<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Storage;

class GeneratePwaIcons extends Command
{
    protected $signature   = 'pwa:icons {--force : Regénérer même si les icônes existent déjà}';
    protected $description = 'Génère toutes les icônes PWA (PNG) dans public/icons/';

    private const SIZES = [72, 96, 128, 144, 152, 192, 384, 512];

    public function handle(): int
    {
        if (! function_exists('imagecreatetruecolor')) {
            $this->error('Extension GD non disponible. Installez php-gd et relancez.');
            return 1;
        }

        $outDir = public_path('icons');
        if (! is_dir($outDir)) {
            mkdir($outDir, 0755, true);
        }

        // ── Trouver la source ─────────────────────────────────────────────
        $sourcePath = $this->resolveSourceLogo();
        $useSource  = $sourcePath !== null;

        if ($useSource) {
            $this->info("Source logo : {$sourcePath}");
        } else {
            $this->warn('Aucun logo source trouvé — génération d\'une icône de marque MF.');
        }

        $bar = $this->output->createProgressBar(count(self::SIZES));
        $bar->start();

        foreach (self::SIZES as $size) {
            $dest = "{$outDir}/icon-{$size}x{$size}.png";

            if (file_exists($dest) && ! $this->option('force')) {
                $bar->advance();
                continue;
            }

            $canvas = $useSource
                ? $this->resizeLogo($sourcePath, $size)
                : $this->generateBrandedIcon($size);

            if ($canvas) {
                imagepng($canvas, $dest, 9);
                imagedestroy($canvas);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Icônes générées dans public/icons/');

        return 0;
    }

    private function resolveSourceLogo(): ?string
    {
        // 1. Logo depuis AppSetting (stocké en DB/storage)
        try {
            $logoSetting = AppSetting::get('logo');
            if ($logoSetting) {
                $path = Storage::disk('public')->path($logoSetting);
                if (file_exists($path)) {
                    return $path;
                }
            }
        } catch (\Throwable) {}

        // 2. Fichiers statiques connus
        $candidates = [
            public_path('images/logo.png'),
            public_path('images/logo.jpg'),
            public_path('images/logo.jpeg'),
            resource_path('images/logo.png'),
        ];

        foreach ($candidates as $p) {
            if (file_exists($p)) {
                return $p;
            }
        }

        return null;
    }

    private function resizeLogo(string $sourcePath, int $size): \GdImage|false
    {
        $ext = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));

        $src = match ($ext) {
            'png'  => imagecreatefrompng($sourcePath),
            'jpg', 'jpeg' => imagecreatefromjpeg($sourcePath),
            'gif'  => imagecreatefromgif($sourcePath),
            default => false,
        };

        if (! $src) {
            return $this->generateBrandedIcon($size);
        }

        $srcW = imagesx($src);
        $srcH = imagesy($src);

        // Canvas carré avec fond #0f172a (foncé) pour les icônes maskable
        $canvas = imagecreatetruecolor($size, $size);
        imagealphablending($canvas, true);
        imagesavealpha($canvas, true);

        $bg = imagecolorallocate($canvas, 15, 23, 42); // #0f172a
        imagefill($canvas, 0, 0, $bg);

        // Calculer la zone de dessin avec padding 15%
        $pad   = (int) ($size * 0.15);
        $inner = $size - ($pad * 2);

        // Ratio source
        $ratio    = min($inner / $srcW, $inner / $srcH);
        $destW    = (int) ($srcW * $ratio);
        $destH    = (int) ($srcH * $ratio);
        $offsetX  = $pad + (int) (($inner - $destW) / 2);
        $offsetY  = $pad + (int) (($inner - $destH) / 2);

        imagecopyresampled($canvas, $src, $offsetX, $offsetY, 0, 0, $destW, $destH, $srcW, $srcH);
        imagedestroy($src);

        return $canvas;
    }

    private function generateBrandedIcon(int $size): \GdImage
    {
        $img = imagecreatetruecolor($size, $size);

        // Fond #10b981 (vert émeraude)
        $green = imagecolorallocate($img, 16, 185, 129);
        imagefill($img, 0, 0, $green);

        // Coins arrondis simulés (carré foncé aux coins)
        $radius = (int) ($size * 0.18);
        $dark   = imagecolorallocate($img, 15, 23, 42);
        $this->fillRoundedCorners($img, $size, $radius, $dark);

        // Texte "MF" centré en blanc
        $white    = imagecolorallocate($img, 255, 255, 255);
        $fontSize = (int) ($size * 0.3);
        $text     = 'MF';

        $fontFile = $this->findFont();

        if ($fontFile) {
            $bbox    = imagettfbbox($fontSize, 0, $fontFile, $text);
            $textW   = $bbox[2] - $bbox[0];
            $textH   = $bbox[1] - $bbox[7];
            $x       = (int) (($size - $textW) / 2);
            $y       = (int) (($size + $textH) / 2);
            imagettftext($img, $fontSize, 0, $x, $y, $white, $fontFile, $text);
        } else {
            // Fallback : police interne GD
            $gfSize = $size > 128 ? 5 : ($size > 64 ? 4 : 3);
            $charW  = imagefontwidth($gfSize);
            $charH  = imagefontheight($gfSize);
            $x      = (int) (($size - $charW * 2) / 2);
            $y      = (int) (($size - $charH) / 2);
            imagestring($img, $gfSize, $x, $y, $text, $white);
        }

        return $img;
    }

    private function fillRoundedCorners(\GdImage $img, int $size, int $r, int $color): void
    {
        // Remplir les carrés de coin puis repercer avec un arc blanc pour simuler
        // (en GD pur, on peint les coins de couleur background)
        for ($x = 0; $x < $r; $x++) {
            for ($y = 0; $y < $r; $y++) {
                $dist = sqrt(($x - $r) ** 2 + ($y - $r) ** 2);
                if ($dist > $r) {
                    imagesetpixel($img, $x, $y, $color);
                    imagesetpixel($img, $size - 1 - $x, $y, $color);
                    imagesetpixel($img, $x, $size - 1 - $y, $color);
                    imagesetpixel($img, $size - 1 - $x, $size - 1 - $y, $color);
                }
            }
        }
    }

    private function findFont(): string|false
    {
        $candidates = [
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
            'C:\\Windows\\Fonts\\arialbd.ttf',
            'C:\\Windows\\Fonts\\Arial Bold.ttf',
            'C:\\laragon\\bin\\php\\php8.3.x64\\extras\\fonts\\arial.ttf',
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return false;
    }
}
