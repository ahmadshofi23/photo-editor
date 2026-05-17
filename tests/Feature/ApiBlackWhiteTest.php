<?php

namespace Tests\Feature;

use App\Models\Image;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiBlackWhiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_process_black_white_via_api()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);

        $file = UploadedFile::fake()->image('photo.jpg')->size(500);
        
        // Let's create an image directly in DB for testing the endpoint
        $imagePath = $file->store('images', 'public');
        
        $image = Image::create([
            'user_id' => $user->id,
            'original_path' => $imagePath,
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size' => 500,
            'status' => 'uploaded'
        ]);

        $response = $this->postJson('/api/v1/blackwhite', [
            'image_id' => $image->id,
            'intensity' => 50,
            'brightness' => 10,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'image_id',
                    'edited_url',
                    'action',
                ]
            ]);

        $this->assertDatabaseHas('image_histories', [
            'image_id' => $image->id,
            'action_type' => 'black_white_converted',
        ]);
    }

    public function test_cannot_process_others_image()
    {
        Storage::fake('public');

        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        
        Sanctum::actingAs($otherUser, ['*']);

        $image = Image::create([
            'user_id' => $owner->id,
            'original_path' => 'dummy.jpg',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size' => 500,
            'status' => 'uploaded'
        ]);

        $response = $this->postJson('/api/v1/blackwhite', [
            'image_id' => $image->id,
            'intensity' => 100,
        ]);

        $response->assertStatus(403);
    }
}
