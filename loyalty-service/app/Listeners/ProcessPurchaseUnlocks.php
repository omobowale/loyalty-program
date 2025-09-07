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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class ProcessPurchaseUnlocks
{
    use InteractsWithQueue;

    public $timeout = 120;
    public $tries = 3;

    /**
     * Handle the event.
     */
    public function handle(PurchaseMade $event): void
    {
        $user = $event->user;
        $purchasePoints = $event->amount;

        logger()->info('💰 ProcessPurchaseUnlocks START', [
            'uuid' => $event->uuid ?? null,
            'user_id' => $user->id,
            'amount' => $purchasePoints,
        ]);

        try {
            // Fetch all achievements & badges from cache
            $allAchievements = Cache::remember('achievements_all', 3600, fn() => Achievement::all());
            $allBadges = Cache::remember('badges_all', 3600, fn() => Badge::all());

            // Already unlocked IDs
            $unlockedAchievementIds = $user->achievements()->pluck('achievements.id')->toArray();
            $unlockedBadgeIds = $user->badges()->pluck('badges.id')->toArray();

            $currentAchievementPoints = $user->achievements()->sum('points_required');
            $totalAchievements = $user->achievements()->count();

            // Determine achievements to unlock
            $achievementsToUnlock = $allAchievements->filter(
                fn($achievement) =>
                !in_array($achievement->id, $unlockedAchievementIds) &&
                    (
                        $purchasePoints >= $achievement->points_required ||
                        $currentAchievementPoints + $purchasePoints >= $achievement->points_required
                    )
            );

            // Total achievements **after unlocking new ones**
            $totalAchievementsAfterUnlock = $totalAchievements + $achievementsToUnlock->count();

            // Determine badges to unlock
            $badgesToUnlock = $allBadges->filter(
                fn($badge) =>
                !in_array($badge->id, $unlockedBadgeIds) &&
                    $totalAchievementsAfterUnlock >= $badge->min_achievements
            );

            if ($achievementsToUnlock->isEmpty() && $badgesToUnlock->isEmpty()) {
                Log::info("No new achievements or badges to unlock.", ['user_id' => $user->id]);
                return;
            }

            // Atomic transaction for DB operations
            DB::transaction(function () use ($user, $achievementsToUnlock, $badgesToUnlock) {
                foreach ($achievementsToUnlock as $achievement) {
                    $user->achievements()->attach($achievement->id, ['unlocked_at' => now()]);
                    Event::dispatch(new AchievementUnlocked($user, $achievement));

                    Log::info("🏆 Achievement unlocked", [
                        'user_id' => $user->id,
                        'achievement_id' => $achievement->id,
                        'achievement_name' => $achievement->name,
                    ]);
                }

                foreach ($badgesToUnlock as $badge) {
                    $user->badges()->attach($badge->id, ['unlocked_at' => now()]);
                    Event::dispatch(new BadgeUnlocked($user, $badge));

                    Log::info("🎖️ Badge unlocked", [
                        'user_id' => $user->id,
                        'badge_id' => $badge->id,
                        'badge_name' => $badge->name,
                    ]);
                }
            });

            Log::info('💰 ProcessPurchaseUnlocks END', [
                'user_id' => $user->id,
                'achievements_unlocked' => $achievementsToUnlock->pluck('id')->toArray(),
                'badges_unlocked' => $badgesToUnlock->pluck('id')->toArray(),
            ]);
        } catch (\Throwable $e) {
            Log::error('ProcessPurchaseUnlocks failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
