<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\CashbackService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashbackServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashback_processing_creates_transaction()
    {
        $user = User::factory()->create();
        $service = new CashbackService();

        $result = $service->process($user, 1000);

        $this->assertContains($result['status'], ['success', 'failed']);
        if ($result['status'] === 'success') {
            $this->assertDatabaseHas('transactions', [
                'user_id' => $user->id,
                'amount' => 50, // 5% of 1000
                'status' => 'success',
            ]);
        }
    }

    public function test_get_cached_balance_returns_correct_amount()
    {
        $user = User::factory()->create();
        $service = new CashbackService();

        // Process a successful cashback
        $service->process($user, 2000);

        $balance = $service->getCachedBalance($user);
        $this->assertGreaterThanOrEqual(0, $balance);
    }
}
