<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class EditedImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'image_id'    => $this->id,
            'edited_url'  => $this->edited_path
                ? asset('storage/' . $this->edited_path)
                : null,
            'original_url' => asset('storage/' . $this->original_path),
            'action'      => $this->latest_action ?? 'edited',
            'updated_at'  => $this->updated_at->toIso8601String(),
        ];
    }
}
