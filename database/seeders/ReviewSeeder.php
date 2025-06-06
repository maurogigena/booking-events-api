<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\Review;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates reviews for events that have at least 3 attendees.
     * Between 3-5 random attendees will leave a review for each event.
     */
    public function run(): void
    {
        Event::all()->each(function ($event) {
            // Get all attendees for this event
            $attendees = $event->attendees;
            
            // Skip events with less than 3 attendees
            if ($attendees->count() < 3) {
                return;
            }
            
            // Select random number of reviewers (between 3 and 5, limited by attendee count)
            $numberOfReviewers = fake()->numberBetween(3, min(5, $attendees->count()));
            $selectedReviewers = $attendees->random($numberOfReviewers);
            
            // Create reviews for selected attendees
            $selectedReviewers->each(function ($user) use ($event) {
                Review::create([
                    'user_id' => $user->id,
                    'event_id' => $event->id,
                    'rating' => fake()->numberBetween(1, 5),
                    'comment' => fake()->sentence()
                ]);
            });
        });
    }
}
