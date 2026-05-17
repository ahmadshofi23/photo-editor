<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompressResultResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'image_id' => $this->id,
            'edited_url' => asset('storage/' . $this->edited_path),
            'stats' => [
                'original_size' => $this->original_size ?? $this->size,
                'new_size' => $this->new_size,
                'reduction_percentage' => round((1 - ($this->new_size / ($this->original_size ?? $this->size))) * 100, 2) . '%',
            ],
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
