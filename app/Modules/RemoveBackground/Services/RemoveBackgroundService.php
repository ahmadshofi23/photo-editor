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

    public function remove(Image $image, RemoveBackgroundDTO $dto): Image
    {
        $rembgBin = $this->findRembg();

        $sourcePath   = $image->edited_path ?? $image->original_path;
        $absolutePath = Storage::disk('public')->path($sourcePath);

        $fileName      = pathinfo($image->original_path, PATHINFO_FILENAME);
        $editedName    = $fileName . '_removebg_' . Str::random(5) . '.png';
        $editedRelPath = 'uploads/processed/' . $editedName;
        $editedAbsPath = Storage::disk('public')->path($editedRelPath);

        Storage::disk('public')->makeDirectory('uploads/processed');

        $inputEscaped  = escapeshellarg($absolutePath);
        $outputEscaped = escapeshellarg($editedAbsPath);
        $cmd           = "U2NET_HOME=/opt/rembg-models $rembgBin i $inputEscaped $outputEscaped 2>&1";

        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0 || !file_exists($editedAbsPath)) {
            $detail = implode(' ', $output);
            throw new \RuntimeException('Gagal menghapus latar: ' . $detail);
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
