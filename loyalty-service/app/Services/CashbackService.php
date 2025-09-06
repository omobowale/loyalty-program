<?php


// app/Services/CashbackService.php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class CashbackService
{
    public function process(User $user, float $amount): array
    {
        try {
            $cashbackAmount = $amount * 0.05; // 5%
            $status = rand(0, 1) ? 'success' : 'failed'; // mock API
            $transaction = $user->transactions()->create([
                'amount' => $cashbackAmount,
                'status' => $status,
                'provider_response' => ['mock' => true, 'status' => $status]
            ]);

            return ['status' => $status, 'transaction' => $transaction];
        } catch (\Throwable $e) {
            Log::error("Cashback failed: " . $e->getMessage());
            return ['status' => 'failed', 'transaction' => null];
        }
    }
}
