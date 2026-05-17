<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Image;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int|string $imageId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $image = Image::find($this->imageId);
        if (!$image) {
            return;
        }

        try {
            // Update status to processing
            $image->update(['status' => 'processing']);

            // Simulated processing or actual base processing (like optimization or thumbnail generation)
            // Example logic goes here using Intervention Image.
            // ...

            $image->update(['status' => 'completed']);
            
            Log::info("Image processed successfully: {$this->imageId}");
        } catch (\Exception $e) {
            Log::error("Failed to process image {$this->imageId}: " . $e->getMessage());
            $image->update(['status' => 'failed']);
        }
    }
}
