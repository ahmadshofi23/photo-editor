<?php

declare(strict_types=1);

namespace App\Modules\AutoContrast\Services;

use App\Models\Image;
use App\Modules\History\Services\HistoryService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AutoContrastService
{
    public function __construct(
        private readonly HistoryService $historyService
    ) {}

    public function enhance(Image $image): Image
    {
        $magick = $this->findMagick();

        $sourcePath   = $image->edited_path ?? $image->original_path;
        $absolutePath = Storage::disk('public')->path($sourcePath);

        $ext          = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        $outputExt    = in_array($ext, ['png', 'webp']) ? $ext : 'jpg';

        $fileName      = pathinfo($image->original_path, PATHINFO_FILENAME);
        $editedName    = $fileName . '_autocontrast_' . Str::random(5) . '.' . $outputExt;
        $editedRelPath = 'uploads/processed/' . $editedName;
        $editedAbsPath = Storage::disk('public')->path($editedRelPath);

        Storage::disk('public')->makeDirectory('uploads/processed');

        // -contrast-stretch 2%x1%  → clips 2% darkest / 1% brightest pixels then stretches to full range
        // -colorspace sRGB          → ensures output stays in sRGB after internal Lab processing
        // -quality 95               → high-quality output
        $input   = escapeshellarg($absolutePath);
        $output  = escapeshellarg($editedAbsPath);
        $cmd     = "$magick $input -contrast-stretch 2%x1% -colorspace sRGB -quality 95 $output 2>&1";

        exec($cmd, $cmdOutput, $exitCode);

        if ($exitCode !== 0 || !file_exists($editedAbsPath)) {
            throw new \RuntimeException('Auto contrast gagal: ' . implode(' ', $cmdOutput));
        }

        [$width, $height] = @getimagesize($editedAbsPath) ?: [$image->width, $image->height];

        $image->update([
            'edited_path' => $editedRelPath,
            'size'        => filesize($editedAbsPath),
            'width'       => $width,
            'height'      => $height,
        ]);

        $this->historyService->log($image->id, 'auto_contrast', [
            'width'          => $width,
            'height'         => $height,
            'generated_path' => $editedRelPath,
        ]);

        return $image->fresh();
    }

    private function findMagick(): string
    {
        $candidates = [
            '/opt/homebrew/bin/magick',
            '/usr/local/bin/magick',
            '/usr/bin/magick',
            trim((string) shell_exec('which magick 2>/dev/null')),
            'magick',
        ];

        foreach ($candidates as $bin) {
            if ($bin && is_executable($bin)) {
                return $bin;
            }
        }

        throw new \RuntimeException('ImageMagick tidak ditemukan. Jalankan: brew install imagemagick');
    }
}
