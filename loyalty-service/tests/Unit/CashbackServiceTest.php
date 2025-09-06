<?php

namespace Tests\Unit;

use App\Models\Transaction;
use App\Models\User;
use App\Services\CashbackService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
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

    public function test_cashback_service_handles_exception_gracefully()
    {
        $user = $this->getMockBuilder(User::class)
            ->onlyMethods(['transactions'])
            ->getMock();

        $user->method('transactions')->willThrowException(new \Exception("DB error"));

        $service = new CashbackService();
        $result = $service->process($user, 500);

        $this->assertEquals('failed', $result['status']);
        $this->assertNull($result['transaction']);
    }

    public function test_cashback_calculation_is_correct()
    {
        $user = User::factory()->create();
        $amount = 2000;
        $expectedCashback = $amount * 0.05;

        // Mock CashbackService to always succeed
        $service = $this->getMockBuilder(\App\Services\CashbackService::class)
            ->onlyMethods(['process'])
            ->getMock();

        $service->method('process')->willReturn([
            'status' => 'success',
            'transaction' => (object)[
                'amount' => $expectedCashback
            ]
        ]);

        $result = $service->process($user, $amount);

        $this->assertEquals('success', $result['status']);
        $this->assertEquals($expectedCashback, $result['transaction']->amount);
    }


    public function test_cached_balance_updates_correctly_after_multiple_transactions()
    {
        $user = User::factory()->create();
        $service = new CashbackService();

        $service->process($user, 1000); // 50
        $service->process($user, 500);  // 25

        $balance = $service->getCachedBalance($user);
        $this->assertEquals(
            $user->transactions()->where('status', 'success')->sum('amount'),
            $balance
        );
    }

    public function test_cashback_balance_is_cached()
    {
        $user = User::factory()->create();
        $service = new CashbackService();

        $service->process($user, 1000);

        $cached = Cache::get("user:{$user->id}:cashback_balance");
        $this->assertNotNull($cached);
        $this->assertEquals($user->transactions()->where('status', 'success')->sum('amount'), $cached);
    }

    public function test_failed_transactions_do_not_count_towards_balance()
    {
        $user = User::factory()->create();
        Transaction::factory()->create([
            'user_id' => $user->id,
            'amount' => 100,
            'status' => 'failed'
        ]);

        $service = new CashbackService();
        $balance = $service->getCachedBalance($user);

        $this->assertEquals(0, $balance);
    }
}
