<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Image;
use App\Models\User;
use App\Modules\Resize\DTOs\ResizeDTO;
use App\Modules\Resize\Services\ResizeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResizeServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Image $image;
    private ResizeService $resizeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Storage::fake('public');

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

        $this->resizeService = app(ResizeService::class);
    }

    public function test_can_resize_image_with_preset(): void
    {
        $dto = new ResizeDTO(
            imageId: $this->image->id,
            width: null,
            height: null,
            mode: 'fit',
            maintainRatio: true,
            preset: 'passport',
            quality: 90
        );

        $editedImage = $this->resizeService->resize($this->image, $dto);

        // Assert DB
        $this->assertNotNull($editedImage->edited_path);
        
        // Assert History
        $this->assertDatabaseHas('image_histories', [
            'image_id' => $this->image->id,
            'action_type' => 'resized',
        ]);

        // Assert new dimensions are updated in DB (passport = 413x531)
        $this->assertEquals(413, $editedImage->width);
        $this->assertEquals(531, $editedImage->height);
    }
}
