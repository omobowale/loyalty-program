<?php

namespace Tests\Feature;

use App\Events\AchievementUnlocked;
use App\Events\BadgeUnlocked;
use App\Events\PurchaseMade;
use App\Models\User;
use App\Models\Achievement;
use App\Models\Badge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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


    public function test_concurrent_purchases_unlock_correct_achievements_and_badges()
    {
        $user = User::factory()->create();
        Achievement::factory()->count(2)->create();
        Badge::factory()->create(['min_achievements' => 2]);

        Cache::put('achievements_all', Achievement::all());
        Cache::put('badges_all', Badge::all());

        // Fire multiple events before queue processing
        event(new PurchaseMade($user, 2000));
        event(new PurchaseMade($user, 5000));

        $this->artisan('queue:work --once');
        $this->artisan('queue:work --once');

        $this->assertEquals(2, $user->achievements()->count());
        $this->assertEquals(1, $user->badges()->count());
    }


    public function test_badge_with_zero_min_achievements_unlocks_immediately()
    {
        $user = User::factory()->create();
        $badge = Badge::factory()->create(['min_achievements' => 0]);

        Cache::put('badges_all', Badge::all());

        event(new PurchaseMade($user, 1000));
        $this->artisan('queue:work --once');

        $this->assertDatabaseHas('user_badges', [
            'user_id' => $user->id,
            'badge_id' => $badge->id,
        ]);
    }

    public function test_zero_and_negative_purchase_amounts_do_not_unlock_achievements_or_badges()
    {
        $user = User::factory()->create();
        Achievement::factory()->create(['points_required' => 1000]);
        Badge::factory()->create(['min_achievements' => 1]);

        Cache::put('achievements_all', Achievement::all());
        Cache::put('badges_all', Badge::all());

        event(new PurchaseMade($user, 0));
        event(new PurchaseMade($user, -500));
        $this->artisan('queue:work --once');

        $this->assertEquals(0, $user->achievements()->count());
        $this->assertEquals(0, $user->badges()->count());
    }


    public function test_same_achievement_not_unlocked_twice()
    {
        $user = User::factory()->create();
        $achievement = Achievement::factory()->create(['points_required' => 1000]);

        Cache::put('achievements_all', Achievement::all());

        event(new PurchaseMade($user, 2000));
        $this->artisan('queue:work --once');

        event(new PurchaseMade($user, 2000));
        $this->artisan('queue:work --once');

        $this->assertEquals(1, $user->achievements()->where('achievement_id', $achievement->id)->count());
    }

    public function test_multiple_concurrent_purchases()
    {
        $user = User::factory()->create();
        Achievement::factory()->count(3)->create();
        Badge::factory()->create(['min_achievements' => 3]);

        Cache::put('achievements_all', Achievement::all());
        Cache::put('badges_all', Badge::all());

        // Simulate multiple purchases happening nearly simultaneously
        $events = [
            new PurchaseMade($user, 2000),
            new PurchaseMade($user, 5000),
            new PurchaseMade($user, 10000),
        ];

        foreach ($events as $event) {
            event($event);
        }

        // Process the queue after firing all events
        $this->artisan('queue:work --once');
        $this->artisan('queue:work --once');
        $this->artisan('queue:work --once');

        $this->assertEquals(3, $user->achievements()->count());
        $this->assertEquals(1, $user->badges()->count());
    }

    public function test_achievement_processing_runs_synchronously()
    {
        $user = User::factory()->create();
        Achievement::factory()->count(2)->create();
        Badge::factory()->create(['min_achievements' => 2]);

        Cache::put('achievements_all', Achievement::all());
        Cache::put('badges_all', Badge::all());

        // Fire multiple purchase events
        event(new PurchaseMade($user, 2000));
        event(new PurchaseMade($user, 5000));

        // No queue processing required since listener is synchronous
        $this->assertEquals(2, $user->achievements()->count());
        $this->assertEquals(1, $user->badges()->count());
    }

    public function test_race_condition_with_simultaneous_purchases()
    {
        $user = User::factory()->create();
        Achievement::factory()->count(2)->create();
        Badge::factory()->create(['min_achievements' => 2]);

        Cache::put('achievements_all', Achievement::all());
        Cache::put('badges_all', Badge::all());

        DB::beginTransaction();
        event(new PurchaseMade($user, 2000));

        DB::beginTransaction();
        event(new PurchaseMade($user, 10000));

        DB::rollBack(); // simulate one transaction finishing later
        DB::commit();   // simulate the other transaction committing

        $this->artisan('queue:work --once');
        $this->artisan('queue:work --once');

        $this->assertEquals(2, $user->achievements()->count());
        $this->assertEquals(1, $user->badges()->count());
    }

    public function test_multiple_users_achievements_independent()
    {
        $users = User::factory()->count(5)->create();
        Achievement::factory()->count(3)->create();
        Badge::factory()->create(['min_achievements' => 3]);

        Cache::put('achievements_all', Achievement::all());
        Cache::put('badges_all', Badge::all());

        foreach ($users as $user) {
            event(new PurchaseMade($user, 2000));
            event(new PurchaseMade($user, 5000));
            event(new PurchaseMade($user, 10000));
        }

        $this->artisan('queue:work --once');
        $this->artisan('queue:work --once');
        $this->artisan('queue:work --once');

        foreach ($users as $user) {
            $this->assertEquals(3, $user->achievements()->count());
            $this->assertEquals(1, $user->badges()->count());
        }
    }
}
