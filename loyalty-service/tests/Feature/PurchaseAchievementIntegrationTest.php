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

class PurchaseAchievementIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_event_unlocks_achievement_and_badge()
    {
        $user = User::factory()->create();

        $achievement = Achievement::factory()->create(['name' => 'First Purchase']);
        $badge = Badge::factory()->create(['name' => 'Bronze', 'min_achievements' => 1]);

        Cache::put('achievements_all', Achievement::all());
        Cache::put('badges_all', Badge::all());

        event(new PurchaseMade($user, 5000));
        $this->artisan('queue:work --once');

        $this->assertDatabaseHas('user_achievements', [
            'user_id' => $user->id,
            'achievement_id' => $achievement->id,
        ]);

        $this->assertDatabaseHas('user_badges', [
            'user_id' => $user->id,
            'badge_id' => $badge->id,
        ]);
    }

    public function test_multiple_purchases_unlock_correct_achievements()
    {
        $user = User::factory()->create();

        $achievement1 = Achievement::factory()->create(['name' => 'First Purchase']);
        $achievement2 = Achievement::factory()->create(['name' => 'Big Spender']);

        Cache::put('achievements_all', Achievement::all());

        event(new PurchaseMade($user, 2000));
        $this->artisan('queue:work --once');

        event(new PurchaseMade($user, 10000));
        $this->artisan('queue:work --once');

        $this->assertDatabaseHas('user_achievements', [
            'user_id' => $user->id,
            'achievement_id' => $achievement1->id,
        ]);

        $this->assertDatabaseHas('user_achievements', [
            'user_id' => $user->id,
            'achievement_id' => $achievement2->id,
        ]);
    }

    public function test_purchase_below_threshold_does_not_unlock_achievements_or_badges()
    {
        $user = User::factory()->create();

        $achievement = Achievement::factory()->create(['name' => 'Big Spender', 'points_required' => 10000]);
        $badge = Badge::factory()->create(['name' => 'Silver', 'min_achievements' => 1]);

        Cache::put('achievements_all', Achievement::all());
        Cache::put('badges_all', Badge::all());

        // Purchase below the threshold
        event(new PurchaseMade($user, 500));
        $this->artisan('queue:work --once');

        $this->assertDatabaseMissing('user_achievements', [
            'user_id' => $user->id,
            'achievement_id' => $achievement->id,
        ]);

        $this->assertDatabaseMissing('user_badges', [
            'user_id' => $user->id,
            'badge_id' => $badge->id,
        ]);
    }

    public function test_multiple_achievements_unlock_single_badge()
    {
        $user = User::factory()->create();

        $achievement1 = Achievement::factory()->create(['name' => 'First Purchase']);
        $achievement2 = Achievement::factory()->create(['name' => 'Big Spender']);
        $badge = Badge::factory()->create(['name' => 'Gold', 'min_achievements' => 2]);

        Cache::put('achievements_all', Achievement::all());
        Cache::put('badges_all', Badge::all());

        event(new PurchaseMade($user, 2000)); // unlock first achievement
        $this->artisan('queue:work --once');

        event(new PurchaseMade($user, 10000)); // unlock second achievement, should trigger badge
        $this->artisan('queue:work --once');

        $this->assertDatabaseHas('user_achievements', [
            'user_id' => $user->id,
            'achievement_id' => $achievement1->id,
        ]);

        $this->assertDatabaseHas('user_achievements', [
            'user_id' => $user->id,
            'achievement_id' => $achievement2->id,
        ]);

        $this->assertDatabaseHas('user_badges', [
            'user_id' => $user->id,
            'badge_id' => $badge->id,
        ]);
    }

    public function test_events_are_dispatched_on_unlock()
    {
        Event::fake([AchievementUnlocked::class, BadgeUnlocked::class]);

        $user = User::factory()->create();

        $achievement = Achievement::factory()->create(['name' => 'First Purchase']);
        $badge = Badge::factory()->create(['name' => 'Bronze', 'min_achievements' => 1]);

        Cache::put('achievements_all', Achievement::all());
        Cache::put('badges_all', Badge::all());

        event(new PurchaseMade($user, 5000));
        $this->artisan('queue:work --once');

        Event::assertDispatched(AchievementUnlocked::class, function ($event) use ($user, $achievement) {
            return $event->user->id === $user->id && $event->achievement->id === $achievement->id;
        });

        Event::assertDispatched(BadgeUnlocked::class, function ($event) use ($user, $badge) {
            return $event->user->id === $user->id && $event->badge->id === $badge->id;
        });
    }
}
