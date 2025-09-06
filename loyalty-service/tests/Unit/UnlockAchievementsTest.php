<?php

namespace Tests\Unit\Listeners;

use App\Events\AchievementUnlocked;
use App\Events\PurchaseMade;
use App\Listeners\UnlockAchievements;
use App\Models\Achievement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UnlockAchievementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_achievements_are_unlocked_when_criteria_met()
    {
        Event::fake(); // Prevent actual event handling
        Cache::flush(); // Ensure cache is empty

        // Create user
        $user = User::factory()->create();

        // Create achievements
        $achievement1 = Achievement::factory()->create([
            'name' => 'First Purchase',
            'points_required' => 100,
        ]);
        $achievement2 = Achievement::factory()->create([
            'name' => 'Big Spender',
            'points_required' => 500,
        ]);

        // Mock cache to return achievements
        Cache::shouldReceive('remember')
            ->once()
            ->with('achievements_all', 3600, \Closure::class)
            ->andReturn(collect([$achievement1, $achievement2]));

        $listener = new UnlockAchievements();

        // Fire event with purchase amount that unlocks first achievement only
        $event = new PurchaseMade($user, 150);
        $listener->handle($event);

        // Assertions
        $this->assertDatabaseHas('user_achievements', [
            'user_id' => $user->id,
            'achievement_id' => $achievement1->id,
        ]);

        $this->assertDatabaseMissing('user_achievements', [
            'user_id' => $user->id,
            'achievement_id' => $achievement2->id,
        ]);

        // Ensure event dispatched
        Event::assertDispatched(AchievementUnlocked::class, function ($e) use ($user, $achievement1) {
            return $e->user->id === $user->id && $e->achievement->id === $achievement1->id;
        });
    }

    public function test_no_duplicate_achievements_are_unlocked()
    {
        Event::fake();
        Cache::flush();

        $user = User::factory()->create();

        $achievement = Achievement::factory()->create([
            'name' => 'First Purchase',
            'points_required' => 100,
        ]);

        // User already unlocked achievement
        $user->achievements()->attach($achievement->id, ['unlocked_at' => now()]);

        Cache::shouldReceive('remember')
            ->once()
            ->with('achievements_all', 3600, \Closure::class)
            ->andReturn(collect([$achievement]));

        $listener = new UnlockAchievements();
        $event = new PurchaseMade($user, 500);
        $listener->handle($event);

        // Assert no duplicate
        $this->assertCount(1, $user->achievements);
        Event::assertNotDispatched(AchievementUnlocked::class);
    }

    public function test_multiple_achievements_can_be_unlocked_in_one_purchase()
    {
        Event::fake();
        Cache::flush();

        $user = User::factory()->create();

        $achievement1 = Achievement::factory()->create(['points_required' => 100]);
        $achievement2 = Achievement::factory()->create(['points_required' => 200]);

        Cache::shouldReceive('remember')
            ->once()
            ->with('achievements_all', 3600, \Closure::class)
            ->andReturn(collect([$achievement1, $achievement2]));

        $listener = new UnlockAchievements();
        $event = new PurchaseMade($user, 250); // Unlocks both
        $listener->handle($event);

        $this->assertDatabaseHas('user_achievements', [
            'user_id' => $user->id,
            'achievement_id' => $achievement1->id,
        ]);
        $this->assertDatabaseHas('user_achievements', [
            'user_id' => $user->id,
            'achievement_id' => $achievement2->id,
        ]);

        Event::assertDispatched(AchievementUnlocked::class, 2);
    }
}
