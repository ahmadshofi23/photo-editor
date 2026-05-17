<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\EditedImageResource;
use App\Models\Image;
use App\Modules\Resize\DTOs\ResizeDTO;
use App\Modules\Resize\Services\ResizeService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;


class ResizeController extends Controller
{
    use ApiResponse;

    protected $service;

    public function __construct(ResizeService $service)
    {
        $this->service = $service;
    }

    public function process(Request $request)
    {
        $request->validate([
            'image_id' => 'required|exists:images,id',
            'width' => 'required|integer|min:10|max:5000',
            'height' => 'required|integer|min:10|max:5000',
            'mode' => 'string|in:fit,cover,stretch,crop',
            'maintain_ratio' => 'boolean',
            'preset' => 'nullable|string',
            'quality' => 'integer|min:1|max:100',
            'crop_x' => 'nullable|integer|min:0',
            'crop_y' => 'nullable|integer|min:0',
            'crop_width' => 'nullable|integer|min:1',
            'crop_height' => 'nullable|integer|min:1',
        ]);

        $image = Image::findOrFail($request->image_id);

        if ($image->user_id !== $request->user()->id) {
            return $this->errorResponse('Unauthorized.', null, 403);
        }

        $dto = new ResizeDTO(
            $image->id,
            $request->width,
            $request->height,
            $request->input('mode', 'fit'),
            $request->input('maintainRatio', $request->input('maintain_ratio', true)),
            $request->preset,
            $request->input('quality', 90),
            $request->input('crop_x'),
            $request->input('crop_y'),
            $request->input('crop_width'),
            $request->input('crop_height')
        );

        try {
            $updatedImage = $this->service->resize($image, $dto);
            $updatedImage->latest_action = 'resize';
            return $this->successResponse(new EditedImageResource($updatedImage), 'Image resized successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse('Processing failed', $e->getMessage(), 500);
        }
    }
}
