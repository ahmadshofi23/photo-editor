<?php

declare(strict_types=1);

namespace App\Modules\BlackWhite\DTOs;

readonly class BlackWhiteDTO
{
    public function __construct(
        public int|string $imageId,
        public int $intensity,
        public int $brightness,
        public int $contrast,
        public bool $sharpen
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            imageId: $data['image_id'],
            intensity: (int) ($data['intensity'] ?? 100),
            brightness: (int) ($data['brightness'] ?? 0),
            contrast: (int) ($data['contrast'] ?? 0),
            sharpen: (bool) ($data['sharpen'] ?? false)
        );
    }
}
