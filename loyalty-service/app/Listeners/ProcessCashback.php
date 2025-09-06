<?php

namespace App\Listeners;

use App\Events\PurchaseMade;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProcessCashback
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PurchaseMade $event)
    {
        $user = $event->user;
        $amount = $event->amount * 0.05; // 5% cashback

        $status = rand(0, 1) ? 'success' : 'failed';
        $transaction = $user->transactions()->create([
            'amount' => $amount,
            'status' => $status,
            'provider_response' => ['mock' => true, 'status' => $status]
        ]);
    }
}
