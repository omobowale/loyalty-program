<?php

namespace App\Listeners;

use App\Events\PurchaseMade;
use App\Services\CashbackService;
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
        $service = new CashbackService();
        $service->process($event->user, $event->amount);
    }
}
