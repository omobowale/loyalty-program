<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CashbackService
{
    protected int $cacheTtl = 3600; // 1 hour

    public function process(User $user, float $amount): array
    {
        try {
            $cashbackAmount = $amount * 0.05; // 5%

            return DB::transaction(function () use ($user, $cashbackAmount) {

                $status = rand(0, 1) ? 'success' : 'failed';
                $providerResponse = [
                    'mock' => true,
                    'status' => $status,
                    'processed_at' => now(),
                ];

                $transaction = $user->transactions()->create([
                    'amount' => $cashbackAmount,
                    'status' => $status,
                    'provider_response' => $providerResponse,
                ]);

                // Update cached balance if successful
                if ($status === 'success') {
                    $this->updateCache($user);
                }

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

    public function getCachedBalance(User $user): float
    {
        $cacheKey = $this->cacheKey($user);

        $balance = Cache::get($cacheKey);

        if ($balance === null) {
            $balance = $user->transactions()->where('status', 'success')->sum('amount');
            Cache::put($cacheKey, $balance, $this->cacheTtl);
        }

        return (float) $balance;
    }

    protected function updateCache(User $user): void
    {
        $balance = $user->transactions()->where('status', 'success')->sum('amount');
        Cache::put($this->cacheKey($user), $balance, $this->cacheTtl);
    }

    protected function cacheKey(User $user): string
    {
        return "user:{$user->id}:cashback_balance";
    }
}
