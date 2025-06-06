<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $future = fake()->boolean(80); // 80% future events, 20% past events
        $date_time = $future
            ? fake()->dateTimeBetween('now', '2025-12-31')
            : fake()->dateTimeBetween('-1 year', 'now');
        $reservation_deadline = (clone $date_time)->modify('-'.fake()->numberBetween(1, 30).' days');
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'date_time' => $date_time,
            'location' => fake()->city(),
            'price' => fake()->randomFloat(2, 0, 500),
            'attendee_limit' => fake()->numberBetween(20, 50),
            'reservation_deadline' => $reservation_deadline,
            'user_id' => User::factory(),
        ];
    }
}
