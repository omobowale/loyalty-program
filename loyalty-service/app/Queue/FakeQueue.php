<?php

namespace App\Queue;

use App\Models\FakeQueueMessage;
use Illuminate\Support\Facades\Log;

class FakeQueue
{
    public static function push(string $event, array $payload = []): void
    {
        FakeQueueMessage::create([
            'event'   => $event,
            'payload' => $payload,
        ]);

        Log::info("📥 Event queued (DB)", ['event' => $event, 'payload' => $payload]);
    }

    /**
     * Consume unprocessed messages.
     * Returns number of processed messages.
     */
    public static function consume(callable $handler): int
    {
        $messages = FakeQueueMessage::whereNull('processed_at')->orderBy('id')->get();
        $count = 0;

        foreach ($messages as $message) {
            Log::info("📤 Event consumed (DB)", ['event' => $message->event]);

            $handler($message->event, $message->payload);

            $message->update(['processed_at' => now()]);
            $count++;
        }

        return $count;
    }
}
