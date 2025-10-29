<?php

namespace Tests\Unit\Policies;

use App\Models\Entity;
use App\Models\User;
use App\Policies\EntityPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EntityPolicyTest extends TestCase
{
    use RefreshDatabase;

    private EntityPolicy $policy;

    private User $user;

    private User $otherUser;

    private Entity $entity;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new EntityPolicy();
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->entity = Entity::factory()->create(['user_id' => $this->user->id]);
    }

    /** @test */
    public function view_allows_owner_to_view_entity()
    {
        $result = $this->policy->view($this->user, $this->entity);

        $this->assertTrue($result);
    }

    /** @test */
    public function view_denies_non_owner_to_view_entity()
    {
        $result = $this->policy->view($this->otherUser, $this->entity);

        $this->assertFalse($result);
    }

    /** @test */
    public function update_allows_owner_to_update_entity()
    {
        $result = $this->policy->update($this->user, $this->entity);

        $this->assertTrue($result);
    }

    /** @test */
    public function update_denies_non_owner_to_update_entity()
    {
        $result = $this->policy->update($this->otherUser, $this->entity);

        $this->assertFalse($result);
    }

    /** @test */
    public function delete_allows_owner_to_delete_entity()
    {
        $result = $this->policy->delete($this->user, $this->entity);

        $this->assertTrue($result);
    }

    /** @test */
    public function delete_denies_non_owner_to_delete_entity()
    {
        $result = $this->policy->delete($this->otherUser, $this->entity);

        $this->assertFalse($result);
    }

    /** @test */
    public function createDay_allows_owner_to_create_day_in_entity()
    {
        $result = $this->policy->createDay($this->user, $this->entity);

        $this->assertTrue($result);
    }

    /** @test */
    public function createDay_denies_non_owner_to_create_day_in_entity()
    {
        $result = $this->policy->createDay($this->otherUser, $this->entity);

        $this->assertFalse($result);
    }

    /** @test */
    public function all_methods_work_with_different_user_ids()
    {
        // 異なるIDのユーザーでテスト
        $user1 = User::factory()->create(['id' => 100]);
        $user2 = User::factory()->create(['id' => 200]);
        $entity = Entity::factory()->create(['user_id' => 100]);

        $this->assertTrue($this->policy->view($user1, $entity));
        $this->assertFalse($this->policy->view($user2, $entity));

        $this->assertTrue($this->policy->update($user1, $entity));
        $this->assertFalse($this->policy->update($user2, $entity));

        $this->assertTrue($this->policy->delete($user1, $entity));
        $this->assertFalse($this->policy->delete($user2, $entity));

        $this->assertTrue($this->policy->createDay($user1, $entity));
        $this->assertFalse($this->policy->createDay($user2, $entity));
    }

    /** @test */
    public function policy_methods_return_boolean_values()
    {
        $viewResult = $this->policy->view($this->user, $this->entity);
        $updateResult = $this->policy->update($this->user, $this->entity);
        $deleteResult = $this->policy->delete($this->user, $this->entity);
        $createDayResult = $this->policy->createDay($this->user, $this->entity);

        $this->assertIsBool($viewResult);
        $this->assertIsBool($updateResult);
        $this->assertIsBool($deleteResult);
        $this->assertIsBool($createDayResult);
    }
}
