<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Image;
use App\Services\StorageService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CleanupImagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'image:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup processed images older than 30 days';

    /**
     * Execute the console command.
     */
    public function handle(StorageService $storageService): void
    {
        $this->info('Starting image cleanup...');

        $threshold = Carbon::now()->subDays(30);

        $images = Image::where('created_at', '<', $threshold)->get();

        $count = 0;
        foreach ($images as $image) {
            $storageService->deleteFile($image->original_path);
            if ($image->edited_path) {
                $storageService->deleteFile($image->edited_path);
            }
            $image->delete();
            $count++;
        }

        $this->info("Cleanup completed. Deleted {$count} old images.");
    }
}
