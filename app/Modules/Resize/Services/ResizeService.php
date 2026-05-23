<?php

declare(strict_types=1);

namespace App\Modules\Resize\Services;

use App\Models\Image;
use App\Modules\History\Services\HistoryService;
use App\Modules\Resize\DTOs\ResizeDTO;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ResizeService
{
    private const PRESETS = [
        'passport' => [413, 531],
        'instagram_post' => [1080, 1080],
        'instagram_story' => [1080, 1920],
        'youtube_thumb' => [1280, 720],
        'facebook_cover' => [820, 312],
    ];

    public function __construct(
        private readonly HistoryService $historyService
    ) {}

    public function resize(Image $image, ResizeDTO $dto): Image
    {
        // Prefer the latest edited version as the source (enables chaining: B&W → resize)
        $sourcePath = $image->edited_path ?? $image->original_path;
        $absolutePath = Storage::disk('public')->path($sourcePath);
        $driverClass = config('image.driver', \Intervention\Image\Drivers\Gd\Driver::class);
        $manager = new ImageManager(new $driverClass());
        $img = $manager->read($absolutePath);
        
        // Ensure image is oriented correctly according to EXIF data before cropping
        // Browsers auto-orient images, so CropperJS coordinates are based on the oriented image.
        $img->orient();

        $width = $dto->width;
        $height = $dto->height;

        \Illuminate\Support\Facades\Log::debug('ResizeService Crop Data:', [
            'cropWidth'   => $dto->cropWidth,
            'cropHeight'  => $dto->cropHeight,
            'cropX'       => $dto->cropX,
            'cropY'       => $dto->cropY,
            'imageWidth'  => $img->width(),
            'imageHeight' => $img->height(),
        ]);

        if ($dto->cropWidth && $dto->cropHeight) {
            // Clamp crop box to image boundaries (CropperJS can return negative offsets)
            $cropX = max(0, $dto->cropX ?? 0);
            $cropY = max(0, $dto->cropY ?? 0);
            $cropW = min($dto->cropWidth, $img->width() - $cropX);
            $cropH = min($dto->cropHeight, $img->height() - $cropY);

            // 1. Crop exactly as the user drew the crop box in CropperJS
            $img->crop($cropW, $cropH, $cropX, $cropY);

            // 2. Only scale to target dimensions if a preset/custom size was chosen.
            //    Use scale() (not resize()) so the image is resampled at high quality
            //    without distortion — critical for print output.
            if ($width && $height) {
                // Scale to fit within target box while keeping pixel-perfect sharpness
                $img->scale(width: $width, height: $height);
            }
        } elseif ($dto->preset && array_key_exists($dto->preset, self::PRESETS)) {
            [$width, $height] = self::PRESETS[$dto->preset];
            $img->cover($width, $height);
        } else {
            if ($width === null && $height === null) {
                // nothing
            } else {
                if ($dto->mode === 'cover' && $width && $height) {
                    $img->cover($width, $height);
                } elseif ($dto->mode === 'crop' && $width && $height) {
                    $img->crop($width, $height);
                } elseif ($dto->mode === 'stretch' || !$dto->maintainRatio) {
                    $img->resize($width, $height);
                } else {
                    $img->scale(width: $width, height: $height);
                }
            }
        }

        // Save new image
        $fileName = pathinfo($image->original_path, PATHINFO_FILENAME);
        $editedName = $fileName . '_resize_' . Str::random(5) . '.' . $image->extension;
        $editedRelativePath = 'uploads/processed/' . $editedName;
        $editedAbsolutePath = Storage::disk('public')->path($editedRelativePath);

        Storage::disk('public')->makeDirectory('uploads/processed');

        // Save at maximum quality to preserve sharpness for printing.
        // dto->quality default is 90; we clamp minimum at 92 for print-grade output.
        $saveQuality = max((int) $dto->quality, 92);
        $img->save($editedAbsolutePath, quality: $saveQuality);

        // Update database
        $image->update([
            'edited_path' => $editedRelativePath,
            'width' => $img->width(),
            'height' => $img->height()
        ]);

        // Log history
        $this->historyService->log($image->id, 'resized', [
            'width' => $img->width(),
            'height' => $img->height(),
            'mode' => $dto->preset ? 'preset_'.$dto->preset : $dto->mode,
            'quality' => $dto->quality,
            'generated_path' => $editedRelativePath
        ]);

        return $image;
    }
}
