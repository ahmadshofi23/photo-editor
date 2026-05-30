<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\EditedImageResource;
use App\Models\Image;
use App\Modules\RemoveBackground\DTOs\RemoveBackgroundDTO;
use App\Modules\RemoveBackground\Services\RemoveBackgroundService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class RemoveBackgroundController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly RemoveBackgroundService $service
    ) {}

    public function process(Request $request)
    {
        // rembg butuh waktu dan RAM lebih untuk proses AI
        ini_set('max_execution_time', '180');
        ini_set('memory_limit', '512M');

        $request->validate([
            'image_id' => 'required|exists:images,id',
        ]);

        $image = Image::findOrFail($request->image_id);

        if ($image->user_id !== $request->user()->id) {
            return $this->errorResponse('Unauthorized.', null, 403);
        }

        try {
            $updated = $this->service->remove($image, new RemoveBackgroundDTO($image->id));
            $updated->latest_action = 'remove_background';
            return $this->successResponse(new EditedImageResource($updated), 'Background removed successfully.');
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        } catch (\Exception $e) {
            return $this->errorResponse('Processing failed.', $e->getMessage(), 500);
        }
    }
}
