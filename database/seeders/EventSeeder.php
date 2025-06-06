<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\User;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        Event::factory(100)->make()->each(function ($event) use ($users) {
            $event->user_id = $users->random()->id;
            $event->save();
        });
    }
}
