<?php

declare(strict_types=1);

namespace App\Models;

class Image extends BaseModel
{
    /**
     * Get the user that owns the image.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the histories for the image.
     */
    public function histories()
    {
        return $this->hasMany(ImageHistory::class);
    }
}
