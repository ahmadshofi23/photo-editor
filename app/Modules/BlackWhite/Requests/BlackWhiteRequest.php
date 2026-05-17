<?php

declare(strict_types=1);

namespace App\Modules\BlackWhite\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BlackWhiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image_id' => 'required|exists:images,id',
            'intensity' => 'nullable|integer|min:0|max:100',
            'brightness' => 'nullable|integer|min:-100|max:100',
            'contrast' => 'nullable|integer|min:-100|max:100',
            'sharpen' => 'nullable|boolean',
        ];
    }
}
