<?php

declare(strict_types=1);

namespace App\Models;

class ImageHistory extends BaseModel
{
    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the image that owns the history.
     */
    public function image()
    {
        return $this->belongsTo(Image::class);
    }
}
