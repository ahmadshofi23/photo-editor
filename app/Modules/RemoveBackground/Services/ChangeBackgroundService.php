<?php

declare(strict_types=1);

namespace App\Modules\RemoveBackground\Services;

use App\Models\Image;
use App\Modules\RemoveBackground\DTOs\ChangeBackgroundDTO;
use App\Modules\History\Services\HistoryService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ChangeBackgroundService
{
    public function __construct(
        private readonly HistoryService $historyService
    ) {}

    public function change(Image $image, ChangeBackgroundDTO $dto): Image
    {
        // Always composite onto the transparent PNG from remove_background.
        // If we used edited_path directly, a second color change would place an opaque
        // JPEG (previous color result) over the new canvas — completely hiding it.
        $removeBgHistory = $image->histories()
            ->where('action_type', 'remove_background')
            ->orderByDesc('id')
            ->first();

        if ($removeBgHistory && isset($removeBgHistory->metadata['generated_path'])) {
            $sourcePath = $removeBgHistory->metadata['generated_path'];
        } else {
            $sourcePath = $image->edited_path ?? $image->original_path;
        }

        $absolutePath = Storage::disk('public')->path($sourcePath);

        $driverClass = config('image.driver', \Intervention\Image\Drivers\Gd\Driver::class);
        $manager     = new ImageManager(new $driverClass());

        $foreground = $manager->read($absolutePath);
        $w          = $foreground->width();
        $h          = $foreground->height();

        if ($dto->bgType === 'color') {
            // Create a solid-color canvas and place the transparent foreground on top
            $canvas = $manager->create($w, $h)->fill($dto->bgColor);
            $canvas->place($foreground, 'top-left', 0, 0);
        } else {
            // Upload a custom background image, scale it to cover, then composite
            $canvas = $manager->read($dto->bgImagePath);
            $canvas->cover($w, $h);
            $canvas->place($foreground, 'top-left', 0, 0);
        }

        $fileName      = pathinfo($image->original_path, PATHINFO_FILENAME);
        $suffix        = $dto->bgType === 'color' ? 'bg_color' : 'bg_img';
        $editedName    = $fileName . '_' . $suffix . '_' . Str::random(5) . '.jpg';
        $editedRelPath = 'uploads/processed/' . $editedName;
        $editedAbsPath = Storage::disk('public')->path($editedRelPath);

        Storage::disk('public')->makeDirectory('uploads/processed');
        $canvas->save($editedAbsPath, quality: 95);

        $image->update([
            'edited_path' => $editedRelPath,
            'mime_type'   => 'image/jpeg',
            'extension'   => 'jpg',
            'size'        => filesize($editedAbsPath),
            'width'       => $w,
            'height'      => $h,
        ]);

        $this->historyService->log($image->id, 'change_background', [
            'bg_type'        => $dto->bgType,
            'bg_color'       => $dto->bgColor,
            'width'          => $w,
            'height'         => $h,
            'generated_path' => $editedRelPath,
        ]);

        return $image->fresh();
    }
}
