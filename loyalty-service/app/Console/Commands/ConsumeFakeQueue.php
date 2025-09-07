<?php

namespace App\Console\Commands;

use App\Events\PurchaseMade;
use App\Models\User;
use App\Queue\FakeQueue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;

class ConsumeFakeQueue extends Command
{
    protected $signature = 'queue:consume-fake';
    protected $description = 'Continuously consume events from the fake DB queue and run listeners immediately';

    public function handle()
    {
        $this->info("🚀 Starting Fake Queue consumer (Ctrl+C to stop)...");

        while (true) {
            $processed = FakeQueue::consume(function ($event, $payload) {

                // Create the event instance
                if ($event === PurchaseMade::class) {
                    $user = User::findOrFail($payload['user_id']);
                    $eventInstance = new $event($user, $payload['amount']);
                } else {
                    $eventInstance = new $event(...array_values($payload));
                }

                // Dispatch normally for any synchronous listeners
                Event::dispatch($eventInstance);

                Log::info("🔥 {$event} DISPATCHED", $payload);

                // Immediately run queued listeners (for testing)
                $listeners = Event::getListeners($event);
                foreach ($listeners as $listener) {
                    $listenerObj = is_string($listener) ? new $listener() : $listener;

                    // Run handle() if it exists
                    if (method_exists($listenerObj, 'handle')) {
                        try {
                            $listenerObj->handle($eventInstance);
                        } catch (\Throwable $e) {
                            Log::error("Listener failed: {$listener}", ['error' => $e->getMessage()]);
                        }
                    }
                }
            });

            if ($processed === 0) {
                usleep(500_000); // 0.5s sleep if nothing new
            }
        }
    }
}
