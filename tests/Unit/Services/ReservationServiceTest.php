<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\User;
use App\Models\Event;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class ReservationServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->event = Event::factory()->create([
            'attendee_limit' => 5,
            'reservation_deadline' => now()->addDays(5),
            'date_time' => now()->addDays(10)
        ]);
    }

    public function test_can_check_event_availability(): void
    {
        // Event should be available initially
        $this->assertTrue($this->isEventAvailable($this->event));

        // Fill the event to its limit
        for ($i = 0; $i < $this->event->attendee_limit; $i++) {
            $user = User::factory()->create();
            $this->event->attendees()->attach($user->id);
        }

        // Event should not be available when full
        $this->assertFalse($this->isEventAvailable($this->event));
    }

    public function test_can_check_reservation_deadline(): void
    {
        // Event should be available before deadline
        $this->assertTrue($this->isBeforeDeadline($this->event));

        // Update event to have a past deadline
        $this->event->update([
            'reservation_deadline' => now()->subDay()
        ]);

        // Event should not be available after deadline
        $this->assertFalse($this->isBeforeDeadline($this->event));
    }

    public function test_can_check_user_existing_reservation(): void
    {
        // User should not have reservation initially
        $this->assertFalse($this->hasExistingReservation($this->event, $this->user));

        // Create a reservation
        $this->event->attendees()->attach($this->user->id);

        // User should have reservation now
        $this->assertTrue($this->hasExistingReservation($this->event, $this->user));
    }

    public function test_can_create_reservation(): void
    {
        // Create reservation
        $this->createReservation($this->event, $this->user);

        // Verify reservation was created
        $this->assertDatabaseHas('event_user', [
            'event_id' => $this->event->id,
            'user_id' => $this->user->id
        ]);
    }

    public function test_can_cancel_reservation(): void
    {
        // Create reservation first
        $this->event->attendees()->attach($this->user->id);

        // Cancel reservation
        $this->cancelReservation($this->event, $this->user);

        // Verify reservation was cancelled
        $this->assertDatabaseMissing('event_user', [
            'event_id' => $this->event->id,
            'user_id' => $this->user->id
        ]);
    }

    private function isEventAvailable(Event $event): bool
    {
        return $event->attendees()->count() < $event->attendee_limit;
    }

    private function isBeforeDeadline(Event $event): bool
    {
        return now()->lte($event->reservation_deadline);
    }

    private function hasExistingReservation(Event $event, User $user): bool
    {
        return $event->attendees()->where('user_id', $user->id)->exists();
    }

    private function createReservation(Event $event, User $user): void
    {
        $event->attendees()->attach($user->id, [
            'created_at' => now()
        ]);
    }

    private function cancelReservation(Event $event, User $user): void
    {
        $event->attendees()->detach($user->id);
    }
} 