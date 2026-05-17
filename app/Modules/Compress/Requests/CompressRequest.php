<?php

declare(strict_types=1);

namespace App\Modules\Compress\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image_id' => 'required|exists:images,id',
            'quality' => 'nullable|integer|min:1|max:100',
            'convertWebp' => 'nullable|boolean',
        ];
    }
}
