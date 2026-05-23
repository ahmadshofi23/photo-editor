<?php

declare(strict_types=1);

namespace App\Modules\RemoveBackground\DTOs;

readonly class RemoveBackgroundDTO
{
    public function __construct(
        public int|string $imageId,
    ) {}
}
