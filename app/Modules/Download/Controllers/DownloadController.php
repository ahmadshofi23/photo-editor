<?php

declare(strict_types=1);

namespace App\Modules\Download\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Download;
use App\Modules\Download\Services\DownloadService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadController extends Controller
{
    public function __construct(
        private readonly DownloadService $downloadService
    ) {}

    public function generateLink(Request $request, int|string $id)
    {
        $image = Image::findOrFail($id);

        if ($image->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $type = $request->query('type', 'edited');

        try {
            $url = $this->downloadService->generateSignedUrl($image, $type);
            return response()->json(['url' => $url]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function downloadSecure(Request $request)
    {
        if (!$request->hasValidSignature()) {
            abort(401, 'Invalid or expired download link.');
        }

        $image = Image::findOrFail($request->query('image'));
        $type = $request->query('type', 'edited');
        
        $path = $this->downloadService->getFilePath($image, $type);

        // Record download stat
        Download::create([
            'user_id' => $image->user_id,
            'image_id' => $image->id,
            'download_type' => $type
        ]);

        return response()->download($path);
    }
}
