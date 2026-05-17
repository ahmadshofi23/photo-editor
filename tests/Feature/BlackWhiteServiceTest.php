<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Image;
use App\Models\User;
use App\Modules\BlackWhite\DTOs\BlackWhiteDTO;
use App\Modules\BlackWhite\Services\BlackWhiteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;
use Tests\TestCase;

class BlackWhiteServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Image $image;
    private BlackWhiteService $bwService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Storage::fake('public');

        // Create a fake image file
        $file = UploadedFile::fake()->image('photo.jpg', 600, 600);
        $path = $file->storeAs('uploads/pending', 'photo_dummy.jpg', 'public');

        $this->image = Image::create([
            'user_id' => $this->user->id,
            'original_path' => $path,
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size' => 1024,
            'width' => 600,
            'height' => 600,
            'status' => 'pending'
        ]);

        $this->bwService = app(BlackWhiteService::class);
    }

    public function test_can_convert_image_to_black_and_white(): void
    {
        $dto = new BlackWhiteDTO(
            imageId: $this->image->id,
            intensity: 100,
            brightness: 10,
            contrast: 5,
            sharpen: true
        );

        $editedImage = $this->bwService->convert($this->image, $dto);

        // Check if database updated
        $this->assertNotNull($editedImage->edited_path);
        
        // Check if file exists
        Storage::disk('public')->assertExists($editedImage->edited_path);

        // Check history
        $this->assertDatabaseHas('image_histories', [
            'image_id' => $this->image->id,
            'action_type' => 'black_white_converted',
        ]);
    }
}
