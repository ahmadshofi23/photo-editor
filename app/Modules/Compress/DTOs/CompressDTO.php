<?php

declare(strict_types=1);

namespace App\Modules\Compress\DTOs;

readonly class CompressDTO
{
    public function __construct(
        public int|string $imageId,
        public int $quality,
        public bool $convertWebp
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            imageId: $data['image_id'],
            quality: (int) ($data['quality'] ?? 80),
            convertWebp: (bool) ($data['convertWebp'] ?? false)
        );
    }
}
