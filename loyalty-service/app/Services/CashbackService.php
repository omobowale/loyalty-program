<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CashbackService
{
    /**
     * Process cashback for a user purchase
     */
    public function process(User $user, float $amount): array
    {
        try {
            $cashbackAmount = $amount * 0.05; // 5% cashback

            return DB::transaction(function () use ($user, $cashbackAmount) {

                // Simulate provider response (mock or real API later)
                $status = rand(0, 1) ? 'success' : 'failed';
                $providerResponse = [
                    'mock' => true,
                    'status' => $status,
                    'processed_at' => now(),
                ];

                // Save transaction
                $transaction = $user->transactions()->create([
                    'amount' => $cashbackAmount,
                    'status' => $status,
                    'provider_response' => $providerResponse,
                ]);

                // Update cached cashback balance for dashboard (1 hour cache)
                if ($status === 'success') {
                    $cacheKey = "user:{$user->id}:cashback_balance";
                    $currentBalance = Cache::get($cacheKey, 0);
                    Cache::put($cacheKey, $currentBalance + $cashbackAmount, 3600);
                }

                // Log the transaction
                Log::info('Cashback processed', [
                    'user_id' => $user->id,
                    'cashback_amount' => $cashbackAmount,
                    'status' => $status,
                ]);

                return ['status' => $status, 'transaction' => $transaction];
            });
        } catch (\Throwable $e) {
            Log::error("Cashback processing failed: " . $e->getMessage(), [
                'user_id' => $user->id,
                'amount' => $amount,
            ]);

            return ['status' => 'failed', 'transaction' => null];
        }
    }

    /**
     * Retrieve cached cashback balance (fast)
     */
    public function getCachedBalance(User $user): float
    {
        return Cache::get("user:{$user->id}:cashback_balance", function () use ($user) {
            // If not cached, calculate and cache
            $total = $user->transactions()->where('status', 'success')->sum('amount');
            Cache::put("user:{$user->id}:cashback_balance", $total, 3600);
            return $total;
        });
    }
}
