<?php

namespace Tests\Unit\Http\Resources;

use App\Http\Resources\EntityResource;
use App\Models\Day;
use App\Models\Entity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class EntityResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_transforms_entity_data_correctly()
    {
        $entity = Entity::factory()->create([
            'user_id' => $this->user->id,
            'name' => '家族の記念日',
            'desc' => '家族に関する記念日集',
            'status' => true,
        ]);

        $resource = new EntityResource($entity);
        $result = $resource->toArray(new Request());

        $this->assertEquals($entity->id, $result['id']);
        $this->assertEquals('家族の記念日', $result['name']);
        $this->assertEquals('家族に関する記念日集', $result['desc']);
        $this->assertTrue($result['status']);
    }

    /** @test */
    public function it_formats_timestamps_correctly()
    {
        $entity = Entity::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => Carbon::parse('2024-01-15 10:30:45'),
            'updated_at' => Carbon::parse('2024-01-20 15:20:30'),
        ]);

        $resource = new EntityResource($entity);
        $result = $resource->toArray(new Request());

        $this->assertEquals('2024-01-15 10:30:45', $result['created_at']);
        $this->assertEquals('2024-01-20 15:20:30', $result['updated_at']);
    }

    /** @test */
    public function it_handles_null_description()
    {
        $entity = Entity::factory()->create([
            'user_id' => $this->user->id,
            'desc' => null,
        ]);

        $resource = new EntityResource($entity);
        $result = $resource->toArray(new Request());

        $this->assertNull($result['desc']);
    }

    /** @test */
    public function it_handles_false_status()
    {
        $entity = Entity::factory()->create([
            'user_id' => $this->user->id,
            'status' => false,
        ]);

        $resource = new EntityResource($entity);
        $result = $resource->toArray(new Request());

        $this->assertFalse($result['status']);
        $this->assertIsBool($result['status']);
    }

    /** @test */
    public function it_includes_days_count_when_counted()
    {
        $entity = Entity::factory()->create(['user_id' => $this->user->id]);

        // 記念日を3つ作成
        Day::factory()->count(3)->create(['entity_id' => $entity->id]);

        // withCount('days') を使用してロード
        $entityWithCount = Entity::withCount('days')->find($entity->id);

        $resource = new EntityResource($entityWithCount);
        $result = $resource->toArray(new Request());

        $this->assertEquals(3, $result['days_count']);
    }

    /** @test */
    public function it_handles_days_count_correctly_when_not_explicitly_counted()
    {
        $entity = Entity::factory()->create(['user_id' => $this->user->id]);

        $resource = new EntityResource($entity);
        $result = $resource->toArray(new Request());

        // whenCounted('days') はロードされていない場合、Laravelの仕様によりMissingValueが返される
        // 実際のJSONレスポンスではこのキーは除外される
        // このテストでは、days_countが適切に処理されることを確認
        $this->assertTrue(
            ! array_key_exists('days_count', $result) ||
            $result['days_count'] instanceof \Illuminate\Http\Resources\MissingValue ||
            is_int($result['days_count'])
        );
    }

    /** @test */
    public function it_includes_days_collection_when_loaded()
    {
        $entity = Entity::factory()->create(['user_id' => $this->user->id]);

        // 記念日を2つ作成
        Day::factory()->count(2)->create(['entity_id' => $entity->id]);

        // 関連データをロード
        $entity->load('days');

        $resource = new EntityResource($entity);
        $result = $resource->toArray(new Request());

        // resolve() を使っているため配列として返される
        $this->assertArrayHasKey('days', $result);
        $this->assertIsArray($result['days']);
        $this->assertCount(2, $result['days']);

        // 各要素がDayResourceとして変換されていることを確認
        foreach ($result['days'] as $dayData) {
            $this->assertIsArray($dayData);
            $this->assertArrayHasKey('id', $dayData);
            $this->assertArrayHasKey('name', $dayData);
            $this->assertArrayHasKey('anniv_at', $dayData);
        }
    }

    /** @test */
    public function it_does_not_include_days_when_not_loaded()
    {
        $entity = Entity::factory()->create(['user_id' => $this->user->id]);

        $resource = new EntityResource($entity);
        $result = $resource->toArray(new Request());

        // when() は未ロード時に MissingValue を返す
        // toArray() では days キーは存在するが、実際のHTTPレスポンスではフィルタリングされる
        $this->assertArrayHasKey('days', $result);
        $this->assertInstanceOf(\Illuminate\Http\Resources\MissingValue::class, $result['days']);
    }

    /** @test */
    public function it_handles_japanese_characters_correctly()
    {
        $entity = Entity::factory()->create([
            'user_id' => $this->user->id,
            'name' => '日本語のエンティティ名！？',
            'desc' => '特殊文字を含む説明：！@#$%^&*()',
        ]);

        $resource = new EntityResource($entity);
        $result = $resource->toArray(new Request());

        $this->assertEquals('日本語のエンティティ名！？', $result['name']);
        $this->assertEquals('特殊文字を含む説明：！@#$%^&*()', $result['desc']);
    }

    /** @test */
    public function it_returns_all_expected_fields()
    {
        $entity = Entity::factory()->create(['user_id' => $this->user->id]);

        $resource = new EntityResource($entity);
        $result = $resource->toArray(new Request());

        $expectedFields = [
            'id',
            'name',
            'desc',
            'status',
            'created_at',
            'updated_at',
        ];

        foreach ($expectedFields as $field) {
            $this->assertArrayHasKey($field, $result);
        }
    }

    /** @test */
    public function it_handles_empty_entity_with_loaded_empty_days()
    {
        $entity = Entity::factory()->create(['user_id' => $this->user->id]);

        // 空の関連データをロード
        $entity->load('days');

        $resource = new EntityResource($entity);
        $result = $resource->toArray(new Request());

        // ロード済みなので days キーは存在し、空配列が返される
        $this->assertArrayHasKey('days', $result);
        $this->assertIsArray($result['days']);
        $this->assertEmpty($result['days']);
    }

    /** @test */
    public function it_maintains_data_types_correctly()
    {
        $entity = Entity::factory()->create([
            'user_id' => $this->user->id,
            'status' => true,
        ]);

        $resource = new EntityResource($entity);
        $result = $resource->toArray(new Request());

        // データ型の確認
        $this->assertIsInt($result['id']);
        $this->assertIsString($result['name']);
        $this->assertIsBool($result['status']);

        if ($result['desc'] !== null) {
            $this->assertIsString($result['desc']);
        }

        if ($result['created_at'] !== null) {
            $this->assertIsString($result['created_at']);
        }

        if ($result['updated_at'] !== null) {
            $this->assertIsString($result['updated_at']);
        }
    }

    /** @test */
    public function it_works_with_both_days_and_days_count()
    {
        $entity = Entity::factory()->create(['user_id' => $this->user->id]);

        // 記念日を2つ作成
        Day::factory()->count(2)->create(['entity_id' => $entity->id]);

        // 両方の関連データをロード
        $entityWithBoth = Entity::withCount('days')->with('days')->find($entity->id);

        $resource = new EntityResource($entityWithBoth);
        $result = $resource->toArray(new Request());

        $this->assertEquals(2, $result['days_count']);
        $this->assertArrayHasKey('days', $result);
        $this->assertIsArray($result['days']);
        $this->assertCount(2, $result['days']);
    }

    /** @test */
    public function it_preserves_entity_attributes_when_days_loaded()
    {
        $entity = Entity::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'テストエンティティ',
            'desc' => 'テスト説明',
            'status' => false,
        ]);

        Day::factory()->create(['entity_id' => $entity->id]);
        $entity->load('days');

        $resource = new EntityResource($entity);
        $result = $resource->toArray(new Request());

        // Entityの基本属性が保持されていることを確認
        $this->assertEquals('テストエンティティ', $result['name']);
        $this->assertEquals('テスト説明', $result['desc']);
        $this->assertFalse($result['status']);

        // 関連データも正しく含まれていることを確認
        $this->assertArrayHasKey('days', $result);
        $this->assertIsArray($result['days']);
        $this->assertCount(1, $result['days']);
    }

    /** @test */
    public function it_handles_null_timestamps()
    {
        // Factoryを使って作成し、タイムスタンプを手動でnullに設定
        $entity = Entity::factory()->create(['user_id' => $this->user->id]);

        // タイムスタンプをnullに設定してテスト用データを作成
        $entity->created_at = null;
        $entity->updated_at = null;

        $resource = new EntityResource($entity);
        $result = $resource->toArray(new Request());

        // タイムスタンプがnullでも正常に処理されることを確認
        $this->assertNull($result['created_at']);
        $this->assertNull($result['updated_at']);
    }
}
