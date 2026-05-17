<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_upload_image_via_api()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);

        $file = UploadedFile::fake()->image('photo.jpg')->size(500);

        $response = $this->postJson('/api/v1/upload', [
            'image' => $file,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'original_url',
                    'filename',
                ]
            ]);

        $this->assertDatabaseHas('images', [
            'user_id' => $user->id,
            'extension' => 'jpg',
        ]);
    }

    public function test_rejects_malicious_upload()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);

        // Create a fake file with malicious extension
        $file = UploadedFile::fake()->create('script.php', 100, 'text/x-php');

        $response = $this->postJson('/api/v1/upload', [
            'image' => $file,
        ]);

        // Validation might fail earlier but PreventMaliciousUpload might catch it if validation allowed it
        $response->assertStatus(422); // 'image' validation rule will reject non-image first
    }
}
