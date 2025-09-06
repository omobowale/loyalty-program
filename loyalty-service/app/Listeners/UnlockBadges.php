<?php

namespace App\Listeners;

use App\Events\AchievementUnlocked;
use App\Events\BadgeUnlocked;
use App\Events\PurchaseMade;
use App\Models\Achievement;
use App\Models\Badge;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;

class UnlockBadges implements ShouldQueue
{
    use InteractsWithQueue;

    public $timeout = 60; // max execution time
    public $tries = 3;    // retry attempts

    public function handle(PurchaseMade $event)
    {
        $user = $event->user;

        // Cache all achievements for 1 hour
        $allAchievements = Cache::remember('achievements_all', 3600, fn() => Achievement::all());

        // Unlock achievements first
        $achievementsToUnlock = $allAchievements->filter(fn($achievement) =>
            !$user->achievements->contains($achievement->id) &&
            $event->amount >= $achievement->points_required
        );

        foreach ($achievementsToUnlock as $achievement) {
            $user->achievements()->attach($achievement->id, ['unlocked_at' => now()]);
            event(new AchievementUnlocked($user, $achievement));
        }

        // Cache all badges for 1 hour
        $allBadges = Cache::remember('badges_all', 3600, fn() => Badge::all());

        // Unlock badges based on user's achievement count
        $unlockedCount = $user->achievements()->count();

        foreach ($allBadges as $badge) {
            $alreadyUnlocked = $user->badges->contains($badge->id);

            if (!$alreadyUnlocked && $unlockedCount >= $badge->min_achievements) {
                $user->badges()->attach($badge->id, ['unlocked_at' => now()]);
                event(new BadgeUnlocked($user, $badge));
            }
        }
    }
}
