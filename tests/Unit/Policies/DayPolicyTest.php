<?php

namespace Tests\Unit\Policies;

use App\Models\Day;
use App\Models\Entity;
use App\Models\User;
use App\Policies\DayPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DayPolicyTest extends TestCase
{
    use RefreshDatabase;

    private DayPolicy $policy;

    private User $user;

    private User $otherUser;

    private Entity $entity;

    private Day $day;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new DayPolicy();
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->entity = Entity::factory()->create(['user_id' => $this->user->id]);
        $this->day = Day::factory()->create(['entity_id' => $this->entity->id]);
    }

    /** @test */
    public function view_allows_owner_to_view_day()
    {
        $result = $this->policy->view($this->user, $this->day);

        $this->assertTrue($result);
    }

    /** @test */
    public function view_denies_non_owner_to_view_day()
    {
        $result = $this->policy->view($this->otherUser, $this->day);

        $this->assertFalse($result);
    }

    /** @test */
    public function update_allows_owner_to_update_day()
    {
        $result = $this->policy->update($this->user, $this->day);

        $this->assertTrue($result);
    }

    /** @test */
    public function update_denies_non_owner_to_update_day()
    {
        $result = $this->policy->update($this->otherUser, $this->day);

        $this->assertFalse($result);
    }

    /** @test */
    public function delete_allows_owner_to_delete_day()
    {
        $result = $this->policy->delete($this->user, $this->day);

        $this->assertTrue($result);
    }

    /** @test */
    public function delete_denies_non_owner_to_delete_day()
    {
        $result = $this->policy->delete($this->otherUser, $this->day);

        $this->assertFalse($result);
    }

    /** @test */
    public function policy_works_with_nested_entity_relationship()
    {
        // 他のユーザーのエンティティとその記念日
        $otherEntity = Entity::factory()->create(['user_id' => $this->otherUser->id]);
        $otherDay = Day::factory()->create(['entity_id' => $otherEntity->id]);

        // 所有者は自分の記念日にアクセス可能
        $this->assertTrue($this->policy->view($this->user, $this->day));
        $this->assertTrue($this->policy->update($this->user, $this->day));
        $this->assertTrue($this->policy->delete($this->user, $this->day));

        // 非所有者は他人の記念日にアクセス不可
        $this->assertFalse($this->policy->view($this->user, $otherDay));
        $this->assertFalse($this->policy->update($this->user, $otherDay));
        $this->assertFalse($this->policy->delete($this->user, $otherDay));
    }

    /** @test */
    public function policy_methods_return_boolean_values()
    {
        $viewResult = $this->policy->view($this->user, $this->day);
        $updateResult = $this->policy->update($this->user, $this->day);
        $deleteResult = $this->policy->delete($this->user, $this->day);

        $this->assertIsBool($viewResult);
        $this->assertIsBool($updateResult);
        $this->assertIsBool($deleteResult);
    }

    /** @test */
    public function policy_works_with_different_user_ids()
    {
        // 異なるIDのユーザーでテスト
        $user1 = User::factory()->create(['id' => 300]);
        $user2 = User::factory()->create(['id' => 400]);
        $entity = Entity::factory()->create(['user_id' => 300]);
        $day = Day::factory()->create(['entity_id' => $entity->id]);

        $this->assertTrue($this->policy->view($user1, $day));
        $this->assertFalse($this->policy->view($user2, $day));

        $this->assertTrue($this->policy->update($user1, $day));
        $this->assertFalse($this->policy->update($user2, $day));

        $this->assertTrue($this->policy->delete($user1, $day));
        $this->assertFalse($this->policy->delete($user2, $day));
    }

    /** @test */
    public function policy_accesses_entity_relationship_correctly()
    {
        // 記念日が正しくエンティティの所有者を参照できることを確認
        $this->assertEquals($this->user->id, $this->day->entity->user_id);

        // 関係性を通じた認可が正しく動作することを確認
        $result = $this->policy->view($this->user, $this->day);
        $this->assertTrue($result);
    }
}
