<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user and get token for authenticated requests
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_can_get_events_list(): void
    {
        Event::factory(3)->create();

        $response = $this->getJson('/api/events');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'title',
                        'attendee_limit',
                        'reservation_deadline',
                        'price'
                    ]
                ],
                'links',
                'meta'
            ]);
    }

    public function test_can_get_single_event(): void
    {
        $event = Event::factory()->create();

        $response = $this->getJson("/api/events/{$event->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'title',
                    'description',
                    'date_time',
                    'location',
                    'price',
                    'attendee_limit',
                    'reservation_deadline'
                ]
            ]);
    }

    public function test_authenticated_user_can_create_event(): void
    {
        $eventData = [
            'title' => 'Test Event',
            'description' => 'Test Description',
            'date_time' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'location' => 'Test Location',
            'price' => 99.99,
            'attendee_limit' => 100,
            'reservation_deadline' => now()->addDays(3)->format('Y-m-d H:i:s')
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/events', $eventData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Event created successfully',
                'data' => [],
                'status' => 201
            ]);

        $this->assertDatabaseHas('events', [
            'title' => 'Test Event',
            'user_id' => $this->user->id
        ]);
    }

    public function test_user_can_update_own_event(): void
    {
        $event = Event::factory()->create([
            'user_id' => $this->user->id
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated Description'
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->patchJson("/api/events/{$event->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 200,
                'message' => 'Event updated successfully'
            ]);

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'Updated Title'
        ]);
    }

    public function test_user_cannot_update_others_event(): void
    {
        $otherUser = User::factory()->create();
        $event = Event::factory()->create([
            'user_id' => $otherUser->id
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->patchJson("/api/events/{$event->id}", [
                'title' => 'Updated Title'
            ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_event(): void
    {
        $event = Event::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/events/{$event->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => "Event {$event->id} deleted successfully",
                'data' => [],
                'status' => 200
            ]);

        $this->assertDatabaseMissing('events', [
            'id' => $event->id
        ]);
    }

    public function test_events_can_be_filtered(): void
    {
        Event::factory()->create([
            'title' => 'Concert Event',
            'price' => 100,
            'reservation_deadline' => now()->addDays(5),
            'date_time' => now()->addDays(10),
            'attendee_limit' => 100
        ]);

        Event::factory()->create([
            'title' => 'Party Event',
            'price' => 200,
            'reservation_deadline' => now()->addDays(5),
            'date_time' => now()->addDays(10),
            'attendee_limit' => 100
        ]);

        // Test title filter
        $response = $this->getJson('/api/events?filter[title]=Concert');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');

        // Test price filter
        $response = $this->getJson('/api/events?filter[price_min]=150');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
} 