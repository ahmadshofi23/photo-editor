<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Support\Str;

class FilenameHelper
{
    /**
     * Sanitize filename to prevent malicious injections or path traversals.
     *
     * @param string $filename
     * @return string
     */
    public static function sanitize(string $filename): string
    {
        // Remove any path info
        $filename = basename($filename);
        
        // Extract extension
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $name = pathinfo($filename, PATHINFO_FILENAME);
        
        // Slugify the name (alphanumeric, dashes)
        $cleanName = Str::slug($name);
        
        // Limit length
        $cleanName = substr($cleanName, 0, 100);
        
        if (empty($cleanName)) {
            $cleanName = Str::random(10);
        }
        
        return $cleanName . ($extension ? '.' . strtolower($extension) : '');
    }
}
