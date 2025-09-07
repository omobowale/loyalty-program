<?php

namespace Tests\Feature;

use App\Events\PurchaseMade;
use App\Listeners\ProcessCashback;
use App\Models\User;
use App\Services\CashbackService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
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

        $listener = new ProcessCashback(new CashbackService());

        // Fire PurchaseMade with known amount
        $event = new PurchaseMade($user, 1000);
        $listener->handle($event);

        // Check database
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'status' => 'success',
            'amount' => 1000 * config('loyalty.cashback_rate', 0.05),
        ]);

        // Check cached balance
        $balance = Cache::get("user:{$user->id}:cashback_balance");
        $this->assertEquals(1000 * config('loyalty.cashback_rate', 0.05), $balance);
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
}
