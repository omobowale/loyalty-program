<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Achievement;
use App\Models\Badge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AchievementApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create users
        $this->user = User::factory()->create();
        $this->admin = User::factory()->create();
    }

    public function test_user_can_fetch_their_achievements()
    {
        $achievement = Achievement::factory()->create(['name' => 'First Purchase']);
        $badge = Badge::factory()->create(['name' => 'Bronze', 'min_achievements' => 1]);

        // Attach to user
        $this->user->achievements()->attach($achievement->id, ['unlocked_at' => now()]);
        $this->user->badges()->attach($badge->id, ['unlocked_at' => now()]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/users/{$this->user->id}/achievements");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'achievements' => [['name','unlocked','unlocked_at']],
                    'current_badge',
                    'cashback_balance'
                ]
            ])
            ->assertJsonFragment([
                'name' => 'First Purchase',
                'current_badge' => 'Bronze'
            ]);
    }

    public function test_admin_can_fetch_all_user_achievements()
    {
        $achievement = Achievement::factory()->create(['name' => 'First Purchase']);
        $badge = Badge::factory()->create(['name' => 'Bronze', 'min_achievements' => 1]);

        $this->user->achievements()->attach($achievement->id, ['unlocked_at' => now()]);
        $this->user->badges()->attach($badge->id, ['unlocked_at' => now()]);

        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/admin/users/achievements');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'user' => ['id','name'],
                        'achievements' => [['name','unlocked_at']],
                        'current_badge'
                    ]
                ]
            ])
            ->assertJsonFragment([
                'name' => $this->user->name,
                'current_badge' => 'Bronze'
            ]);
    }

    public function test_unauthenticated_user_cannot_access_endpoints()
    {
        $response1 = $this->getJson("/api/users/{$this->user->id}/achievements");
        $response1->assertStatus(401);

        $response2 = $this->getJson('/api/admin/users/achievements');
        $response2->assertStatus(401);
    }

    public function test_user_with_no_achievements_returns_empty_data()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/users/{$this->user->id}/achievements");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'achievements' => [],
                    'current_badge' => null,
                    'cashback_balance' => 0
                ]
            ]);
    }
}
