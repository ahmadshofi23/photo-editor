<?php

declare(strict_types=1);

namespace App\Modules\Resize\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResizeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image_id' => 'required|exists:images,id',
            'width' => 'nullable|integer|min:1|max:5000',
            'height' => 'nullable|integer|min:1|max:5000',
            'mode' => 'nullable|string|in:fit,cover,stretch,crop',
            'maintainRatio' => 'nullable|boolean',
            'preset' => 'nullable|string|in:passport,instagram_post,instagram_story,youtube_thumb,facebook_cover',
            'quality' => 'nullable|integer|min:1|max:100',
        ];
    }
}
