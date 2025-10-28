<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Day;
use App\Models\Entity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DaysControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Entity $entity;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->entity = Entity::factory()->create(['user_id' => $this->user->id]);
    }

    /** @test */
    public function index_returns_days_for_authenticated_user()
    {
        $day1 = Day::factory()->create(['entity_id' => $this->entity->id]);
        $day2 = Day::factory()->create(['entity_id' => $this->entity->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/entities/{$this->entity->id}/days");

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['id' => $day1->id])
            ->assertJsonFragment(['id' => $day2->id]);
    }

    /** @test */
    public function index_denies_access_to_other_users_entity()
    {
        $otherUser = User::factory()->create();
        $otherEntity = Entity::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/entities/{$otherEntity->id}/days");

        $response->assertForbidden();
    }

    /** @test */
    public function store_creates_day_with_valid_data()
    {
        $dayData = [
            'name' => '誕生日',
            'desc' => '大切な人の誕生日',
            'anniv_at' => '2023-12-25',
        ];

        $response = $this->actingAs($this->user)
            ->post("/entities/{$this->entity->id}/days", $dayData);

        $response->assertRedirect('/entities');

        $this->assertDatabaseHas('days', [
            'entity_id' => $this->entity->id,
            'name' => '誕生日',
            'desc' => '大切な人の誕生日',
            'anniv_at' => '2023-12-25',
        ]);
    }

    /** @test */
    public function store_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->post("/entities/{$this->entity->id}/days", []);

        $response->assertSessionHasErrors(['name', 'anniv_at']);
    }

    /** @test */
    public function store_validates_date_format()
    {
        $dayData = [
            'name' => '記念日',
            'anniv_at' => 'invalid-date',
        ];

        $response = $this->actingAs($this->user)
            ->post("/entities/{$this->entity->id}/days", $dayData);

        $response->assertSessionHasErrors(['anniv_at']);
    }

    /** @test */
    public function show_returns_day_data()
    {
        $day = Day::factory()->create(['entity_id' => $this->entity->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/entities/{$this->entity->id}/days/{$day->id}");

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $day->id,
                'name' => $day->name,
                'anniv_at' => $day->anniv_at,
            ]);
    }

    /** @test */
    public function show_denies_access_to_other_users_day()
    {
        $otherUser = User::factory()->create();
        $otherEntity = Entity::factory()->create(['user_id' => $otherUser->id]);
        $otherDay = Day::factory()->create(['entity_id' => $otherEntity->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/entities/{$otherEntity->id}/days/{$otherDay->id}");

        $response->assertForbidden();
    }

    /** @test */
    public function update_modifies_day_with_valid_data()
    {
        $day = Day::factory()->create(['entity_id' => $this->entity->id]);

        $updateData = [
            'name' => '更新された名前',
            'desc' => '更新された説明',
            'anniv_at' => '2024-01-01',
        ];

        $response = $this->actingAs($this->user)
            ->put("/entities/{$this->entity->id}/days/{$day->id}", $updateData);

        $response->assertRedirect('/entities');

        $this->assertDatabaseHas('days', [
            'id' => $day->id,
            'name' => '更新された名前',
            'desc' => '更新された説明',
            'anniv_at' => '2024-01-01',
        ]);
    }

    /** @test */
    public function update_validates_required_fields()
    {
        $day = Day::factory()->create(['entity_id' => $this->entity->id]);

        $response = $this->actingAs($this->user)
            ->put("/entities/{$this->entity->id}/days/{$day->id}", []);

        $response->assertSessionHasErrors(['name', 'anniv_at']);
    }

    /** @test */
    public function destroy_soft_deletes_day()
    {
        $day = Day::factory()->create(['entity_id' => $this->entity->id]);

        $response = $this->actingAs($this->user)
            ->delete("/entities/{$this->entity->id}/days/{$day->id}");

        $response->assertRedirect('/entities');
        $this->assertSoftDeleted('days', ['id' => $day->id]);
    }

    /** @test */
    public function destroy_denies_access_to_other_users_day()
    {
        $otherUser = User::factory()->create();
        $otherEntity = Entity::factory()->create(['user_id' => $otherUser->id]);
        $otherDay = Day::factory()->create(['entity_id' => $otherEntity->id]);

        $response = $this->actingAs($this->user)
            ->delete("/entities/{$otherEntity->id}/days/{$otherDay->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('days', ['id' => $otherDay->id]);
    }

    /** @test */
    public function create_renders_form()
    {
        $response = $this->actingAs($this->user)
            ->get("/entities/{$this->entity->id}/days/create");

        $response->assertOk();
    }

    /** @test */
    public function create_denies_access_to_other_users_entity()
    {
        $otherUser = User::factory()->create();
        $otherEntity = Entity::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->get("/entities/{$otherEntity->id}/days/create");

        $response->assertForbidden();
    }

    /** @test */
    public function edit_renders_form_with_day_data()
    {
        $day = Day::factory()->create(['entity_id' => $this->entity->id]);

        $response = $this->actingAs($this->user)
            ->get("/entities/{$this->entity->id}/days/{$day->id}/edit");

        $response->assertOk();
    }

    /** @test */
    public function edit_denies_access_to_other_users_day()
    {
        $otherUser = User::factory()->create();
        $otherEntity = Entity::factory()->create(['user_id' => $otherUser->id]);
        $otherDay = Day::factory()->create(['entity_id' => $otherEntity->id]);

        $response = $this->actingAs($this->user)
            ->get("/entities/{$otherEntity->id}/days/{$otherDay->id}/edit");

        $response->assertForbidden();
    }

    /** @test */
    public function guest_user_cannot_access_any_day_endpoints()
    {
        $day = Day::factory()->create(['entity_id' => $this->entity->id]);

        // GET endpoints
        $this->get("/entities/{$this->entity->id}/days")->assertRedirect('/login');
        $this->get("/entities/{$this->entity->id}/days/create")->assertRedirect('/login');
        $this->get("/entities/{$this->entity->id}/days/{$day->id}")->assertRedirect('/login');
        $this->get("/entities/{$this->entity->id}/days/{$day->id}/edit")->assertRedirect('/login');

        // POST/PUT/DELETE endpoints
        $this->post("/entities/{$this->entity->id}/days", [])->assertRedirect('/login');
        $this->put("/entities/{$this->entity->id}/days/{$day->id}", [])->assertRedirect('/login');
        $this->delete("/entities/{$this->entity->id}/days/{$day->id}")->assertRedirect('/login');
    }
}
