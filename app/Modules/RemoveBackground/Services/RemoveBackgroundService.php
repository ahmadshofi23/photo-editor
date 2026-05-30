<?php

declare(strict_types=1);

namespace App\Modules\RemoveBackground\Services;

use App\Models\Image;
use App\Modules\RemoveBackground\DTOs\RemoveBackgroundDTO;
use App\Modules\History\Services\HistoryService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RemoveBackgroundService
{
    public function __construct(
        private readonly HistoryService $historyService
    ) {}

    // Maksimal panjang sisi gambar sebelum diproses rembg (hemat RAM di Railway)
    private const MAX_REMBG_SIZE = 512;

    public function remove(Image $image, RemoveBackgroundDTO $dto): Image
    {
        $rembgBin = $this->findRembg();

        $sourcePath   = $image->edited_path ?? $image->original_path;
        $absolutePath = Storage::disk('public')->path($sourcePath);

        if (!file_exists($absolutePath)) {
            throw new \RuntimeException('File gambar tidak ditemukan di server: ' . $absolutePath);
        }

        $fileName      = pathinfo($image->original_path, PATHINFO_FILENAME);
        $editedName    = $fileName . '_removebg_' . Str::random(5) . '.png';
        $editedRelPath = 'uploads/processed/' . $editedName;
        $editedAbsPath = Storage::disk('public')->path($editedRelPath);

        Storage::disk('public')->makeDirectory('uploads/processed');

        [$origW, $origH] = @getimagesize($absolutePath) ?: [0, 0];
        $needsResize = ($origW > self::MAX_REMBG_SIZE || $origH > self::MAX_REMBG_SIZE);

        // Resize gambar dulu jika terlalu besar agar tidak OOM di server
        $rembgInput  = $needsResize ? $this->prepareResizedInput($absolutePath) : $absolutePath;
        $rembgOutput = $needsResize
            ? sys_get_temp_dir() . '/rembg_out_' . uniqid() . '.png'
            : $editedAbsPath;

        $inputEscaped  = escapeshellarg($rembgInput);
        $outputEscaped = escapeshellarg($rembgOutput);
        $u2netHome = is_dir('/opt/rembg-models') ? '/opt/rembg-models' : (getenv('HOME') ?: sys_get_temp_dir());
        $cmd       = "U2NET_HOME=" . escapeshellarg($u2netHome) . " $rembgBin i -m u2netp $inputEscaped $outputEscaped 2>&1";

        exec($cmd, $output, $exitCode);

        if ($rembgInput !== $absolutePath && file_exists($rembgInput)) {
            @unlink($rembgInput);
        }

        if ($exitCode !== 0 || !file_exists($rembgOutput)) {
            $detail = implode(' ', $output);
            throw new \RuntimeException('Gagal menghapus latar: ' . $detail);
        }

        // Jika di-resize, upscale mask alpha kembali ke ukuran original menggunakan ImageMagick
        if ($needsResize && $origW > 0) {
            $resizeCmd = 'convert ' . escapeshellarg($rembgOutput)
                . ' -resize ' . (int)$origW . 'x' . (int)$origH . '!'
                . ' ' . escapeshellarg($editedAbsPath) . ' 2>&1';
            exec($resizeCmd, $resizeOut, $resizeExit);
            @unlink($rembgOutput);

            if ($resizeExit !== 0 || !file_exists($editedAbsPath)) {
                // Fallback: pakai hasil kecil jika upscale gagal
                rename($rembgOutput, $editedAbsPath);
            }
        }

        [$width, $height] = @getimagesize($editedAbsPath) ?: [$image->width, $image->height];

        $image->update([
            'edited_path' => $editedRelPath,
            'mime_type'   => 'image/png',
            'extension'   => 'png',
            'size'        => filesize($editedAbsPath),
            'width'       => $width,
            'height'      => $height,
        ]);

        $this->historyService->log($image->id, 'remove_background', [
            'width'          => $width,
            'height'         => $height,
            'generated_path' => $editedRelPath,
        ]);

        return $image->fresh();
    }

    private function prepareResizedInput(string $absolutePath): string
    {
        [$origW, $origH] = @getimagesize($absolutePath) ?: [0, 0];

        if ($origW <= self::MAX_REMBG_SIZE && $origH <= self::MAX_REMBG_SIZE) {
            return $absolutePath;
        }

        $tmpPath = sys_get_temp_dir() . '/rembg_input_' . uniqid() . '.jpg';
        $size    = self::MAX_REMBG_SIZE . 'x' . self::MAX_REMBG_SIZE;

        // Pakai ImageMagick (convert) — jauh lebih hemat RAM dari PHP GD untuk gambar besar
        $cmd    = 'convert ' . escapeshellarg($absolutePath) . ' -resize ' . escapeshellarg($size) . '\\> -quality 92 ' . escapeshellarg($tmpPath) . ' 2>&1';
        $output = [];
        $exit   = -1;
        exec($cmd, $output, $exit);

        if ($exit === 0 && file_exists($tmpPath)) {
            return $tmpPath;
        }

        // Fallback ke PHP GD jika ImageMagick tidak tersedia
        $src = @imagecreatefromjpeg($absolutePath)
            ?: @imagecreatefrompng($absolutePath)
            ?: @imagecreatefromwebp($absolutePath);

        if (!$src) {
            return $absolutePath;
        }

        $ratio = min(self::MAX_REMBG_SIZE / $origW, self::MAX_REMBG_SIZE / $origH);
        $newW  = (int) round($origW * $ratio);
        $newH  = (int) round($origH * $ratio);
        $dst   = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
        imagejpeg($dst, $tmpPath, 92);
        imagedestroy($src);
        imagedestroy($dst);

        return file_exists($tmpPath) ? $tmpPath : $absolutePath;
    }

    private function findRembg(): string
    {
        $candidates = [
            trim((string) shell_exec('which rembg 2>/dev/null')),
            '/usr/local/bin/rembg',
            '/usr/bin/rembg',
            // local dev (Mac)
            '/Users/mac/.pyenv/versions/3.12.2/bin/rembg',
            'rembg',
        ];

        foreach ($candidates as $bin) {
            if ($bin && is_executable($bin)) {
                return $bin;
            }
        }

        throw new \RuntimeException('rembg tidak ditemukan. Jalankan: pip3 install rembg');
    }
}
