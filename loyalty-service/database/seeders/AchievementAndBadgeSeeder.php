<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Achievement;
use App\Models\Badge;

class AchievementAndBadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Achievements
        $achievements = [
            [
                'name' => 'First Purchase',
                'description' => 'Unlock this after your very first purchase.',
                'points_required' => 100, // purchase amount threshold
            ],
            [
                'name' => 'Big Spender',
                'description' => 'Spend at least 1,000 in a single purchase.',
                'points_required' => 1000,
            ],
            [
                'name' => 'Loyal Customer',
                'description' => 'Spend at least 5,000 overall.',
                'points_required' => 5000,
            ],
        ];

        foreach ($achievements as $data) {
            Achievement::firstOrCreate(
                ['name' => $data['name']],
                $data
            );
        }

        // Badges
        $badges = [
            [
                'name' => 'Bronze Shopper',
                'description' => 'Unlock this after earning 1 achievement.',
                'min_achievements' => 1,
            ],
            [
                'name' => 'Silver Shopper',
                'description' => 'Unlock this after earning 3 achievements.',
                'min_achievements' => 3,
            ],
            [
                'name' => 'Gold Shopper',
                'description' => 'Unlock this after earning 5 achievements.',
                'min_achievements' => 5,
            ],
        ];

        foreach ($badges as $data) {
            Badge::firstOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}
