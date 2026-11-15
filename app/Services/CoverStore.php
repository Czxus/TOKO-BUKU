<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Sampul buku: dikompres otomatis ke WebP (sisi terpanjang maks 800 px, kualitas 80),
 * disimpan di disk `public` per tenant. Nama berkas acak (UUID), bukan nama dari klien.
 */
final class CoverStore
{
    public const MAX_SIDE = 800;

    private const MAX_SOURCE_PIXELS = 24_000_000;

    public function store(UploadedFile $file, int $tenantId): string
    {
        if (! function_exists('imagewebp')) {
            throw new RuntimeException('Ekstensi PHP GD dengan dukungan WebP belum aktif.');
        }

        $realPath = $file->getRealPath();
        $info = @getimagesize($realPath);

        if ($info === false || ($info[0] * $info[1]) > self::MAX_SOURCE_PIXELS) {
            throw new RuntimeException('Berkas bukan gambar yang valid atau terlalu besar.');
        }

        $source = @imagecreatefromstring((string) file_get_contents($realPath));
        if ($source === false) {
            throw new RuntimeException('Berkas bukan gambar yang valid.');
        }

        imagepalettetotruecolor($source);

        [$width, $height] = [imagesx($source), imagesy($source)];
        $scale = min(1, self::MAX_SIDE / max($width, $height));

        $target = $scale < 1
            ? imagescale($source, max(1, (int) round($width * $scale)), max(1, (int) round($height * $scale)))
            : $source;

        if ($target === false) {
            throw new RuntimeException('Gambar gagal diperkecil.');
        }

        imagealphablending($target, false);
        imagesavealpha($target, true);

        ob_start();
        imagewebp($target, null, 80);
        $webp = (string) ob_get_clean();

        $path = "covers/{$tenantId}/".Str::uuid().'.webp';
        Storage::disk('public')->put($path, $webp);

        return $path;
    }

    public function delete(?string $path): void
    {
        // Hanya hapus berkas yang memang dibuat oleh CoverStore.
        if ($path !== null && str_starts_with($path, 'covers/')) {
            Storage::disk('public')->delete($path);
        }
    }
}
