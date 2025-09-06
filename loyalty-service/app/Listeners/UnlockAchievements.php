<?php

namespace App\Listeners;

use App\Events\AchievementUnlocked;
use App\Events\PurchaseMade;
use App\Models\Achievement;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;

class UnlockAchievements implements ShouldQueue
{
    use InteractsWithQueue;

    public $timeout = 60; // max execution time
    public $tries = 3;    // retry attempts

    /**
     * Handle the event.
     */
    public function handle(PurchaseMade $event)
    {
        $user = $event->user;

        // Cache all achievements for 1 hour
        $allAchievements = Cache::remember('achievements_all', 3600, function () {
            return Achievement::all();
        });

        // Filter achievements that user hasn't unlocked AND meets points requirement
        $achievementsToUnlock = $allAchievements->filter(function ($achievement) use ($user, $event) {
            $alreadyUnlocked = $user->achievements->contains($achievement->id);
            $meetsRequirement = $event->amount >= $achievement->points_required;
            return !$alreadyUnlocked && $meetsRequirement;
        });

        foreach ($achievementsToUnlock as $achievement) {
            $user->achievements()->attach($achievement->id, ['unlocked_at' => now()]);
            event(new AchievementUnlocked($user, $achievement));
        }
    }
}
