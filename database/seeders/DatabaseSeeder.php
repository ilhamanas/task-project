<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@taskproject.test'],
            [
                'nama' => 'Administrator',
                'password' => 'password',
                'role' => 'admin',
                'is_active' => true,
            ]
        );
    }
}
