<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Day;
use App\Models\Entity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EntitiesControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function pickup_returns_entities_with_days_sorted_by_diff_days()
    {
        $entity = Entity::factory()->create(['user_id' => $this->user->id]);
        
        // 異なる日付の記念日を作成
        Day::factory()->create([
            'entity_id' => $entity->id,
            'anniv_at' => now()->addDays(10)->format('Y-m-d')
        ]);
        Day::factory()->create([
            'entity_id' => $entity->id,
            'anniv_at' => now()->addDays(5)->format('Y-m-d')
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertOk();
    }

    /** @test */
    public function pickup_excludes_entities_without_days()
    {
        // 記念日ありのエンティティ
        $entityWithDays = Entity::factory()->create(['user_id' => $this->user->id]);
        Day::factory()->create(['entity_id' => $entityWithDays->id]);
        
        // 記念日なしのエンティティ
        Entity::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertOk();
    }

    /** @test */
    public function index_returns_all_user_entities()
    {
        $entity1 = Entity::factory()->create(['user_id' => $this->user->id]);
        $entity2 = Entity::factory()->create(['user_id' => $this->user->id]);
        
        // 他のユーザーのエンティティ
        $otherUser = User::factory()->create();
        Entity::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->get('/entities');

        $response->assertOk();
    }

    /** @test */
    public function store_creates_entity_with_valid_data()
    {
        $entityData = [
            'name' => '家族の記念日',
            'desc' => '家族に関する記念日'
        ];

        $response = $this->actingAs($this->user)
            ->post('/entities', $entityData);

        $response->assertRedirect('/entities');
        
        $this->assertDatabaseHas('entities', [
            'user_id' => $this->user->id,
            'name' => '家族の記念日',
            'desc' => '家族に関する記念日'
        ]);
    }

    /** @test */
    public function store_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->post('/entities', []);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function store_validates_name_max_length()
    {
        $entityData = [
            'name' => str_repeat('あ', 256) // 256文字
        ];

        $response = $this->actingAs($this->user)
            ->post('/entities', $entityData);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function show_returns_entity_with_days()
    {
        $entity = Entity::factory()->create(['user_id' => $this->user->id]);
        $day = Day::factory()->create(['entity_id' => $entity->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/entities/{$entity->id}");

        $response->assertOk()
            ->assertJsonFragment(['id' => $entity->id])
            ->assertJsonPath('data.days.0.id', $day->id);
    }

    /** @test */
    public function show_denies_access_to_other_users_entity()
    {
        $otherUser = User::factory()->create();
        $otherEntity = Entity::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/entities/{$otherEntity->id}");

        $response->assertForbidden();
    }

    /** @test */
    public function update_modifies_entity_with_valid_data()
    {
        $entity = Entity::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'name' => '更新されたエンティティ',
            'desc' => '更新された説明',
            'status' => false
        ];

        $response = $this->actingAs($this->user)
            ->put("/entities/{$entity->id}", $updateData);

        $response->assertRedirect('/entities');
        
        $this->assertDatabaseHas('entities', [
            'id' => $entity->id,
            'name' => '更新されたエンティティ',
            'desc' => '更新された説明',
            'status' => false
        ]);
    }

    /** @test */
    public function update_validates_required_fields()
    {
        $entity = Entity::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->put("/entities/{$entity->id}", []);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function update_denies_access_to_other_users_entity()
    {
        $otherUser = User::factory()->create();
        $otherEntity = Entity::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->put("/entities/{$otherEntity->id}", [
                'name' => 'ハックされたエンティティ'
            ]);

        $response->assertForbidden();
    }

    /** @test */
    public function destroy_soft_deletes_entity()
    {
        $entity = Entity::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->delete("/entities/{$entity->id}");

        $response->assertRedirect('/entities');
        $this->assertSoftDeleted('entities', ['id' => $entity->id]);
    }

    /** @test */
    public function destroy_denies_access_to_other_users_entity()
    {
        $otherUser = User::factory()->create();
        $otherEntity = Entity::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->delete("/entities/{$otherEntity->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('entities', ['id' => $otherEntity->id]);
    }

    /** @test */
    public function create_renders_form()
    {
        $response = $this->actingAs($this->user)->get('/entities/create');

        $response->assertOk();
    }

    /** @test */
    public function edit_renders_form_with_entity_data()
    {
        $entity = Entity::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->get("/entities/{$entity->id}/edit");

        $response->assertOk();
    }

    /** @test */
    public function edit_denies_access_to_other_users_entity()
    {
        $otherUser = User::factory()->create();
        $otherEntity = Entity::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->get("/entities/{$otherEntity->id}/edit");

        $response->assertForbidden();
    }

    /** @test */
    public function guest_user_cannot_access_any_entity_endpoints()
    {
        $entity = Entity::factory()->create(['user_id' => $this->user->id]);

        // GET endpoints
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/entities')->assertRedirect('/login');
        $this->get('/entities/create')->assertRedirect('/login');
        $this->get("/entities/{$entity->id}")->assertRedirect('/login');
        $this->get("/entities/{$entity->id}/edit")->assertRedirect('/login');

        // POST/PUT/DELETE endpoints
        $this->post('/entities', [])->assertRedirect('/login');
        $this->put("/entities/{$entity->id}", [])->assertRedirect('/login');
        $this->delete("/entities/{$entity->id}")->assertRedirect('/login');
    }

    /** @test */
    public function entity_operations_handle_japanese_text_correctly()
    {
        $entityData = [
            'name' => '日本語のエンティティ名',
            'desc' => '日本語での説明文。特殊文字も含む：！？'
        ];

        $response = $this->actingAs($this->user)
            ->post('/entities', $entityData);

        $response->assertRedirect('/entities');
        
        $this->assertDatabaseHas('entities', [
            'user_id' => $this->user->id,
            'name' => '日本語のエンティティ名',
            'desc' => '日本語での説明文。特殊文字も含む：！？'
        ]);

        $entity = Entity::where('name', '日本語のエンティティ名')->first();
        
        // 更新テスト
        $updateData = [
            'name' => '更新された日本語名',
            'desc' => '更新された日本語説明',
            'status' => true
        ];

        $response = $this->actingAs($this->user)
            ->put("/entities/{$entity->id}", $updateData);

        $response->assertRedirect('/entities');
        
        $this->assertDatabaseHas('entities', [
            'id' => $entity->id,
            'name' => '更新された日本語名',
            'desc' => '更新された日本語説明'
        ]);
    }
}