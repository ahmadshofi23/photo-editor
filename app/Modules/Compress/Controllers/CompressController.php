<?php

declare(strict_types=1);

namespace App\Modules\Compress\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Modules\Compress\DTOs\CompressDTO;
use App\Modules\Compress\Requests\CompressRequest;
use App\Modules\Compress\Services\CompressService;
use App\Modules\ImageUpload\Resources\ImageResource;
use Illuminate\Http\JsonResponse;

class CompressController extends Controller
{
    public function __construct(
        private readonly CompressService $compressService
    ) {}

    public function process(CompressRequest $request): JsonResponse
    {
        $dto = CompressDTO::fromArray($request->validated());
        
        $image = Image::findOrFail($dto->imageId);

        // Authorization
        if ($image->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $result = $this->compressService->compress($image, $dto);

            return response()->json([
                'message' => 'Image compressed successfully.',
                'data' => new ImageResource($result['image']),
                'edited_url' => asset('storage/' . $result['image']->edited_path),
                'stats' => [
                    'original_size' => $result['original_size'],
                    'new_size' => $result['new_size'],
                    'reduction_percentage' => $result['reduction_percentage'] . '%'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to compress image.', 'error' => $e->getMessage()], 500);
        }
    }

    public function editView(int|string $imageId)
    {
        $image = Image::findOrFail($imageId);

        if ($image->user_id !== request()->user()->id) {
            abort(403, 'Unauthorized');
        }

        return view('editor.compress', compact('image'));
    }
}
