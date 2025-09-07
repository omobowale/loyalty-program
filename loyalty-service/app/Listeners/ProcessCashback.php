<?php

namespace App\Listeners;

use App\Events\PurchaseMade;
use App\Services\CashbackService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class ProcessCashback
{
    protected CashbackService $cashbackService;

    public function __construct(CashbackService $cashbackService)
    {
        $this->cashbackService = $cashbackService;
    }

    /**
     * Handle the event.
     */
    public function handle(PurchaseMade $event): void
    {

        logger()->info('💰 ProcessCashback START', [
            'uuid' => $event->uuid,
            'user_id' => $event->user->id,
            'amount' => $event->amount,
            'trace' => collect(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10))
                ->pluck('function'),
        ]);

        $result = $this->cashbackService->process($event->user, $event->amount);

        Log::info('💰 Cashback event handled', [
            'user_id' => $event->user->id,
            'purchase_amount' => $event->amount,
            'cashback_status' => $result['status'],
        ]);
    }
}
