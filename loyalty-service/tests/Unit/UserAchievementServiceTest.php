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
}
