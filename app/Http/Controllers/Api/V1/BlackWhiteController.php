<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\EditedImageResource;
use App\Models\Image;
use App\Modules\BlackWhite\DTOs\BlackWhiteDTO;
use App\Modules\BlackWhite\Services\BlackWhiteService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;


class BlackWhiteController extends Controller
{
    use ApiResponse;

    protected $service;

    public function __construct(BlackWhiteService $service)
    {
        $this->service = $service;
    }

    public function process(Request $request)
    {
        $request->validate([
            'image_id' => 'required|exists:images,id',
            'intensity' => 'integer|min:0|max:100',
            'brightness' => 'integer|min:-100|max:100',
            'contrast' => 'integer|min:-100|max:100',
            'sharpen' => 'boolean',
        ]);

        $image = Image::findOrFail($request->image_id);

        if ($image->user_id !== $request->user()->id) {
            return $this->errorResponse('Unauthorized.', null, 403);
        }

        $dto = new BlackWhiteDTO(
            $image->id,
            $request->input('intensity', 100),
            $request->input('brightness', 0),
            $request->input('contrast', 0),
            $request->input('sharpen', false)
        );

        try {
            $updatedImage = $this->service->convert($image, $dto);
            $updatedImage->latest_action = 'black_white';
            return $this->successResponse(new EditedImageResource($updatedImage), 'Black & white conversion successful.');
        } catch (\Exception $e) {
            return $this->errorResponse('Processing failed', $e->getMessage(), 500);
        }
    }
}
