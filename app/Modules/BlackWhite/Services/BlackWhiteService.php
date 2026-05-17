<?php

declare(strict_types=1);

namespace App\Modules\BlackWhite\Services;

use App\Models\Image;
use App\Modules\BlackWhite\DTOs\BlackWhiteDTO;
use App\Modules\History\Services\HistoryService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class BlackWhiteService
{
    public function __construct(
        private readonly HistoryService $historyService
    ) {}

    public function convert(Image $image, BlackWhiteDTO $dto): Image
    {
        // Prefer the latest edited version as the source (enables chaining: resize → B&W)
        $sourcePath = $image->edited_path ?? $image->original_path;
        $absolutePath = Storage::disk('public')->path($sourcePath);
        
        $driverClass = config('image.driver', \Intervention\Image\Drivers\Gd\Driver::class);
        $manager = new ImageManager(new $driverClass());
        $img = $manager->read($absolutePath);
        $img->orient();

        // Apply Intensity (simulated with opacity or mixing original, but v3 core greyscale is just greyscale)
        // If intensity < 100, we might need a more advanced mix, but we'll stick to full greyscale for simplicity or use blend.
        $img->greyscale();

        if ($dto->brightness !== 0) {
            $img->brightness($dto->brightness);
        }

        if ($dto->contrast !== 0) {
            $img->contrast($dto->contrast);
        }

        if ($dto->sharpen) {
            $img->sharpen(10); // Standard sharpen amount
        }

        // Save new image
        $fileName = pathinfo($image->original_path, PATHINFO_FILENAME);
        $editedName = $fileName . '_bw_' . Str::random(5) . '.' . $image->extension;
        $editedRelativePath = 'uploads/processed/' . $editedName;
        $editedAbsolutePath = Storage::disk('public')->path($editedRelativePath);
        
        // Ensure directory exists
        Storage::disk('public')->makeDirectory('uploads/processed');

        $img->save($editedAbsolutePath);

        // Update database
        $image->update([
            'edited_path' => $editedRelativePath,
        ]);

        // Log history
        $this->historyService->log($image->id, 'black_white_converted', [
            'intensity' => $dto->intensity,
            'brightness' => $dto->brightness,
            'contrast' => $dto->contrast,
            'sharpen' => $dto->sharpen,
            'generated_path' => $editedRelativePath
        ]);

        return $image;
    }
}
