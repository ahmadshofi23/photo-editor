<?php

declare(strict_types=1);

namespace App\Modules\Resize\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Modules\ImageUpload\Resources\ImageResource;
use App\Modules\Resize\DTOs\ResizeDTO;
use App\Modules\Resize\Requests\ResizeRequest;
use App\Modules\Resize\Services\ResizeService;
use Illuminate\Http\JsonResponse;

class ResizeController extends Controller
{
    public function __construct(
        private readonly ResizeService $resizeService
    ) {}

    public function process(ResizeRequest $request): JsonResponse
    {
        $dto = ResizeDTO::fromArray($request->validated());
        
        $image = Image::findOrFail($dto->imageId);

        // Authorization
        if ($image->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $editedImage = $this->resizeService->resize($image, $dto);

            return response()->json([
                'message' => 'Image resized successfully.',
                'data' => new ImageResource($editedImage),
                'edited_url' => asset('storage/' . $editedImage->edited_path),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to resize image.', 'error' => $e->getMessage()], 500);
        }
    }

    public function editView(int|string $imageId)
    {
        $image = Image::findOrFail($imageId);

        if ($image->user_id !== request()->user()->id) {
            abort(403, 'Unauthorized');
        }

        // When a user opens the editor, they are starting a new edit session on the original photo.
        // Wipe any previous edited_path so it doesn't conflict or load an already-cropped image.
        if ($image->edited_path) {
            // We do NOT delete the physical file because we want it to be available in the Print Queue history!
            $image->update(['edited_path' => null]);
        }

        return view('editor.resize', compact('image'));
    }
}
