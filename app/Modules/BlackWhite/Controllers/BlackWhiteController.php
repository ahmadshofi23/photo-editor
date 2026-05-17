<?php

declare(strict_types=1);

namespace App\Modules\BlackWhite\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Modules\BlackWhite\DTOs\BlackWhiteDTO;
use App\Modules\BlackWhite\Requests\BlackWhiteRequest;
use App\Modules\BlackWhite\Services\BlackWhiteService;
use App\Modules\ImageUpload\Resources\ImageResource;
use Illuminate\Http\JsonResponse;

class BlackWhiteController extends Controller
{
    public function __construct(
        private readonly BlackWhiteService $bwService
    ) {}

    public function process(BlackWhiteRequest $request): JsonResponse
    {
        $dto = BlackWhiteDTO::fromArray($request->validated());
        
        $image = Image::findOrFail($dto->imageId);

        // Authorization
        if ($image->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $editedImage = $this->bwService->convert($image, $dto);

            return response()->json([
                'message' => 'Image converted successfully.',
                'data' => new ImageResource($editedImage),
                'edited_url' => asset('storage/' . $editedImage->edited_path),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to convert image.', 'error' => $e->getMessage()], 500);
        }
    }

    public function editView(int|string $imageId)
    {
        $image = Image::findOrFail($imageId);

        if ($image->user_id !== request()->user()->id) {
            abort(403, 'Unauthorized');
        }

        return view('editor.blackwhite', compact('image'));
    }
}
