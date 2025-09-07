<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;


class PurchaseMade implements ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $amount;
    public $uuid;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, float $amount)
    {
        $this->user = $user;
        $this->amount = $amount;
        $this->uuid = (string) Str::uuid(); // unique event ID

        // 👇 Add this log
        logger()->info('🔥 PurchaseMade DISPATCHED', [
            'uuid'   => $this->uuid,
            'user_id' => $user->id,
            'amount' => $amount,
            'trace'  => collect(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10))
                ->pluck('function'),
        ]);
    }
}
