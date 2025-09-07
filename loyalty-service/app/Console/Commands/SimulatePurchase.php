<?php

namespace App\Console\Commands;

use App\Events\PurchaseMade;
use App\Models\User;
use App\Queue\FakeQueue;
use Illuminate\Console\Command;

class SimulatePurchase extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'loyalty:purchase {userId} {amount}';

    /**
     * The console command description.
     */
    protected $description = 'Simulate a user purchase and push into fake queue';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = User::findOrFail($this->argument('userId'));
        $amount = (int) $this->argument('amount');

        // push into fake queue
        FakeQueue::push(PurchaseMade::class, [
            'user_id' => $user->id,
            'amount'  => $amount,
        ]);

        $this->info("✅ Purchase queued: User {$user->id}, Amount {$amount}");
    }
}
