<?php

declare(strict_types=1);

namespace App\Modules\ImageUpload\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessImageJob;
use App\Modules\ImageUpload\Repositories\ImageUploadRepository;
use App\Modules\ImageUpload\Requests\StoreImageRequest;
use App\Modules\ImageUpload\Resources\ImageResource;
use App\Modules\ImageUpload\Services\ImageUploadService;
use App\Services\StorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ImageUploadController extends Controller
{
    public function __construct(
        private readonly ImageUploadService $uploadService,
        private readonly ImageUploadRepository $repository,
        private readonly StorageService $storageService
    ) {}

    public function store(StoreImageRequest $request): JsonResponse
    {
        $userId = $request->user()->id;

        // Rate limit check: max 20 uploads per day per user (can be config-driven)
        $dailyLimit = config('image.upload_limit_per_day', 20);
        if ($this->repository->countUserUploadsToday($userId) >= $dailyLimit) {
            return response()->json(['message' => 'Daily upload limit exceeded.'], 429);
        }

        try {
            $file = $request->file('image');
            $dto = $this->uploadService->upload($file);
            
            $image = $this->repository->save($userId, $dto->toArray());

            // Dispatch job to process the image asynchronously
            ProcessImageJob::dispatch($image->id);

            return response()->json([
                'message' => 'Image uploaded successfully.',
                'data' => new ImageResource($image),
            ], 201);

        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            Log::error('Upload failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to upload image.'], 500);
        }
    }

    public function destroy(int|string $id): JsonResponse
    {
        $image = $this->repository->find($id);

        if (!$image) {
            return response()->json(['message' => 'Image not found.'], 404);
        }

        // Authorization check
        if ($image->user_id !== request()->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Delete files
        $this->storageService->deleteFile($image->original_path);
        if ($image->edited_path) {
            $this->storageService->deleteFile($image->edited_path);
        }

        $this->repository->delete($id);

        return response()->json(['message' => 'Image deleted successfully.']);
    }
}
