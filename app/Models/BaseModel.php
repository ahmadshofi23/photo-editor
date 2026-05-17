<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    /**
     * Disable guarded attributes to allow mass assignment safely.
     * Ensure validation is done at the request level.
     *
     * @var array<int, string>
     */
    protected $guarded = [];
}
