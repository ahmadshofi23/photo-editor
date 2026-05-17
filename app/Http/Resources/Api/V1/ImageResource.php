<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'original_url' => url('storage/' . $this->original_path),
            'edited_url' => $this->edited_path ? url('storage/' . $this->edited_path) : null,
            'filename' => basename($this->original_path),
            'size_bytes' => $this->size,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
