<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\User;

class ReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        
        Event::all()->each(function ($event) use ($users) {
            // Get potential reservants (excluding event creator)
            $potentialReservants = $users->where('id', '!=', $event->user_id);
            
            // Generate a random number of reservations for this event
            $desiredReservations = fake()->numberBetween(10, $event->attendee_limit);
            
            // Get random users for reservations (limited by available users)
            $selectedReservants = $potentialReservants->random(
                min($desiredReservations, $potentialReservants->count())
            );
            
            // Create reservations for selected users
            $selectedReservants->each(function ($user) use ($event) {
                $event->attendees()->attach($user->id, [
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            });
        });
    }
}
