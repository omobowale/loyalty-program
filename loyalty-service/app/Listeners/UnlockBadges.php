<?php

namespace App\Listeners;

use App\Events\BadgeUnlocked;
use App\Events\PurchaseMade;
use App\Models\Badge;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UnlockBadges
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
    public function handle(PurchaseMade $event): void
    {
        $user = $event->user;
        $badges = Badge::all();

        foreach ($badges as $badge) {
            $unlockedCount = $user->achievements()->count();
            if ($unlockedCount >= $badge->min_achievements && !$user->badges->contains($badge->id)) {
                $user->badges()->attach($badge->id, ['unlocked_at' => now()]);
                event(new BadgeUnlocked($user, $badge));
            }
        }
    }
}
