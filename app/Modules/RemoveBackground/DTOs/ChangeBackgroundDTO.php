<?php

declare(strict_types=1);

namespace App\Modules\RemoveBackground\DTOs;

readonly class ChangeBackgroundDTO
{
    public function __construct(
        public int|string $imageId,
        public string     $bgType,      // 'color' | 'image'
        public ?string    $bgColor      = null, // hex e.g. '#ffffff'
        public ?string    $bgImagePath  = null, // absolute temp path of uploaded bg image
    ) {}
}
