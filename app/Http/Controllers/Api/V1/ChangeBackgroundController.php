<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\EditedImageResource;
use App\Models\Image;
use App\Modules\RemoveBackground\DTOs\ChangeBackgroundDTO;
use App\Modules\RemoveBackground\Services\ChangeBackgroundService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ChangeBackgroundController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ChangeBackgroundService $service
    ) {}

    public function process(Request $request)
    {
        $request->validate([
            'image_id' => 'required|exists:images,id',
            'bg_type'  => 'required|in:color,image',
            'bg_color' => 'required_if:bg_type,color|nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'bg_image' => 'required_if:bg_type,image|nullable|file|mimes:jpeg,jpg,png,webp|max:10240',
        ]);

        $image = Image::findOrFail($request->image_id);

        if ($image->user_id !== $request->user()->id) {
            return $this->errorResponse('Unauthorized.', null, 403);
        }

        $dto = new ChangeBackgroundDTO(
            imageId:    $image->id,
            bgType:     $request->bg_type,
            bgColor:    $request->bg_color,
            bgImagePath: $request->hasFile('bg_image')
                ? $request->file('bg_image')->getRealPath()
                : null,
        );

        try {
            $updated = $this->service->change($image, $dto);
            $updated->latest_action = 'change_background';
            return $this->successResponse(new EditedImageResource($updated), 'Background changed successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse('Processing failed.', $e->getMessage(), 500);
        }
    }
}
