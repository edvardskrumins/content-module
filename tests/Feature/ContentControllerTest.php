<?php

namespace Tests\Feature;

use App\Models\Content;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Tests\TestCase;

class ContentControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Expected JSON structure for a single content resource.
     */
    private const CONTENT_RESOURCE_STRUCTURE = [
        'data' => [
            'id',
            'title',
            'description',
            'subtitle',
            'thumb',
            'source',
            'created_at',
            'updated_at',
        ],
    ];


    /**
     * Test index method returns all contents.
     */
    public function testIndexReturnsAllContents(): void
    {
        $contents = Content::factory()->count(3)->create();

        $response = $this->getJson('/api/content-module/contents');
        $response->assertStatus(HttpResponse::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => self::CONTENT_RESOURCE_STRUCTURE['data']
                ],
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Test index method returns empty collection when no contents exist.
     */
    public function testIndexReturnsEmptyCollectionWhenNoContents(): void
    {
        $response = $this->getJson('/api/content-module/contents');
        $response->assertStatus(HttpResponse::HTTP_OK)
            ->assertJson(['data' => []]);
    }

    /**
     * Test store method creates a new content.
     */
    public function testStoreCreatesNewContent(): void
    {
        $data = Content::factory()->make()->toArray();

        $response = $this->postJson('/api/content-module/contents', $data);
        $response->assertStatus(HttpResponse::HTTP_CREATED)
            ->assertJsonStructure(self::CONTENT_RESOURCE_STRUCTURE)
            ->assertJson([
                'data' => [
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'subtitle' => $data['subtitle'],
                    'thumb' => $data['thumb'],
                    'source' => $data['source'],
                ],
            ]);

        $this->assertDatabaseHas('contents', [
            'title' => $data['title'],
            'source' => $data['source'],
        ]);
    }

    /**
     * Test store method validates required fields.
     */
    public function testStoreValidatesRequiredFields(): void
    {
        $response = $this->postJson('/api/content-module/contents', []);
        $response->assertStatus(HttpResponse::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['title', 'source']);
    }

    /**
     * Test store method validates source is a valid URL.
     */
    public function testStoreValidatesSourceIsUrl(): void
    {
        $data = Content::factory()->make()->toArray();
        $data['source'] = 'not-a-valid-url';

        $response = $this->postJson('/api/content-module/contents', $data);
        $response->assertStatus(HttpResponse::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['source']);
    }

    /**
     * Test store method accepts nullable fields.
     */
    public function testStoreAcceptsNullableFields(): void
    {
        $data = Content::factory()->make()->toArray();
        $data = [
            'title' => $data['title'],
            'source' => $data['source'],
        ];

        $response = $this->postJson('/api/content-module/contents', $data);
        $response->assertStatus(HttpResponse::HTTP_CREATED);
        $this->assertDatabaseHas('contents', [
            'title' => $data['title'],
            'description' => null,
            'subtitle' => null,
            'thumb' => null,
        ]);
    }

    /**
     * Test show method returns a specific content.
     */
    public function testShowReturnsSpecificContent(): void
    {
        $content = Content::factory()->create();

        $response = $this->getJson("/api/content-module/contents/{$content->id}");
        $response->assertStatus(HttpResponse::HTTP_OK)
            ->assertJsonStructure(self::CONTENT_RESOURCE_STRUCTURE)
            ->assertJson([
                'data' => [
                    'id' => $content->id,
                    'title' => $content->title,
                    'source' => $content->source,
                ],
            ]);
    }

    /**
     * Test show method returns 404 for non-existent content.
     */
    public function testShowReturns404ForNonExistentContent(): void
    {
        $response = $this->getJson('/api/content-module/contents/999');
        $response->assertStatus(HttpResponse::HTTP_NOT_FOUND);
    }

    /**
     * Test update method updates existing content.
     */
    public function testUpdateUpdatesExistingContent(): void
    {
        $content = Content::factory()->create();
        $updateData = Content::factory()->make()->only(['title', 'description']);

        $response = $this->putJson("/api/content-module/contents/{$content->id}", $updateData);
        $response->assertStatus(HttpResponse::HTTP_OK)
            ->assertJsonStructure(self::CONTENT_RESOURCE_STRUCTURE)
            ->assertJson([
                'data' => [
                    'id' => $content->id,
                    'title' => $updateData['title'],
                    'description' => $updateData['description'],
                    'source' => $content->source,
                ],
            ]);

        $this->assertDatabaseHas('contents', [
            'id' => $content->id,
            'title' => $updateData['title'],
            'description' => $updateData['description'],
        ]);
    }

    /**
     * Test update method validates source URL when provided.
     */
    public function testUpdateValidatesSourceUrlWhenProvided(): void
    {
        $content = Content::factory()->create();
        $updateData['source'] = 'not-a-valid-url';

        $response = $this->putJson("/api/content-module/contents/{$content->id}", $updateData);
        $response->assertStatus(HttpResponse::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['source']);
    }

    /**
     * Test update method returns 404 for non-existent content.
     */
    public function testUpdateReturns404ForNonExistentContent(): void
    {
        $updateData = Content::factory()->make()->only(['title']);

        $response = $this->putJson('/api/content-module/contents/999', $updateData);
        $response->assertStatus(HttpResponse::HTTP_NOT_FOUND);
    }

    /**
     * Test update method can update only specific fields.
     */
    public function testUpdateCanUpdateOnlySpecificFields(): void
    {
        $content = Content::factory()->create();
        $originalDescription = $content->description;
        $originalSubtitle = $content->subtitle;
        
        $updateData = Content::factory()->make()->only(['title']);

        $response = $this->putJson("/api/content-module/contents/{$content->id}", $updateData);
        $response->assertStatus(HttpResponse::HTTP_OK);
        $this->assertDatabaseHas('contents', [
            'id' => $content->id,
            'title' => $updateData['title'],
            'description' => $originalDescription,
            'subtitle' => $originalSubtitle,
        ]);
    }

    /**
     * Test destroy method deletes content.
     */
    public function testDestroyDeletesContent(): void
    {
        $content = Content::factory()->create();

        $response = $this->deleteJson("/api/content-module/contents/{$content->id}");
        $response->assertStatus(HttpResponse::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('contents', [
            'id' => $content->id,
        ]);
    }

    /**
     * Test destroy method returns 404 for non-existent content.
     */
    public function testDestroyReturns404ForNonExistentContent(): void
    {
        $response = $this->deleteJson('/api/content-module/contents/999');
        $response->assertStatus(HttpResponse::HTTP_NOT_FOUND);
    }
}

