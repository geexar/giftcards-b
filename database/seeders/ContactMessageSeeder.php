<?php

namespace Database\Seeders;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContactMessageSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        for ($i = 1; $i <= 15; $i++) {
            $hasUser = rand(0, 1) == 1 && $users->count() > 0;

            if ($hasUser) {
                $user = $users->random();
                ContactMessage::create([
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'message' => fake()->paragraph(),
                ]);
            } else {
                ContactMessage::create([
                    'user_id' => null,
                    'name' => fake()->name(),
                    'email' => fake()->unique()->safeEmail(),
                    'message' => fake()->paragraph(),
                ]);
            }
        }
    }
}
