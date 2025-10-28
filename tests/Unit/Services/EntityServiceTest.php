<?php

namespace Tests\Unit\Services;

use App\Models\Day;
use App\Models\Entity;
use App\Models\User;
use App\Services\EntityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class EntityServiceTest extends TestCase
{
    use RefreshDatabase;

    private EntityService $service;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EntityService();
        $this->user = User::factory()->create();
        Auth::login($this->user);
    }

    /** @test */
    public function getEntitiesForPickup_returns_only_entities_with_days()
    {
        // エンティティと記念日を作成
        $entityWithDays = Entity::factory()->create(['user_id' => $this->user->id]);
        Day::factory()->create(['entity_id' => $entityWithDays->id, 'anniv_at' => '2023-12-25']);

        // 記念日のないエンティティ
        Entity::factory()->create(['user_id' => $this->user->id]);

        $result = $this->service->getEntitiesForPickup();

        $this->assertCount(1, $result);
        $this->assertEquals($entityWithDays->id, $result->first()->id);
    }

    /** @test */
    public function getEntitiesForPickup_sorts_days_by_diff_days()
    {
        $entity = Entity::factory()->create(['user_id' => $this->user->id]);

        // 異なる日付の記念日を作成（順序をバラバラに）
        $day1 = Day::factory()->create([
            'entity_id' => $entity->id,
            'anniv_at' => now()->addDays(10)->format('Y-m-d'),
        ]);
        $day2 = Day::factory()->create([
            'entity_id' => $entity->id,
            'anniv_at' => now()->addDays(5)->format('Y-m-d'),
        ]);
        $day3 = Day::factory()->create([
            'entity_id' => $entity->id,
            'anniv_at' => now()->addDays(15)->format('Y-m-d'),
        ]);

        $result = $this->service->getEntitiesForPickup();

        $this->assertCount(1, $result);
        $sortedDays = $result->first()->days;

        // diff_days で昇順にソートされているかチェック
        $this->assertEquals($day2->id, $sortedDays[0]->id); // 5日後
        $this->assertEquals($day1->id, $sortedDays[1]->id); // 10日後
        $this->assertEquals($day3->id, $sortedDays[2]->id); // 15日後
    }

    /** @test */
    public function getEntitiesForPickup_returns_only_current_user_entities()
    {
        $anotherUser = User::factory()->create();

        // 現在のユーザーのエンティティ
        $myEntity = Entity::factory()->create(['user_id' => $this->user->id]);
        Day::factory()->create(['entity_id' => $myEntity->id]);

        // 他のユーザーのエンティティ
        $otherEntity = Entity::factory()->create(['user_id' => $anotherUser->id]);
        Day::factory()->create(['entity_id' => $otherEntity->id]);

        $result = $this->service->getEntitiesForPickup();

        $this->assertCount(1, $result);
        $this->assertEquals($myEntity->id, $result->first()->id);
    }

    /** @test */
    public function getAllForUser_returns_all_user_entities_including_those_without_days()
    {
        // 記念日ありのエンティティ
        $entityWithDays = Entity::factory()->create(['user_id' => $this->user->id]);
        Day::factory()->create(['entity_id' => $entityWithDays->id]);

        // 記念日なしのエンティティ
        $entityWithoutDays = Entity::factory()->create(['user_id' => $this->user->id]);

        $result = $this->service->getAllForUser();

        $this->assertCount(2, $result);
        $this->assertTrue($result->contains('id', $entityWithDays->id));
        $this->assertTrue($result->contains('id', $entityWithoutDays->id));
    }

    /** @test */
    public function create_creates_entity_with_correct_attributes()
    {
        $data = [
            'name' => 'テストエンティティ',
            'desc' => 'テスト説明',
        ];

        $entity = $this->service->create($data);

        $this->assertEquals('テストエンティティ', $entity->name);
        $this->assertEquals('テスト説明', $entity->desc);
        $this->assertEquals($this->user->id, $entity->user_id);
        $this->assertDatabaseHas('entities', [
            'name' => 'テストエンティティ',
            'desc' => 'テスト説明',
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function create_handles_null_description()
    {
        $data = [
            'name' => 'テストエンティティ',
            'desc' => null,
        ];

        $entity = $this->service->create($data);

        $this->assertEquals('テストエンティティ', $entity->name);
        $this->assertNull($entity->desc);
    }

    /** @test */
    public function getWithDays_loads_related_days()
    {
        $entity = Entity::factory()->create(['user_id' => $this->user->id]);
        $day = Day::factory()->create(['entity_id' => $entity->id]);

        $result = $this->service->getWithDays($entity);

        $this->assertTrue($result->relationLoaded('days'));
        $this->assertCount(1, $result->days);
        $this->assertEquals($day->id, $result->days->first()->id);
    }

    /** @test */
    public function update_updates_entity_correctly()
    {
        $entity = Entity::factory()->create([
            'user_id' => $this->user->id,
            'name' => '古い名前',
            'desc' => '古い説明',
            'status' => false,
        ]);

        $updateData = [
            'name' => '新しい名前',
            'desc' => '新しい説明',
            'status' => true,
        ];

        $updatedEntity = $this->service->update($entity, $updateData);

        $this->assertEquals('新しい名前', $updatedEntity->name);
        $this->assertEquals('新しい説明', $updatedEntity->desc);
        $this->assertTrue($updatedEntity->status);
    }

    /** @test */
    public function delete_soft_deletes_entity()
    {
        $entity = Entity::factory()->create(['user_id' => $this->user->id]);

        $this->service->delete($entity);

        $this->assertSoftDeleted('entities', ['id' => $entity->id]);
    }
}
