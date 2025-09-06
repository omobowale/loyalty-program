<?php

namespace Database\Factories;

use App\Models\Achievement;
use Illuminate\Database\Eloquent\Factories\Factory;

class AchievementFactory extends Factory
{
    protected $model = Achievement::class;

    public function definition()
    {
        return [
            'name' => $this->faker->words(2, true),
            'points_required' => $this->faker->numberBetween(1, 100),
        ];
    }
}
