<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CashbackService
{
    protected float $rate;
    protected int $cacheTtl = 3600; // 1 hour

    public function __construct()
    {
        // Use the configured cashback rate, default to 5%
        $this->rate = config('loyalty.cashback_rate', 0.05);
    }

    /**
     * Process cashback for a user
     *
     * @param User $user
     * @param float $amount
     * @param bool|null $forceSuccess Force success/failure for tests
     * @param bool $forceCache Update cache immediately (useful for tests)
     * @return array ['status' => 'success'|'failed', 'transaction' => Transaction|null]
     */
    public function process(User $user, float $amount, ?bool $forceSuccess = null, bool $forceCache = false): array
    {
        // Reject negative amounts immediately
        if ($amount < 0) {
            Log::warning("Attempted cashback for negative amount", [
                'user_id' => $user->id,
                'amount' => $amount,
            ]);
            return ['status' => 'failed', 'transaction' => null];
        }

        $cashbackAmount = $amount * $this->rate;

        try {
            return DB::transaction(function () use ($user, $cashbackAmount, $forceSuccess, $forceCache) {

                // Determine transaction status
                if ($forceSuccess === true) {
                    $status = 'success';
                } elseif ($forceSuccess === false) {
                    $status = 'failed';
                } else {
                    $status = rand(0, 1) ? 'success' : 'failed';
                }

                $providerResponse = [
                    'mock' => true,
                    'status' => $status,
                    'processed_at' => now(),
                ];

                // Create transaction
                $transaction = $user->transactions()->create([
                    'amount' => $cashbackAmount,
                    'status' => $status,
                    'provider_response' => $providerResponse,
                ]);

                // Update cache only if successful
                if ($status === 'success') {
                    if ($forceCache) {
                        $this->updateCache($user);
                    } else {
                        DB::afterCommit(fn() => $this->updateCache($user));
                    }
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
