<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(), // create a user if not provided
            'amount' => $this->faker->randomFloat(2, 1, 1000),
            'status' => $this->faker->randomElement(['success', 'failed']),
            'provider_response' => ['mock' => true, 'status' => 'success'],
        ];
    }
}
