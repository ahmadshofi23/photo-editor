<?php

declare(strict_types=1);

namespace App\Modules\ImageUpload\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => Storage::disk('public')->url($this->original_path),
            'size' => $this->size,
            'width' => $this->width,
            'height' => $this->height,
            'status' => $this->status,
        ];
    }
}
