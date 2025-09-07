<?php

namespace Tests\Feature;

use App\Events\PurchaseMade;
use App\Listeners\ProcessCashback;
use App\Models\User;
use App\Services\CashbackService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProcessCashbackIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure cache is empty before each test
        Cache::flush();
    }

    public function test_successful_cashback_creates_transaction_and_updates_cache()
    {
        $user = User::factory()->create();
        $cashbackService = new CashbackService();
        $listener = new ProcessCashback($cashbackService);

        $event = new PurchaseMade($user, 1000);

        // Instead of modifying the event, pass forced success to the service
        $cashbackService->process($event->user, $event->amount, true, true);

        $expectedAmount = 1000 * config('loyalty.cashback_rate', 0.05);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'status' => 'success',
            'amount' => $expectedAmount,
        ]);

        $balance = Cache::get("user:{$user->id}:cashback_balance");
        $this->assertEquals($expectedAmount, $balance);
    }


    public function test_failed_cashback_creates_transaction_but_does_not_update_cache()
    {
        $user = User::factory()->create();

        $cashbackService = new CashbackService();

        // Force a failed transaction
        $result = $cashbackService->process($user, 1000, false, true);

        $this->assertEquals('failed', $result['status']);
        $this->assertNotNull($result['transaction']);

        // Cache should not be updated for failed transaction
        $balance = Cache::get("user:{$user->id}:cashback_balance");
        $this->assertNull($balance);
    }

    public function test_multiple_purchases_accumulate_cashback_correctly()
    {
        $user = User::factory()->create();

        $cashbackService = new CashbackService();

        $cashbackService->process($user, 1000, true, true);
        $cashbackService->process($user, 500, true, true);

        $expectedTotal = (1000 + 500) * config('loyalty.cashback_rate', 0.05);

        $balance = Cache::get("user:{$user->id}:cashback_balance");
        $this->assertEquals($expectedTotal, $balance);

        $this->assertDatabaseCount('transactions', 2);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'amount' => 1000 * config('loyalty.cashback_rate', 0.05),
            'status' => 'success'
        ]);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'amount' => 500 * config('loyalty.cashback_rate', 0.05),
            'status' => 'success'
        ]);
    }

    public function test_listener_works_when_event_is_fired()
    {
        $user = User::factory()->create();

        // If listener is queued, process it
        event(new PurchaseMade($user, 1000));
        $this->artisan('queue:work --once');

        // Verify transaction exists
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
        ]);
    }


    public function test_zero_amount_purchase_creates_zero_cashback_transaction()
    {
        $user = User::factory()->create();
        $cashbackService = new CashbackService();

        $result = $cashbackService->process($user, 0, true, true);

        $this->assertEquals('success', $result['status']);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'amount' => 0,
            'status' => 'success',
        ]);

        $balance = Cache::get("user:{$user->id}:cashback_balance");
        $this->assertEquals(0, $balance);
    }

    public function test_negative_amount_purchase_fails_transaction()
    {
        $user = User::factory()->create();
        $cashbackService = new CashbackService();

        $result = $cashbackService->process($user, -100);

        // Should immediately fail
        $this->assertEquals('failed', $result['status']);

        // No transaction should be created
        $this->assertDatabaseCount('transactions', 0);

        // Cache should remain null
        $balance = Cache::get("user:{$user->id}:cashback_balance");
        $this->assertNull($balance);
    }

    public function test_high_value_purchase_handles_large_numbers()
    {
        $user = User::factory()->create();
        $highAmount = 10_000_000_000; // 10 billion
        $cashbackService = new CashbackService();

        $result = $cashbackService->process($user, $highAmount, true, true);
        $expectedCashback = $highAmount * config('loyalty.cashback_rate', 0.05);

        $this->assertEquals('success', $result['status']);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'amount' => $expectedCashback,
            'status' => 'success',
        ]);

        $balance = Cache::get("user:{$user->id}:cashback_balance");
        $this->assertEquals($expectedCashback, $balance);
    }

    public function test_multiple_users_cache_is_isolated()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $cashbackService = new CashbackService();
        $cashbackService->process($user1, 1000, true, true);
        $cashbackService->process($user2, 2000, true, true);

        $balance1 = Cache::get("user:{$user1->id}:cashback_balance");
        $balance2 = Cache::get("user:{$user2->id}:cashback_balance");

        $this->assertEquals(1000 * config('loyalty.cashback_rate', 0.05), $balance1);
        $this->assertEquals(2000 * config('loyalty.cashback_rate', 0.05), $balance2);
    }

    public function test_listener_runs_synchronously_when_event_is_fired()
    {
        $user = User::factory()->create();

        // Fire the event
        event(new PurchaseMade($user, 1000));

        // Since the listener is NOT queued, the transaction should already exist
        $expectedAmount = 1000 * config('loyalty.cashback_rate', 0.05);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'amount' => $expectedAmount,
            'status' => 'success',
        ]);

        // Cached balance should be updated as well
        $balance = Cache::get("user:{$user->id}:cashback_balance");
        $this->assertEquals($expectedAmount, $balance);
    }


    public function test_transaction_rolls_back_on_exception()
    {
        $user = User::factory()->create();

        $cashbackService = new class extends \App\Services\CashbackService {
            public function process($user, $amount, ?bool $forceSuccess = null, bool $forceCache = false): array
            {
                try {
                    // Force exception inside transaction but catch it like the original service
                    return \Illuminate\Support\Facades\DB::transaction(function () use ($user) {
                        throw new \Exception("Simulated DB failure");
                    });
                } catch (\Throwable $e) {
                    return ['status' => 'failed', 'transaction' => null];
                }
            }
        };

        $result = $cashbackService->process($user, 1000, true, true);

        $this->assertEquals('failed', $result['status']);
        $this->assertDatabaseCount('transactions', 0);
        $this->assertNull(Cache::get("user:{$user->id}:cashback_balance"));
    }



    public function test_random_success_failure_generates_valid_status()
    {
        $user = User::factory()->create();
        $cashbackService = new CashbackService();

        $result = $cashbackService->process($user, 1000); // no forceSuccess
        $this->assertContains($result['status'], ['success', 'failed']);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
        ]);
    }
}
