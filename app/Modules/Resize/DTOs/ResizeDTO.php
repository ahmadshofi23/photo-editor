<?php

declare(strict_types=1);

namespace App\Modules\Resize\DTOs;

readonly class ResizeDTO
{
    public function __construct(
        public int|string $imageId,
        public ?int $width,
        public ?int $height,
        public string $mode, // fit, cover, stretch, crop
        public bool $maintainRatio,
        public ?string $preset,
        public int $quality,
        public ?int $cropX = null,
        public ?int $cropY = null,
        public ?int $cropWidth = null,
        public ?int $cropHeight = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            imageId: $data['image_id'],
            width: isset($data['width']) ? (int) $data['width'] : null,
            height: isset($data['height']) ? (int) $data['height'] : null,
            mode: $data['mode'] ?? 'fit',
            maintainRatio: (bool) ($data['maintainRatio'] ?? true),
            preset: $data['preset'] ?? null,
            quality: (int) ($data['quality'] ?? 90),
            cropX: isset($data['crop_x']) ? (int) $data['crop_x'] : null,
            cropY: isset($data['crop_y']) ? (int) $data['crop_y'] : null,
            cropWidth: isset($data['crop_width']) ? (int) $data['crop_width'] : null,
            cropHeight: isset($data['crop_height']) ? (int) $data['crop_height'] : null
        );
    }
}
