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

        // Always start editor from the original photo — each Edit session is fresh.
        // The edit history and print library entries are preserved in image_histories.
        if ($image->edited_path) {
            $image->update(['edited_path' => null]);
        }

        return view('editor.resize', compact('image'));
    }
}
