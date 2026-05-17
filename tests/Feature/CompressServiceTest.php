<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Image;
use App\Models\User;
use App\Modules\Compress\DTOs\CompressDTO;
use App\Modules\Compress\Services\CompressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompressServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Image $image;
    private CompressService $compressService;

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

        $this->compressService = app(CompressService::class);
    }

    public function test_can_compress_image(): void
    {
        $dto = new CompressDTO(
            imageId: $this->image->id,
            quality: 50,
            convertWebp: false
        );

        $result = $this->compressService->compress($this->image, $dto);

        // Check if database updated
        $this->assertNotNull($result['image']->edited_path);
        
        // Check if file exists
        Storage::disk('public')->assertExists($result['image']->edited_path);

        // Check history
        $this->assertDatabaseHas('image_histories', [
            'image_id' => $this->image->id,
            'action_type' => 'compressed',
        ]);
        
        // Check reduction is calculated
        $this->assertArrayHasKey('reduction_percentage', $result);
        $this->assertArrayHasKey('new_size', $result);
    }

    public function test_can_compress_and_convert_to_webp(): void
    {
        $dto = new CompressDTO(
            imageId: $this->image->id,
            quality: 50,
            convertWebp: true
        );

        $result = $this->compressService->compress($this->image, $dto);

        // Check if file exists and has webp extension
        Storage::disk('public')->assertExists($result['image']->edited_path);
        $this->assertStringEndsWith('.webp', $result['image']->edited_path);
        $this->assertEquals('image/webp', $result['image']->mime_type);
        $this->assertEquals('webp', $result['image']->extension);

        // Check history
        $this->assertDatabaseHas('image_histories', [
            'image_id' => $this->image->id,
            'action_type' => 'compressed',
        ]);
    }
}
