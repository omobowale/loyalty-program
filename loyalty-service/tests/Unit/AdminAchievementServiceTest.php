<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Achievement;
use App\Models\Badge;
use App\Services\AdminAchievementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAchievementServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_all_user_achievements()
    {
        $user1 = User::factory()->create(['name' => 'Alice']);
        $user2 = User::factory()->create(['name' => 'Bob']);

        $achievement1 = Achievement::factory()->create(['name' => 'First Purchase']);
        $achievement2 = Achievement::factory()->create(['name' => 'Big Spender']);

        $badge1 = Badge::factory()->create(['name' => 'Bronze']);
        $badge2 = Badge::factory()->create(['name' => 'Silver']);

        // Attach achievements
        $user1->achievements()->attach($achievement1->id, ['unlocked_at' => now()]);
        $user2->achievements()->attach($achievement2->id, ['unlocked_at' => now()]);

        // Attach badges
        $user1->badges()->attach($badge1->id);
        $user2->badges()->attach($badge2->id);

        $service = new AdminAchievementService();
        $results = $service->getAllUserAchievements();

        $this->assertCount(2, $results);

        $this->assertEquals('Alice', $results[0]['user']['name']);
        $this->assertEquals($achievement1->name, $results[0]['achievements'][0]['name']);
        $this->assertEquals($badge1->name, $results[0]['current_badge']);

        $this->assertEquals('Bob', $results[1]['user']['name']);
        $this->assertEquals($achievement2->name, $results[1]['achievements'][0]['name']);
        $this->assertEquals($badge2->name, $results[1]['current_badge']);
    }

    public function test_no_users_returns_empty_array()
    {
        $service = new AdminAchievementService();
        $results = $service->getAllUserAchievements();

        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    public function test_user_with_achievements_but_no_badge()
    {
        $user = User::factory()->create(['name' => 'Charlie']);
        $achievement = Achievement::factory()->create(['name' => 'First Login']);

        $user->achievements()->attach($achievement->id, ['unlocked_at' => now()]);

        $service = new AdminAchievementService();
        $results = $service->getAllUserAchievements();

        $this->assertCount(1, $results);
        $this->assertEquals('Charlie', $results[0]['user']['name']);
        $this->assertEquals($achievement->name, $results[0]['achievements'][0]['name']);
        $this->assertNull($results[0]['current_badge']); // No badge
    }


    public function test_user_with_badge_but_no_achievements()
    {
        $user = User::factory()->create(['name' => 'David']);
        $badge = Badge::factory()->create(['name' => 'Gold']);

        $user->badges()->attach($badge->id);

        $service = new AdminAchievementService();
        $results = $service->getAllUserAchievements();

        $this->assertCount(1, $results);
        $this->assertEquals('David', $results[0]['user']['name']);
        $this->assertEmpty($results[0]['achievements']); // No achievements
        $this->assertEquals('Gold', $results[0]['current_badge']);
    }


    public function test_user_with_multiple_achievements_and_badges()
    {
        $user = User::factory()->create(['name' => 'Eve']);
        $ach1 = Achievement::factory()->create(['name' => 'First Login']);
        $ach2 = Achievement::factory()->create(['name' => 'Big Spender']);
        $badge1 = Badge::factory()->create(['name' => 'Bronze']);
        $badge2 = Badge::factory()->create(['name' => 'Silver']);

        $user->achievements()->attach([$ach1->id => ['unlocked_at' => now()], $ach2->id => ['unlocked_at' => now()]]);
        $user->badges()->attach([$badge1->id, $badge2->id]);

        $service = new AdminAchievementService();
        $results = $service->getAllUserAchievements();

        $this->assertCount(1, $results);
        $this->assertCount(2, $results[0]['achievements']);
        $this->assertEquals('Silver', $results[0]['current_badge']); // Last badge
    }


    public function test_multiple_users_with_varied_data()
    {
        $user1 = User::factory()->create(['name' => 'Alice']);
        $user2 = User::factory()->create(['name' => 'Bob']);
        $user3 = User::factory()->create(['name' => 'Charlie']);

        $achievement1 = Achievement::factory()->create(['name' => 'First Purchase']);
        $achievement2 = Achievement::factory()->create(['name' => 'Big Spender']);

        $badge1 = Badge::factory()->create(['name' => 'Bronze']);
        $badge2 = Badge::factory()->create(['name' => 'Silver']);

        // Attach achievements
        $user1->achievements()->attach($achievement1->id, ['unlocked_at' => now()]);
        $user2->achievements()->attach($achievement2->id, ['unlocked_at' => now()]);
        // user3 has no achievements

        // Attach badges
        $user1->badges()->attach($badge1->id);
        $user3->badges()->attach($badge2->id);
        // user2 has no badge

        $service = new AdminAchievementService();
        $results = $service->getAllUserAchievements();

        // Sort results by user id to make assertion predictable
        $results = collect($results)->sortBy('user.id')->values()->toArray();

        $this->assertCount(3, $results);

        // Alice
        $this->assertEquals('Alice', $results[0]['user']['name']);
        $this->assertCount(1, $results[0]['achievements']);
        $this->assertEquals($achievement1->name, $results[0]['achievements'][0]['name']);
        $this->assertEquals($badge1->name, $results[0]['current_badge']);

        // Bob
        $this->assertEquals('Bob', $results[1]['user']['name']);
        $this->assertCount(1, $results[1]['achievements']);
        $this->assertEquals($achievement2->name, $results[1]['achievements'][0]['name']);
        $this->assertNull($results[1]['current_badge']); // no badge

        // Charlie
        $this->assertEquals('Charlie', $results[2]['user']['name']);
        $this->assertEmpty($results[2]['achievements']); // no achievements
        $this->assertEquals($badge2->name, $results[2]['current_badge']); // has badge
    }


    public function test_achievement_unlocked_at_can_be_null()
    {
        $user = User::factory()->create(['name' => 'Frank']);
        $achievement = Achievement::factory()->create(['name' => 'Special Achievement']);

        // Attach achievement without unlocked_at
        $user->achievements()->attach($achievement->id, ['unlocked_at' => null]);

        $service = new AdminAchievementService();
        $results = $service->getAllUserAchievements();

        $this->assertCount(1, $results[0]['achievements']);
        $this->assertNull($results[0]['achievements'][0]['unlocked_at']);
    }
}
