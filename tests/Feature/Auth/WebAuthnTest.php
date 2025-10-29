<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laragear\WebAuthn\Models\WebAuthnCredential;
use Tests\TestCase;

class WebAuthnTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_registration_options(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/webauthn/register/options');

        $response->assertStatus(200);

        // Laragear/WebAuthnは直接publicKey構造を返す
        $this->assertNotEmpty($response->json());
    }

    public function test_guest_cannot_get_registration_options(): void
    {
        $response = $this->postJson('/webauthn/register/options');

        $response->assertStatus(401);
    }

    public function test_guest_can_get_login_options(): void
    {
        $response = $this->postJson('/webauthn/login/options');

        $response->assertStatus(200);

        // Laragear/WebAuthnは直接publicKey構造を返す
        $this->assertNotEmpty($response->json());
    }

    public function test_authenticated_user_can_get_their_credentials_list(): void
    {
        $user = User::factory()->create();

        // ユーザーにパスキーを登録
        WebAuthnCredential::unguarded(function () use ($user) {
            WebAuthnCredential::create([
                'id' => 'test-cred-'.uniqid(),
                'authenticatable_type' => 'App\Models\User',
                'authenticatable_id' => $user->id,
                'user_id' => $user->id,
                'alias' => 'Test Device',
                'counter' => 0,
                'rp_id' => 'localhost',
                'origin' => 'http://localhost',
                'transports' => json_encode(['internal']),
                'aaguid' => fake()->uuid(),
                'public_key' => base64_encode(random_bytes(65)),
                'attestation_format' => 'none',
            ]);
        });

        $response = $this->actingAs($user)
            ->getJson('/webauthn/credentials');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Test Device');
    }

    public function test_user_cannot_see_other_users_credentials(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // user1にパスキーを登録
        WebAuthnCredential::unguarded(function () use ($user1, $user2) {
            WebAuthnCredential::create([
                'id' => 'test-cred-'.uniqid(),
                'authenticatable_type' => 'App\Models\User',
                'authenticatable_id' => $user1->id,
                'user_id' => $user1->id,
                'alias' => 'User1 Device',
                'counter' => 0,
                'rp_id' => 'localhost',
                'origin' => 'http://localhost',
                'transports' => json_encode(['internal']),
                'aaguid' => fake()->uuid(),
                'public_key' => base64_encode(random_bytes(65)),
                'attestation_format' => 'none',
            ]);

            // user2にパスキーを登録
            WebAuthnCredential::create([
                'id' => 'test-cred-'.uniqid(),
                'authenticatable_type' => 'App\Models\User',
                'authenticatable_id' => $user2->id,
                'user_id' => $user2->id,
                'alias' => 'User2 Device',
                'counter' => 0,
                'rp_id' => 'localhost',
                'origin' => 'http://localhost',
                'transports' => json_encode(['internal']),
                'aaguid' => fake()->uuid(),
                'public_key' => base64_encode(random_bytes(65)),
                'attestation_format' => 'none',
            ]);
        });

        // user1でログインして一覧取得
        $response = $this->actingAs($user1)
            ->getJson('/webauthn/credentials');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'User1 Device');
    }

    public function test_user_can_delete_their_own_credential(): void
    {
        $user = User::factory()->create();

        $credential = WebAuthnCredential::unguarded(function () use ($user) {
            return WebAuthnCredential::create([
                'id' => 'test-cred-'.uniqid(),
                'authenticatable_type' => 'App\Models\User',
                'authenticatable_id' => $user->id,
                'user_id' => $user->id,
                'alias' => 'Test Device',
                'counter' => 0,
                'rp_id' => 'localhost',
                'origin' => 'http://localhost',
                'transports' => json_encode(['internal']),
                'aaguid' => fake()->uuid(),
                'public_key' => base64_encode(random_bytes(65)),
                'attestation_format' => 'none',
            ]);
        });

        $response = $this->actingAs($user)
            ->deleteJson('/webauthn/credentials/'.urlencode($credential->id));

        $response->assertStatus(200);

        $this->assertDatabaseMissing('webauthn_credentials', [
            'id' => $credential->id,
        ]);
    }

    public function test_user_cannot_delete_other_users_credential(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $credential = WebAuthnCredential::unguarded(function () use ($user2) {
            return WebAuthnCredential::create([
                'id' => 'test-cred-'.uniqid(),
                'authenticatable_type' => 'App\Models\User',
                'authenticatable_id' => $user2->id,
                'user_id' => $user2->id,
                'alias' => 'User2 Device',
                'counter' => 0,
                'rp_id' => 'localhost',
                'origin' => 'http://localhost',
                'transports' => json_encode(['internal']),
                'aaguid' => fake()->uuid(),
                'public_key' => base64_encode(random_bytes(65)),
                'attestation_format' => 'none',
            ]);
        });

        $response = $this->actingAs($user1)
            ->deleteJson('/webauthn/credentials/'.urlencode($credential->id));

        $response->assertStatus(404);

        // パスキーは削除されていないことを確認
        $this->assertDatabaseHas('webauthn_credentials', [
            'id' => $credential->id,
        ]);
    }

    public function test_guest_cannot_get_credentials_list(): void
    {
        $response = $this->getJson('/webauthn/credentials');

        $response->assertStatus(401);
    }

    public function test_guest_cannot_delete_credential(): void
    {
        $user = User::factory()->create();

        $credential = WebAuthnCredential::unguarded(function () use ($user) {
            return WebAuthnCredential::create([
                'id' => 'test-cred-'.uniqid(),
                'authenticatable_type' => 'App\Models\User',
                'authenticatable_id' => $user->id,
                'user_id' => $user->id,
                'alias' => 'Test Device',
                'counter' => 0,
                'rp_id' => 'localhost',
                'origin' => 'http://localhost',
                'transports' => json_encode(['internal']),
                'aaguid' => fake()->uuid(),
                'public_key' => base64_encode(random_bytes(65)),
                'attestation_format' => 'none',
            ]);
        });

        $response = $this->deleteJson('/webauthn/credentials/'.urlencode($credential->id));

        $response->assertStatus(401);

        // パスキーは削除されていないことを確認
        $this->assertDatabaseHas('webauthn_credentials', [
            'id' => $credential->id,
        ]);
    }
}
