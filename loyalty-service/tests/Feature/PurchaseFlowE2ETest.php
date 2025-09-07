<?php

namespace Tests\Feature;

use App\Events\AchievementUnlocked;
use App\Events\BadgeUnlocked;
use App\Events\PurchaseMade;
use App\Models\User;
use App\Models\Achievement;
use App\Models\Badge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PurchaseFlowE2ETest extends TestCase
{
    use RefreshDatabase;

    public function test_full_purchase_to_achievement_and_badge_flow()
    {
        Event::fake([AchievementUnlocked::class, BadgeUnlocked::class]);

        $user = User::factory()->create();

        // Create achievements and badges
        $achievement1 = Achievement::factory()->create(['points_required' => 1000]);
        $achievement2 = Achievement::factory()->create(['points_required' => 5000]);
        $badge = Badge::factory()->create(['min_achievements' => 2]);

        // Populate cache like your app does
        Cache::put('achievements_all', Achievement::all());
        Cache::put('badges_all', Badge::all());

        // Fire purchases
        event(new PurchaseMade($user, 1500)); // unlocks achievement1
        event(new PurchaseMade($user, 5000)); // unlocks achievement2 and badge

        // --- Assertions ---

        // Database achievements
        $this->assertDatabaseHas('user_achievements', [
            'user_id' => $user->id,
            'achievement_id' => $achievement1->id,
        ]);
        $this->assertDatabaseHas('user_achievements', [
            'user_id' => $user->id,
            'achievement_id' => $achievement2->id,
        ]);

        // Database badge
        $this->assertDatabaseHas('user_badges', [
            'user_id' => $user->id,
            'badge_id' => $badge->id,
        ]);

        // Events dispatched
        Event::assertDispatched(AchievementUnlocked::class, 2);
        Event::assertDispatched(BadgeUnlocked::class, 1);

        // Total achievements and badges
        $this->assertEquals(2, $user->achievements()->count());
        $this->assertEquals(1, $user->badges()->count());
    }
}
