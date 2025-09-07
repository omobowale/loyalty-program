<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Achievement;
use App\Models\Badge;
use App\Models\Transaction;
use App\Services\UserAchievementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAchievementServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_achievements_badge_and_cashback()
    {
        $user = User::factory()->create();

        $achievement1 = Achievement::factory()->create(['name' => 'First Purchase']);
        $achievement2 = Achievement::factory()->create(['name' => 'Big Spender']);

        $badge = Badge::factory()->create(['name' => 'Gold']);

        // Attach achievements
        $user->achievements()->attach($achievement1->id, ['unlocked_at' => now()]);
        $user->achievements()->attach($achievement2->id, ['unlocked_at' => now()]);

        // Attach badge
        $user->badges()->attach($badge->id);

        // Add successful transactions
        Transaction::factory()->create(['user_id' => $user->id, 'amount' => 100, 'status' => 'success']);
        Transaction::factory()->create(['user_id' => $user->id, 'amount' => 50, 'status' => 'success']);
        Transaction::factory()->create(['user_id' => $user->id, 'amount' => 20, 'status' => 'failed']); // should not count

        $service = new UserAchievementService();
        $result = $service->getUserAchievements($user);

        $this->assertCount(2, $result['achievements']);
        $this->assertEquals($achievement1->name, $result['achievements'][0]['name']);
        $this->assertTrue($result['achievements'][0]['unlocked']);
        $this->assertEquals($achievement2->name, $result['achievements'][1]['name']);
        $this->assertTrue($result['achievements'][1]['unlocked']);

        $this->assertEquals($badge->name, $result['current_badge']);
        $this->assertEquals(150, $result['cashback_balance']); // only successful transactions
    }

    public function test_user_with_no_achievements()
    {
        $user = User::factory()->create();

        $service = new UserAchievementService();
        $result = $service->getUserAchievements($user);

        $this->assertEmpty($result['achievements']);
        $this->assertNull($result['current_badge']);
        $this->assertEquals(0, $result['cashback_balance']);
    }

    public function test_user_with_no_transactions()
    {
        $user = User::factory()->create();

        $achievement = Achievement::factory()->create(['name' => 'First Purchase']);
        $badge = Badge::factory()->create(['name' => 'Bronze']);

        $user->achievements()->attach($achievement->id, ['unlocked_at' => now()]);
        $user->badges()->attach($badge->id);

        $service = new UserAchievementService();
        $result = $service->getUserAchievements($user);

        $this->assertCount(1, $result['achievements']);
        $this->assertEquals($achievement->name, $result['achievements'][0]['name']);
        $this->assertEquals($badge->name, $result['current_badge']);
        $this->assertEquals(0, $result['cashback_balance']);
    }

    public function test_user_with_multiple_badges_only_latest_counts()
    {
        $user = User::factory()->create();

        $badge1 = Badge::factory()->create(['name' => 'Bronze']);
        $badge2 = Badge::factory()->create(['name' => 'Silver']);

        $user->badges()->attach($badge1->id);
        $user->badges()->attach($badge2->id);

        $service = new UserAchievementService();
        $result = $service->getUserAchievements($user);

        $this->assertEquals($badge2->name, $result['current_badge']);
    }
}
