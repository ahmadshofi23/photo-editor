<?php

declare(strict_types=1);

namespace App\Modules\Compress\Services;

use App\Models\Image;
use App\Modules\Compress\DTOs\CompressDTO;
use App\Modules\History\Services\HistoryService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class CompressService
{
    public function __construct(
        private readonly HistoryService $historyService
    ) {}

    public function compress(Image $image, CompressDTO $dto): array
    {
        $originalPath = Storage::disk('public')->path($image->original_path);
        $originalSize = filesize($originalPath);

        $driverClass = config('image.driver', \Intervention\Image\Drivers\Gd\Driver::class);
        $manager = new ImageManager(new $driverClass());
        $img = $manager->read($originalPath);

        $fileName = pathinfo($image->original_path, PATHINFO_FILENAME);
        $extension = $dto->convertWebp ? 'webp' : $image->extension;
        
        $editedName = $fileName . '_compressed_' . Str::random(5) . '.' . $extension;
        $editedRelativePath = 'uploads/processed/' . $editedName;
        $editedAbsolutePath = Storage::disk('public')->path($editedRelativePath);

        Storage::disk('public')->makeDirectory('uploads/processed');

        if ($dto->convertWebp) {
            $img->toWebp($dto->quality)->save($editedAbsolutePath);
        } else {
            $img->save($editedAbsolutePath, quality: $dto->quality);
        }

        $newSize = filesize($editedAbsolutePath);
        $reduction = $originalSize > 0 ? (($originalSize - $newSize) / $originalSize) * 100 : 0;

        // Update database
        $image->update([
            'edited_path' => $editedRelativePath,
            'size' => $newSize,
            'mime_type' => $dto->convertWebp ? 'image/webp' : $image->mime_type,
            'extension' => $extension,
        ]);

        // Log history
        $this->historyService->log($image->id, 'compressed', [
            'quality' => $dto->quality,
            'converted_to_webp' => $dto->convertWebp,
            'original_size' => $originalSize,
            'new_size' => $newSize,
            'reduction_percentage' => round($reduction, 2),
            'generated_path' => $editedRelativePath
        ]);

        return [
            'image' => $image,
            'original_size' => $originalSize,
            'new_size' => $newSize,
            'reduction_percentage' => round($reduction, 2)
        ];
    }
}
