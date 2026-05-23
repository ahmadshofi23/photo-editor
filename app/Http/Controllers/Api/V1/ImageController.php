<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ImageHistoryResource;
use App\Http\Resources\Api\V1\ImageResource;
use App\Models\Image;
use App\Modules\Download\Controllers\DownloadController;
use App\Modules\ImageUpload\Services\ImageUploadService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    use ApiResponse;

    protected $uploadService;
    protected $repository;

    public function __construct(ImageUploadService $uploadService, \App\Modules\ImageUpload\Repositories\ImageUploadRepository $repository)
    {
        $this->uploadService = $uploadService;
        $this->repository = $repository;
    }

    public function upload(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'image' => [
                'required',
                'file',
                'mimes:jpeg,jpg,png,webp,gif',
                'max:10240', // 10MB
            ],
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                'Validation failed',
                $validator->errors()->first('image'),
                422
            );
        }

        try {
            $dto = $this->uploadService->upload($request->file('image'));
            $image = $this->repository->save($request->user()->id, $dto->toArray());

            return $this->successResponse(new ImageResource($image), 'Image uploaded successfully.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Upload failed', $e->getMessage(), 500);
        }
    }

    public function batchUpload(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'images'   => ['required', 'array', 'min:1', 'max:20'],
            'images.*' => ['file', 'mimes:jpeg,jpg,png,webp,gif', 'max:10240'],
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors()->first(), 422);
        }

        $results = ['success' => [], 'failed' => []];

        foreach ($request->file('images') as $index => $file) {
            try {
                $dto   = $this->uploadService->upload($file);
                $image = $this->repository->save($request->user()->id, $dto->toArray());
                $results['success'][] = new ImageResource($image);
            } catch (\Exception $e) {
                $results['failed'][] = ['index' => $index, 'error' => $e->getMessage()];
            }
        }

        return $this->successResponse($results, count($results['success']) . ' image(s) uploaded.', 201);
    }

    public function history(Request $request)
    {
        $histories = $request->user()->imageHistories()->with('image')->latest()->paginate(15);
        return $this->successResponse(
            ImageHistoryResource::collection($histories)->response()->getData(true),
            'History retrieved successfully'
        );
    }

    public function download(Request $request, $id)
    {
        $image = Image::findOrFail($id);

        // Manual ownership check (consistent with other controllers)
        if ($image->user_id !== $request->user()->id) {
            return $this->errorResponse('Unauthorized.', null, 403);
        }

        $type = $request->query('type', 'edited');

        // Ensure path exists
        $path = $type === 'edited' ? $image->edited_path : $image->original_path;
        if (!$path) {
            return $this->errorResponse('No edited image found. Please apply changes first.', null, 404);
        }

        $url = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'api.download.secure',
            now()->addMinutes(60),
            ['image' => $image->id, 'type' => $type]
        );

        return $this->successResponse(['download_url' => $url], 'Download link generated.');
    }

    public function destroy(Request $request, $id)
    {
        $image = Image::findOrFail($id);

        if ($image->user_id !== $request->user()->id) {
            return $this->errorResponse('Unauthorized.', null, 403);
        }

        // Delete physical files from storage ONLY if no other records share the same paths
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        
        $sharedOriginal = \App\Models\Image::where('original_path', $image->original_path)->where('id', '!=', $image->id)->exists();
        if (!$sharedOriginal && $image->original_path && $disk->exists($image->original_path)) {
            $disk->delete($image->original_path);
        }

        if ($image->edited_path) {
            $sharedEdited = \App\Models\Image::where('edited_path', $image->edited_path)->where('id', '!=', $image->id)->exists();
            if (!$sharedEdited && $disk->exists($image->edited_path)) {
                $disk->delete($image->edited_path);
            }
        }

        // Delete any orphaned historical generated files
        foreach ($image->histories as $history) {
            $meta = $history->metadata;
            if (isset($meta['generated_path']) && $disk->exists($meta['generated_path'])) {
                // We assume generated paths in history are unique per action
                $disk->delete($meta['generated_path']);
            }
        }

        // Delete DB record (cascades to histories via foreign key)
        $image->delete();

        return $this->successResponse(null, 'Photo deleted successfully.');
    }

    public function reset(Request $request, $id)
    {
        $image = Image::findOrFail($id);

        if ($image->user_id !== $request->user()->id) {
            return $this->errorResponse('Unauthorized.', null, 403);
        }

        // We do NOT delete the original_path. We just clear the edited_path.
        $image->update([
            'edited_path' => null
        ]);

        return $this->successResponse(new ImageResource($image), 'Image reset successfully.');
    }
}
