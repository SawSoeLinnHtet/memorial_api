<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_featured_image_upload_stores_local_storage_url(): void
    {
        Storage::fake('public');

        $collection = Collection::query()->create([
            'description' => 'Grandparents',
        ]);

        $response = $this->postJson('/api/featured-images', [
            'memory_text' => 'A day we remember',
            'collection_id' => $collection->id,
            'image' => UploadedFile::fake()->image('memory.jpg'),
            'memorial_date' => '2026-04-18',
        ], $this->apiHeaders())
            ->assertCreated();

        $imageUrl = $response->json('image_url');

        $this->assertStringStartsWith('/storage/featured-images/', $imageUrl);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $imageUrl));

        $this->assertDatabaseHas('featured_images', [
            'memory_text' => 'A day we remember',
            'collection_id' => $collection->id,
            'image_url' => $imageUrl,
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
