<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'role' => 'admin',
            'password' => Hash::make('password'), // change in production
            'remember_token' => Str::random(10),
        ]);

        // Customer 1
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'email_verified_at' => now(),
            'role' => 'user',
            'password' => Hash::make('password123'),
            'remember_token' => Str::random(10),
        ]);

        // Customer 2
        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'email_verified_at' => now(),
            'role' => 'user',
            'password' => Hash::make('securepass'),
            'remember_token' => Str::random(10),
        ]);
    }
}
