<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Event;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReviewTest extends TestCase
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

        // Create an event and make the user an attendee
        $this->event = Event::factory()->create();
        $this->event->attendees()->attach($this->user->id);
    }

    public function test_can_get_event_reviews(): void
    {
        Review::create([
            'user_id' => $this->user->id,
            'event_id' => $this->event->id,
            'rating' => 5,
            'comment' => 'Great event!'
        ]);

        $response = $this->getJson("/api/events/{$this->event->id}/reviews");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'user_name',
                        'rating',
                        'comment',
                        'created_at'
                    ]
                ]
            ]);
    }

    public function test_attendee_can_create_review(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/events/{$this->event->id}/reviews", [
                'rating' => 4,
                'comment' => 'Good event!'
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 201,
                'message' => 'Review created successfully'
            ]);

        $this->assertDatabaseHas('reviews', [
            'user_id' => $this->user->id,
            'event_id' => $this->event->id,
            'rating' => 4,
            'comment' => 'Good event!'
        ]);
    }

    public function test_non_attendee_cannot_create_review(): void
    {
        $nonAttendee = User::factory()->create();
        $nonAttendeeToken = $nonAttendee->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $nonAttendeeToken)
            ->postJson("/api/events/{$this->event->id}/reviews", [
                'rating' => 4,
                'comment' => 'Good event!'
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 403,
                'message' => 'Only users who have reserved can leave a review'
            ]);
    }

    public function test_user_can_update_own_review(): void
    {
        $review = Review::create([
            'user_id' => $this->user->id,
            'event_id' => $this->event->id,
            'rating' => 3,
            'comment' => 'Original comment'
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->patchJson("/api/reviews/{$review->id}", [
                'rating' => 4,
                'comment' => 'Updated comment'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 200,
                'message' => 'Review updated successfully'
            ]);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 4,
            'comment' => 'Updated comment'
        ]);
    }

    public function test_user_cannot_update_others_review(): void
    {
        $otherUser = User::factory()->create();
        $review = Review::create([
            'user_id' => $otherUser->id,
            'event_id' => $this->event->id,
            'rating' => 3,
            'comment' => 'Original comment'
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->patchJson("/api/reviews/{$review->id}", [
                'rating' => 4,
                'comment' => 'Updated comment'
            ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_review(): void
    {
        $review = Review::create([
            'user_id' => $this->user->id,
            'event_id' => $this->event->id,
            'rating' => 3,
            'comment' => 'Test comment'
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 200,
                'message' => 'Review deleted successfully'
            ]);

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_reviews_can_be_filtered_by_rating(): void
    {
        // Create reviews with different ratings
        Review::create([
            'user_id' => $this->user->id,
            'event_id' => $this->event->id,
            'rating' => 5,
            'comment' => 'Excellent!'
        ]);

        Review::create([
            'user_id' => User::factory()->create()->id,
            'event_id' => $this->event->id,
            'rating' => 3,
            'comment' => 'Average'
        ]);

        // Test filter for 5-star reviews
        $response = $this->getJson("/api/events/{$this->event->id}/reviews?filter[stars]=5");
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');

        // Test filter for multiple ratings
        $response = $this->getJson("/api/events/{$this->event->id}/reviews?filter[stars]=3,5");
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }
} 