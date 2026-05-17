<?php

declare(strict_types=1);

namespace App\Modules\ImageUpload\DTOs;

readonly class ImageUploadDTO
{
    public function __construct(
        public string $filename,
        public string $mimeType,
        public string $extension,
        public int $size,
        public ?int $width,
        public ?int $height,
        public string $path
    ) {}

    public function toArray(): array
    {
        return [
            'original_path' => $this->path,
            'mime_type' => $this->mimeType,
            'extension' => $this->extension,
            'size' => $this->size,
            'width' => $this->width,
            'height' => $this->height,
            'status' => 'pending',
        ];
    }
}
