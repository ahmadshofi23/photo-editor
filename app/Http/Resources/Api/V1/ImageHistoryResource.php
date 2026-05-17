<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImageHistoryResource extends JsonResource
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
            'image_id' => $this->image_id,
            'action_type' => $this->action_type,
            'parameters' => $this->parameters,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
