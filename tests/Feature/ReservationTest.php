<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;
    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user and get token for authenticated requests
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;

        // Create an event for testing reservations
        $this->event = Event::factory()->create([
            'attendee_limit' => 5,
            'reservation_deadline' => now()->addDays(5),
            'date_time' => now()->addDays(10)
        ]);
    }

    public function test_user_can_make_reservation(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/events/{$this->event->id}/reserve");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 200,
                'message' => 'Reservation created successfully'
            ]);

        $this->assertDatabaseHas('event_user', [
            'event_id' => $this->event->id,
            'user_id' => $this->user->id
        ]);
    }

    public function test_user_cannot_reserve_full_event(): void
    {
        // Fill the event to its limit
        $users = User::factory(5)->create();
        foreach ($users as $user) {
            $this->event->attendees()->attach($user->id);
        }

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/events/{$this->event->id}/reserve");

        $response->assertStatus(400)
            ->assertJson([
                'status' => 400,
                'message' => 'No available seats for this event.'
            ]);
    }

    public function test_user_cannot_reserve_after_deadline(): void
    {
        // Update event to have a past deadline
        $this->event->update([
            'reservation_deadline' => now()->subDay()
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/events/{$this->event->id}/reserve");

        $response->assertStatus(400)
            ->assertJson([
                'status' => 400,
                'message' => 'Reservation deadline has passed.'
            ]);
    }

    public function test_user_cannot_reserve_same_event_twice(): void
    {
        // Make first reservation
        $this->event->attendees()->attach($this->user->id);

        // Try to reserve again
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/events/{$this->event->id}/reserve");

        $response->assertStatus(400)
            ->assertJson([
                'status' => 400,
                'message' => 'You already have a reservation for this event.'
            ]);
    }

    public function test_user_can_cancel_reservation(): void
    {
        // Make a reservation first
        $this->event->attendees()->attach($this->user->id);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/events/{$this->event->id}/reserve");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 200,
                'message' => 'Reservation cancelled successfully'
            ]);

        $this->assertDatabaseMissing('event_user', [
            'event_id' => $this->event->id,
            'user_id' => $this->user->id
        ]);
    }

    public function test_user_cannot_cancel_nonexistent_reservation(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/events/{$this->event->id}/reserve");

        $response->assertStatus(404)
            ->assertJson([
                'status' => 404,
                'message' => 'You do not have a reservation for this event.'
            ]);
    }
} 