<?php

declare(strict_types=1);

namespace App\Modules\History\Services;

use App\Models\ImageHistory;

class HistoryService
{
    /**
     * Log an action in the history.
     */
    public function log(int|string $imageId, string $actionType, array $metadata = []): void
    {
        ImageHistory::create([
            'image_id' => $imageId,
            'action_type' => $actionType,
            'metadata' => $metadata,
        ]);
    }
}
