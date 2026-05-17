<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CompressResultResource;
use App\Models\Image;
use App\Modules\Compress\DTOs\CompressDTO;
use App\Modules\Compress\Services\CompressService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;


class CompressController extends Controller
{
    use ApiResponse;

    protected $service;

    public function __construct(CompressService $service)
    {
        $this->service = $service;
    }

    public function process(Request $request)
    {
        $request->validate([
            'image_id' => 'required|exists:images,id',
            'quality' => 'integer|min:1|max:100',
            'convert_webp' => 'boolean',
        ]);

        $image = Image::findOrFail($request->image_id);

        if ($image->user_id !== $request->user()->id) {
            return $this->errorResponse('Unauthorized.', null, 403);
        }

        $dto = new CompressDTO(
            $image->id,
            $request->input('quality', 80),
            (bool) $request->input('convertWebp', $request->input('convert_webp', false))
        );

        try {
            $result = $this->service->compress($image, $dto);
            
            // Set dynamic properties for resource
            $image->original_size = $result['original_size'];
            $image->new_size = $result['new_size'];
            
            return $this->successResponse(new CompressResultResource($image), 'Image compressed successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse('Processing failed', $e->getMessage(), 500);
        }
    }
}
