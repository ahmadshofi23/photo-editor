<?php

declare(strict_types=1);

namespace App\Modules\Download\Services;

use App\Models\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class DownloadService
{
    public function generateSignedUrl(Image $image, string $type = 'edited'): string
    {
        $path = $type === 'edited' ? $image->edited_path : $image->original_path;
        if (!$path) {
            throw new \InvalidArgumentException('Image path not found.');
        }

        return URL::temporarySignedRoute(
            'api.download.secure', 
            now()->addMinutes(60),
            ['image' => $image->id, 'type' => $type]
        );
    }

    public function getFilePath(Image $image, string $type): string
    {
        $path = $type === 'edited' ? $image->edited_path : $image->original_path;
        return Storage::disk('public')->path($path);
    }
}
