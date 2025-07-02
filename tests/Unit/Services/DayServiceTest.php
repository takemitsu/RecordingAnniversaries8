<?php

namespace Tests\Unit\Services;

use App\Models\Day;
use App\Models\Entity;
use App\Models\User;
use App\Services\DayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DayServiceTest extends TestCase
{
    use RefreshDatabase;

    private DayService $service;
    private User $user;
    private Entity $entity;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DayService();
        $this->user = User::factory()->create();
        $this->entity = Entity::factory()->create(['user_id' => $this->user->id]);
    }

    /** @test */
    public function getByEntity_returns_all_days_for_entity()
    {
        $day1 = Day::factory()->create(['entity_id' => $this->entity->id]);
        $day2 = Day::factory()->create(['entity_id' => $this->entity->id]);
        
        // 他のエンティティの記念日
        $otherEntity = Entity::factory()->create(['user_id' => $this->user->id]);
        Day::factory()->create(['entity_id' => $otherEntity->id]);

        $result = $this->service->getByEntity($this->entity);

        $this->assertCount(2, $result);
        $this->assertTrue($result->contains('id', $day1->id));
        $this->assertTrue($result->contains('id', $day2->id));
    }

    /** @test */
    public function create_creates_day_with_correct_attributes()
    {
        $data = [
            'name' => 'テスト記念日',
            'desc' => 'テスト説明',
            'anniv_at' => '2023-12-25'
        ];

        $day = $this->service->create($this->entity, $data);

        $this->assertEquals('テスト記念日', $day->name);
        $this->assertEquals('テスト説明', $day->desc);
        $this->assertEquals('2023-12-25', $day->anniv_at);
        $this->assertEquals($this->entity->id, $day->entity_id);
        
        $this->assertDatabaseHas('days', [
            'name' => 'テスト記念日',
            'desc' => 'テスト説明',
            'anniv_at' => '2023-12-25',
            'entity_id' => $this->entity->id
        ]);
    }

    /** @test */
    public function create_handles_null_description()
    {
        $data = [
            'name' => 'テスト記念日',
            'desc' => null,
            'anniv_at' => '2023-12-25'
        ];

        $day = $this->service->create($this->entity, $data);

        $this->assertEquals('テスト記念日', $day->name);
        $this->assertNull($day->desc);
        $this->assertEquals('2023-12-25', $day->anniv_at);
    }

    /** @test */
    public function get_returns_the_specified_day()
    {
        $day = Day::factory()->create(['entity_id' => $this->entity->id]);

        $result = $this->service->get($this->entity, $day);

        $this->assertEquals($day->id, $result->id);
        $this->assertEquals($day->name, $result->name);
    }

    /** @test */
    public function update_updates_day_correctly()
    {
        $day = Day::factory()->create([
            'entity_id' => $this->entity->id,
            'name' => '古い名前',
            'desc' => '古い説明',
            'anniv_at' => '2023-01-01'
        ]);

        $updateData = [
            'name' => '新しい名前',
            'desc' => '新しい説明',
            'anniv_at' => '2023-12-25'
        ];

        $updatedDay = $this->service->update($this->entity, $day, $updateData);

        $this->assertEquals('新しい名前', $updatedDay->name);
        $this->assertEquals('新しい説明', $updatedDay->desc);
        $this->assertEquals('2023-12-25', $updatedDay->anniv_at);
        
        $this->assertDatabaseHas('days', [
            'id' => $day->id,
            'name' => '新しい名前',
            'desc' => '新しい説明',
            'anniv_at' => '2023-12-25'
        ]);
    }

    /** @test */
    public function update_handles_null_description()
    {
        $day = Day::factory()->create([
            'entity_id' => $this->entity->id,
            'desc' => '古い説明'
        ]);

        $updateData = [
            'name' => 'テスト',
            'desc' => null,
            'anniv_at' => '2023-12-25'
        ];

        $updatedDay = $this->service->update($this->entity, $day, $updateData);

        $this->assertNull($updatedDay->desc);
    }

    /** @test */
    public function delete_soft_deletes_day()
    {
        $day = Day::factory()->create(['entity_id' => $this->entity->id]);

        $this->service->delete($this->entity, $day);

        $this->assertSoftDeleted('days', ['id' => $day->id]);
    }

    /** @test */
    public function create_persists_day_to_database()
    {
        $data = [
            'name' => '誕生日',
            'desc' => '大切な人の誕生日',
            'anniv_at' => '1990-05-15'
        ];

        $day = $this->service->create($this->entity, $data);

        $this->assertNotNull($day->id);
        $this->assertTrue($day->exists);
        
        // データベースから再取得して確認
        $savedDay = Day::find($day->id);
        $this->assertNotNull($savedDay);
        $this->assertEquals('誕生日', $savedDay->name);
        $this->assertEquals('大切な人の誕生日', $savedDay->desc);
        $this->assertEquals('1990-05-15', $savedDay->anniv_at);
    }

    /** @test */
    public function update_persists_changes_to_database()
    {
        $day = Day::factory()->create([
            'entity_id' => $this->entity->id,
            'name' => '結婚記念日',
            'anniv_at' => '2020-06-20'
        ]);

        $updateData = [
            'name' => '結婚記念日（修正）',
            'desc' => '特別な日',
            'anniv_at' => '2020-06-21'
        ];

        $this->service->update($this->entity, $day, $updateData);

        // データベースから再取得して確認
        $updatedDay = Day::find($day->id);
        $this->assertEquals('結婚記念日（修正）', $updatedDay->name);
        $this->assertEquals('特別な日', $updatedDay->desc);
        $this->assertEquals('2020-06-21', $updatedDay->anniv_at);
    }
}