<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\Collection;
use App\Services\GoogleDriveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ApiKeyProtectedApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_routes_require_an_api_key(): void
    {
        $this->getJson('/api/collections')
            ->assertUnauthorized()
            ->assertJson(['message' => 'API key is required.']);
    }

    public function test_collection_can_be_created_with_api_key(): void
    {
        $this->postJson('/api/collections', [
            'description' => 'Family memories',
        ], $this->apiHeaders())
            ->assertCreated()
            ->assertJsonPath('description', 'Family memories');

        $this->assertDatabaseHas('collections', [
            'description' => 'Family memories',
        ]);
    }

    public function test_featured_image_upload_stores_google_drive_url(): void
    {
        $collection = Collection::query()->create([
            'description' => 'Grandparents',
        ]);

        $this->mock(GoogleDriveService::class, function ($mock): void {
            $mock->shouldReceive('upload')
                ->once()
                ->andReturn('https://drive.google.com/uc?id=file123');
        });

        $this->postJson('/api/featured-images', [
            'memory_text' => 'A day we remember',
            'collection_id' => $collection->id,
            'image' => UploadedFile::fake()->image('memory.jpg'),
            'memorial_date' => '2026-04-18',
        ], $this->apiHeaders())
            ->assertCreated()
            ->assertJsonPath('image_url', 'https://drive.google.com/uc?id=file123');

        $this->assertDatabaseHas('featured_images', [
            'memory_text' => 'A day we remember',
            'collection_id' => $collection->id,
            'image_url' => 'https://drive.google.com/uc?id=file123',
            'memorial_date' => '2026-04-18',
        ]);
    }

    private function apiHeaders(): array
    {
        $plainKey = 'test-api-key';

        ApiKey::query()->create([
            'name' => 'test',
            'key_hash' => hash('sha256', $plainKey),
        ]);

        return ['X-API-Key' => $plainKey];
    }
}
