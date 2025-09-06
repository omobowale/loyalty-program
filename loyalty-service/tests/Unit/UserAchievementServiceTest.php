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

    public function test_user_achievements_service_returns_correct_data()
    {
        // Create user
        $user = User::factory()->create();

        // Create achievements and attach to user
        $achievement1 = Achievement::factory()->create(['name' => 'First Purchase']);
        $achievement2 = Achievement::factory()->create(['name' => 'Big Spender']);

        $user->achievements()->attach($achievement1->id, ['unlocked_at' => now()]);
        $user->achievements()->attach($achievement2->id, ['unlocked_at' => now()]);

        // Create badges and attach to user
        $badge = Badge::factory()->create(['name' => 'Bronze', 'min_achievements' => 2]);
        $user->badges()->attach($badge->id, ['unlocked_at' => now()]);

        // Create transactions for cashback
        Transaction::factory()->create([
            'user_id' => $user->id,
            'amount' => 100,
            'status' => 'success',
        ]);
        Transaction::factory()->create([
            'user_id' => $user->id,
            'amount' => 50,
            'status' => 'failed',
        ]);

        // Run service
        $service = new UserAchievementService();
        $result = $service->getUserAchievements($user);

        // Assertions
        $this->assertCount(2, $result['achievements']);
        $this->assertEquals('Bronze', $result['current_badge']);
        $this->assertEquals(100, $result['cashback_balance']);

        // Check unlocked_at exists
        foreach ($result['achievements'] as $a) {
            $this->assertArrayHasKey('unlocked_at', $a);
            $this->assertTrue($a['unlocked']);
        }
    }

    public function test_user_with_no_achievements_returns_empty_array()
    {
        $user = User::factory()->create();
        $service = new UserAchievementService();

        $result = $service->getUserAchievements($user);
        $this->assertEmpty($result['achievements']);
        $this->assertNull($result['current_badge']);
        $this->assertEquals(0, $result['cashback_balance']);
    }

    public function test_user_with_multiple_badges_returns_latest_badge()
    {
        $user = User::factory()->create();

        $badge1 = Badge::factory()->create(['name' => 'Bronze']);
        $badge2 = Badge::factory()->create(['name' => 'Silver']);
        $user->badges()->attach($badge1->id, ['unlocked_at' => now()->subDay()]);
        $user->badges()->attach($badge2->id, ['unlocked_at' => now()]);

        $service = new UserAchievementService();
        $result = $service->getUserAchievements($user);

        $this->assertEquals('Silver', $result['current_badge']);
    }

    public function test_achievements_without_unlocked_at_still_returns_unlocked_flag()
    {
        $user = User::factory()->create();
        $achievement = Achievement::factory()->create(['name' => 'Test Achievement']);
        $user->achievements()->attach($achievement->id, ['unlocked_at' => null]);

        $service = new UserAchievementService();
        $result = $service->getUserAchievements($user);

        $this->assertCount(1, $result['achievements']);
        $this->assertArrayHasKey('unlocked', $result['achievements'][0]);
        $this->assertTrue($result['achievements'][0]['unlocked']); // your service currently returns true always
    }

    public function test_cashback_balance_sums_only_successful_transactions()
    {
        $user = User::factory()->create();
        Transaction::factory()->create(['user_id' => $user->id, 'amount' => 100, 'status' => 'success']);
        Transaction::factory()->create(['user_id' => $user->id, 'amount' => 50, 'status' => 'failed']);

        $service = new UserAchievementService();
        $result = $service->getUserAchievements($user);

        $this->assertEquals(100, $result['cashback_balance']);
    }

    public function test_large_number_of_achievements()
    {
        $user = User::factory()->create();
        $achievements = Achievement::factory()->count(50)->create();

        foreach ($achievements as $a) {
            $user->achievements()->attach($a->id, ['unlocked_at' => now()]);
        }

        $service = new UserAchievementService();
        $result = $service->getUserAchievements($user);

        $this->assertCount(50, $result['achievements']);
    }
}
