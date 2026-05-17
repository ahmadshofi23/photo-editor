<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class StorageService extends BaseService
{
    /**
     * Get public URL for a given path.
     *
     * @param string $path
     * @return string
     */
    public function getPublicUrl(string $path): string
    {
        return Storage::disk('public')->url($path);
    }

    /**
     * Move file to processed directory.
     *
     * @param string $currentPath
     * @param string $newFilename
     * @return string
     */
    public function moveToProcessed(string $currentPath, string $newFilename): string
    {
        $newPath = 'processed/' . $newFilename;
        Storage::disk('public')->move($currentPath, $newPath);
        
        return $newPath;
    }

    /**
     * Delete file from storage.
     *
     * @param string $path
     * @return bool
     */
    public function deleteFile(string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }
        
        return false;
    }
}
