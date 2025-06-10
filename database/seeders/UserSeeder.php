<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Mauro Gigena',
            'email' => 'mauro.test@example.com',
            'password' => bcrypt('password'),
        ]);

        User::factory(20)->create();
    }
}
