<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Achievement;
use App\Models\Badge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AchievementApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->user = User::factory()->create();
    }

    /** Helper: attach achievement & badge to user */
    protected function attachAchievementAndBadge(User $user, Achievement $achievement, Badge $badge)
    {
        $user->achievements()->attach($achievement->id, ['unlocked_at' => now()]);
        $user->badges()->attach($badge->id, ['unlocked_at' => now()]);
    }

    /** Helper: create multiple cashback transactions */
    protected function addTransactions(User $user, array $transactions)
    {
        $user->transactions()->createMany($transactions);
    }

    /** Test: user can fetch their achievements */
    public function test_user_can_fetch_their_achievements()
    {
        $achievement = Achievement::factory()->create(['name' => 'First Purchase']);
        $badge = Badge::factory()->create(['name' => 'Bronze', 'min_achievements' => 1]);
        $this->attachAchievementAndBadge($this->user, $achievement, $badge);

        $response = $this->withHeaders(['X-Mock-User' => $this->user->id])
            ->getJson("/api/users/{$this->user->id}/achievements");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'achievements' => [['name', 'unlocked', 'unlocked_at']],
                    'current_badge',
                    'cashback_balance'
                ]
            ])
            ->assertJsonFragment(['status' => true, 'current_badge' => 'Bronze']);
    }

    /** Test: admin can fetch all user achievements */
    public function test_admin_can_fetch_all_user_achievements()
    {
        $achievement = Achievement::factory()->create(['name' => 'First Purchase']);
        $badge = Badge::factory()->create(['name' => 'Bronze', 'min_achievements' => 1]);
        $this->attachAchievementAndBadge($this->user, $achievement, $badge);

        $response = $this->withHeaders(['X-Mock-User' => $this->admin->id])
            ->getJson('/api/admin/users/achievements');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('user', $data[0]);
        $this->assertArrayHasKey('achievements', $data[0]);
        $this->assertArrayHasKey('current_badge', $data[0]);
    }

    /** Test: unauthenticated users cannot access endpoints */
    public function test_unauthenticated_user_cannot_access_endpoints()
    {
        $this->getJson("/api/users/{$this->user->id}/achievements")->assertStatus(401);
        $this->getJson('/api/admin/users/achievements')->assertStatus(401);
    }

    /** Test: user with no achievements returns empty data */
    public function test_user_with_no_achievements_returns_empty_data()
    {
        $response = $this->withHeaders(['X-Mock-User' => $this->user->id])
            ->getJson("/api/users/{$this->user->id}/achievements");

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'data' => [
                    'achievements' => [],
                    'current_badge' => null,
                    'cashback_balance' => 0
                ]
            ]);
    }

    /** Test: non-admin cannot access admin endpoint */
    public function test_non_admin_cannot_access_admin_endpoint()
    {
        Log::info($this->user->id);
        Log::info($this->user->role);
        $response = $this->withHeaders(['X-Mock-User' => $this->user->id])
            ->getJson('/api/admin/users/achievements');

        $response->assertStatus(403);
    }

    /** Test: invalid user ID returns error */
    public function test_user_endpoint_with_invalid_user_id_returns_error()
    {
        $invalidUserId = 9999;

        $response = $this->withHeaders(['X-Mock-User' => $this->user->id])
            ->getJson("/api/users/{$invalidUserId}/achievements");

        $response->assertStatus(404)
            ->assertJson(['status' => false, 'message' => 'User not found']);
    }

    /** Test: admin fetches multiple users */
    public function test_admin_can_fetch_all_users_achievements_multiple_users()
    {
        $user2 = User::factory()->create();
        $ach1 = Achievement::factory()->create(['name' => 'First Purchase']);
        $ach2 = Achievement::factory()->create(['name' => 'Big Spender']);
        $badge = Badge::factory()->create(['name' => 'Bronze', 'min_achievements' => 1]);

        $this->attachAchievementAndBadge($this->user, $ach1, $badge);
        $this->attachAchievementAndBadge($user2, $ach2, $badge);

        $response = $this->withHeaders(['X-Mock-User' => $this->admin->id])
            ->getJson('/api/admin/users/achievements');

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertCount(3, $data);
        $this->assertEquals($this->user->id, $data[1]['user']['id']);
        $this->assertEquals($user2->id, $data[2]['user']['id']);
    }


    /** Test: user achievements with multiple cashback transactions */
    public function test_user_achievements_with_multiple_cashback_transactions()
    {
        $badge = Badge::factory()->create(['name' => 'Bronze', 'min_achievements' => 1]);
        $ach = Achievement::factory()->create(['name' => 'First Purchase']);
        $this->attachAchievementAndBadge($this->user, $ach, $badge);

        $this->addTransactions($this->user, [
            ['amount' => 100, 'status' => 'success'],
            ['amount' => 200, 'status' => 'success'],
            ['amount' => 50, 'status' => 'failed'], // ignored
        ]);

        $response = $this->withHeaders(['X-Mock-User' => $this->user->id])
            ->getJson("/api/users/{$this->user->id}/achievements");

        $response->assertStatus(200);

        $data = $response->json('data');
        $expectedBalance = 300; // only successful
        $this->assertEquals($expectedBalance, $data['cashback_balance']);
        $this->assertEquals('Bronze', $data['current_badge']);
    }
}
