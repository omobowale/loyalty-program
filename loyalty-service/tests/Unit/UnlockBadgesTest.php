<?php

namespace Tests\Unit\Listeners;

use App\Events\BadgeUnlocked;
use App\Events\PurchaseMade;
use App\Listeners\UnlockBadges;
use App\Models\Achievement;
use App\Models\Badge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UnlockBadgesTest extends TestCase
{
    use RefreshDatabase;

    public function test_badge_is_unlocked_when_criteria_met()
    {
        Event::fake();
        Cache::flush();

        $user = User::factory()->create();

        // Achievements for user
        $achievement1 = Achievement::factory()->create(['points_required' => 100]);
        $achievement2 = Achievement::factory()->create(['points_required' => 200]);

        $user->achievements()->attach([
            $achievement1->id => ['unlocked_at' => now()],
            $achievement2->id => ['unlocked_at' => now()],
        ]);

        // Badge that requires 2 achievements
        $badge = Badge::factory()->create(['name' => 'Bronze', 'min_achievements' => 2]);

        // Mock caches
        Cache::shouldReceive('remember')
            ->once()
            ->with('achievements_all', 3600, \Closure::class)
            ->andReturn(collect([$achievement1, $achievement2]));

        Cache::shouldReceive('remember')
            ->once()
            ->with('badges_all', 3600, \Closure::class)
            ->andReturn(collect([$badge]));

        $listener = new UnlockBadges();
        $event = new PurchaseMade($user, 300);
        $listener->handle($event);

        // Assert badge unlocked in DB
        $this->assertDatabaseHas('user_badges', [
            'user_id' => $user->id,
            'badge_id' => $badge->id,
        ]);

        // Assert event dispatched
        Event::assertDispatched(BadgeUnlocked::class, function ($e) use ($user, $badge) {
            return $e->user->id === $user->id && $e->badge->id === $badge->id;
        });
    }

    public function test_no_duplicate_badges_are_unlocked()
    {
        Event::fake();
        Cache::flush();

        $user = User::factory()->create();
        $badge = Badge::factory()->create(['min_achievements' => 1]);

        // Already unlocked
        $user->badges()->attach($badge->id, ['unlocked_at' => now()]);

        Cache::shouldReceive('remember')
            ->once()
            ->with('achievements_all', 3600, \Closure::class)
            ->andReturn(collect());

        Cache::shouldReceive('remember')
            ->once()
            ->with('badges_all', 3600, \Closure::class)
            ->andReturn(collect([$badge]));

        $listener = new UnlockBadges();
        $event = new PurchaseMade($user, 500);
        $listener->handle($event);

        $this->assertCount(1, $user->badges);
        Event::assertNotDispatched(BadgeUnlocked::class);
    }

    public function test_multiple_badges_can_be_unlocked()
    {
        Event::fake();
        Cache::flush();

        $user = User::factory()->create();

        $achievement1 = Achievement::factory()->create(['points_required' => 100]);
        $achievement2 = Achievement::factory()->create(['points_required' => 200]);

        $user->achievements()->attach([
            $achievement1->id => ['unlocked_at' => now()],
            $achievement2->id => ['unlocked_at' => now()],
        ]);

        $badge1 = Badge::factory()->create(['min_achievements' => 1]);
        $badge2 = Badge::factory()->create(['min_achievements' => 2]);

        Cache::shouldReceive('remember')
            ->once()
            ->with('achievements_all', 3600, \Closure::class)
            ->andReturn(collect([$achievement1, $achievement2]));

        Cache::shouldReceive('remember')
            ->once()
            ->with('badges_all', 3600, \Closure::class)
            ->andReturn(collect([$badge1, $badge2]));

        $listener = new UnlockBadges();
        $event = new PurchaseMade($user, 300);
        $listener->handle($event);

        $this->assertDatabaseHas('user_badges', [
            'user_id' => $user->id,
            'badge_id' => $badge1->id,
        ]);

        $this->assertDatabaseHas('user_badges', [
            'user_id' => $user->id,
            'badge_id' => $badge2->id,
        ]);

        Event::assertDispatched(BadgeUnlocked::class, 2);
    }
}
