<?php

namespace App\Listeners;

use App\Events\AchievementUnlocked;
use App\Events\PurchaseMade;
use App\Models\Achievement;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UnlockAchievements implements ShouldQueue
{

    public $timeout = 60; // max execution time
    public $tries = 3;    // retry attempts


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
        $achievements = Achievement::all();

        foreach ($achievements as $achievement) {
            if (!$user->achievements->contains($achievement->id)) {
                // Example logic: unlock based on points
                if ($event->amount >= $achievement->points_required) {
                    $user->achievements()->attach($achievement->id, ['unlocked_at' => now()]);
                    event(new AchievementUnlocked($user, $achievement));
                }
            }
        }
    }
}
