<?php

declare(strict_types=1);

namespace App\Modules\ImageUpload\Services;

use App\Helpers\FilenameHelper;
use App\Modules\ImageUpload\DTOs\ImageUploadDTO;
use App\Services\BaseService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ImageUploadService extends BaseService
{
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/webp',
        'image/gif',
    ];

    /**
     * Handle the file upload process.
     *
     * @param UploadedFile $file
     * @return ImageUploadDTO
     * @throws InvalidArgumentException
     */
    public function upload(UploadedFile $file): ImageUploadDTO
    {
        $this->validate($file);

        $secureFilename = $this->generateSecureFilename($file);
        
        // Store in local storage disk 'public'
        $path = $file->storeAs('uploads/pending', $secureFilename, 'public');
        
        if (!$path) {
            throw new \RuntimeException('Failed to store uploaded file.');
        }

        // Gather image dimensions if possible
        $width = null;
        $height = null;
        $imageSize = @getimagesize($file->getRealPath());
        if ($imageSize !== false) {
            $width = $imageSize[0];
            $height = $imageSize[1];
        }

        return new ImageUploadDTO(
            filename: $secureFilename,
            mimeType: $file->getMimeType(),
            extension: $file->getClientOriginalExtension() ?: $file->guessExtension() ?: '',
            size: $file->getSize(),
            width: $width,
            height: $height,
            path: $path
        );
    }

    /**
     * Validate the file (e.g., proper upload, basic integrity).
     *
     * @param UploadedFile $file
     * @return void
     * @throws InvalidArgumentException
     */
    public function validate(UploadedFile $file): void
    {
        if (!$file->isValid()) {
            throw new InvalidArgumentException('Uploaded file is not valid.');
        }

        $mime = $file->getMimeType();
        if (!in_array($mime, self::ALLOWED_MIME_TYPES, true)) {
            throw new InvalidArgumentException("MIME type {$mime} is not allowed.");
        }
    }

    /**
     * Generate a secure, unique filename.
     *
     * @param UploadedFile $file
     * @return string
     */
    public function generateSecureFilename(UploadedFile $file): string
    {
        $originalName = $file->getClientOriginalName();
        $sanitized = FilenameHelper::sanitize($originalName);
        
        $nameWithoutExtension = pathinfo($sanitized, PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension() ?: $file->guessExtension();
        
        return sprintf('%s_%s.%s', $nameWithoutExtension, Str::random(10), strtolower($extension));
    }
}
