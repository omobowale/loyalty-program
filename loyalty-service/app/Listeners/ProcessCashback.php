<?php

namespace App\Listeners;

use App\Events\PurchaseMade;
use App\Services\CashbackService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

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
    public function handle(PurchaseMade $event)
    {
        $this->cashbackService->process($event->user, $event->amount);
    }
}
