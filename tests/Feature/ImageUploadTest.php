<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageUploadTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Storage::fake('public');
    }

    public function test_valid_image_upload(): void
    {
        \Illuminate\Support\Facades\Queue::fake();

        $file = UploadedFile::fake()->image('photo.jpg', 600, 600);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/upload', [
                'image' => $file,
            ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'data' => [
                         'id', 'url', 'size', 'width', 'height', 'status'
                     ]
                 ]);

        // Check if file exists in storage
        $uploadedPath = 'uploads/pending/' . $response->json('data.url');
        // Extracting filename from URL to check storage might be tricky, so we check DB
        $this->assertDatabaseHas('images', [
            'user_id' => $this->user->id,
            'mime_type' => 'image/jpeg',
            'status' => 'pending'
        ]);
    }

    public function test_invalid_mime_upload(): void
    {
        // Fake a PDF disguised as JPG
        $file = UploadedFile::fake()->create('document.jpg', 100, 'application/pdf');

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/upload', [
                'image' => $file,
            ]);

        $response->assertStatus(422)
                 ->assertJsonPath('message', 'Invalid image MIME type detected via magic bytes.');
    }

    public function test_oversized_upload(): void
    {
        // 11 MB file
        $file = UploadedFile::fake()->image('large.jpg')->size(11 * 1024);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/upload', [
                'image' => $file,
            ]);

        $response->assertStatus(422);
    }

    public function test_upload_rate_limit(): void
    {
        // Simulate 20 uploads
        for ($i = 0; $i < 20; $i++) {
            \App\Models\Image::create([
                'user_id' => $this->user->id,
                'original_path' => 'dummy/path.jpg',
                'mime_type' => 'image/jpeg',
                'extension' => 'jpg',
                'size' => 1024,
                'width' => 100,
                'height' => 100,
            ]);
        }

        $file = UploadedFile::fake()->image('photo21.jpg');

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/upload', [
                'image' => $file,
            ]);

        $response->assertStatus(429)
                 ->assertJsonPath('message', 'Daily upload limit exceeded.');
    }
}
