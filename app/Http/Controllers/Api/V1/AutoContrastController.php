<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\EditedImageResource;
use App\Models\Image;
use App\Modules\AutoContrast\Services\AutoContrastService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AutoContrastController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly AutoContrastService $service) {}

    public function process(Request $request)
    {
        $request->validate([
            'image_id' => 'required|exists:images,id',
        ]);

        $image = Image::findOrFail($request->image_id);

        if ($image->user_id !== $request->user()->id) {
            return $this->errorResponse('Unauthorized.', null, 403);
        }

        try {
            $updatedImage = $this->service->enhance($image);
            $updatedImage->latest_action = 'auto_contrast';
            return $this->successResponse(
                new EditedImageResource($updatedImage),
                'Auto contrast berhasil diterapkan.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Processing failed', $e->getMessage(), 500);
        }
    }
}
