<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ValidateImageMime
{
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/webp',
        'image/gif',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            
            if (!$file->isValid()) {
                return response()->json(['message' => 'Uploaded file is not valid'], 422);
            }

            // Using finfo to check magic bytes, not just the extension
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file->getRealPath());
            finfo_close($finfo);

            if (!in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
                Log::warning('Blocked invalid MIME type upload attempt', [
                    'mime' => $mimeType,
                    'original_name' => $file->getClientOriginalName(),
                ]);
                return response()->json(['message' => 'Invalid image MIME type detected via magic bytes.'], 422);
            }
        }

        return $next($request);
    }
}
