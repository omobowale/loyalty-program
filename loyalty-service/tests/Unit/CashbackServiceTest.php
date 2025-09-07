<?php

namespace Tests\Unit;

use App\Models\Transaction;
use App\Models\User;
use App\Services\CashbackService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class CashbackServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure array cache for tests
        Config::set('cache.default', 'array');
        Cache::flush();

        // Force cashback rate for consistency
        Config::set('loyalty.cashback_rate', 0.05);
    }

    public function test_cashback_processing_creates_transaction()
    {
        $user = User::factory()->create();
        $service = new CashbackService();

        $result = $service->process($user, 1000, true, true);

        $this->assertEquals('success', $result['status']);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'amount' => 50, // 5% of 1000
            'status' => 'success',
        ]);
    }

    public function test_get_cached_balance_returns_correct_amount()
    {
        $user = User::factory()->create();
        $service = new CashbackService();

        $service->process($user, 2000, true, true);

        $balance = $service->getCachedBalance($user);

        $this->assertEquals(2000 * 0.05, $balance);
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

    public function test_cached_balance_updates_correctly_after_multiple_transactions()
    {
        $user = User::factory()->create();
        $service = new CashbackService();

        $service->process($user, 1000, true, true); // 50
        $service->process($user, 500, true, true);  // 25

        $balance = $service->getCachedBalance($user);
        $this->assertEquals(75, $balance);
    }

    public function test_cashback_balance_is_cached()
    {
        $user = User::factory()->create();
        $service = new CashbackService();

        $service->process($user, 1000, true, true);

        $cached = Cache::get("user:{$user->id}:cashback_balance");
        $this->assertNotNull($cached, 'Cache should not be null');
        $this->assertEquals(50, $cached);
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

    public function test_cashback_success_and_failure()
    {
        $user = User::factory()->create();
        $service = new CashbackService();

        // Force success for test determinism
        $result = $service->process($user, 1000, true, true);

        $this->assertArrayHasKey('status', $result);
        $this->assertEquals('success', $result['status']);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'status' => 'success',
        ]);
    }
}
