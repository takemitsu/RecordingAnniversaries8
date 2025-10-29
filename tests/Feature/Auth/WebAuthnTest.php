<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laragear\WebAuthn\Models\WebAuthnCredential;
use Tests\TestCase;

class WebAuthnTest extends TestCase
{
    use RefreshDatabase;

    /**
     * WebAuthnCredentialのテストデータを作成するヘルパーメソッド
     */
    private function createCredential(User $user, array $attributes = []): WebAuthnCredential
    {
        return WebAuthnCredential::unguarded(function () use ($user, $attributes) {
            return WebAuthnCredential::create(array_merge([
                'id' => 'test-cred-'.uniqid(),
                'authenticatable_type' => User::class,
                'authenticatable_id' => $user->id,
                'user_id' => $user->id,
                'alias' => 'Test Device',
                'counter' => 0,
                'rp_id' => config('webauthn.relying_party.id', 'localhost'),
                'origin' => config('app.url', 'http://localhost'),
                'transports' => json_encode(['internal']),
                'aaguid' => fake()->uuid(),
                'public_key' => base64_encode(random_bytes(65)),
                'attestation_format' => 'none',
            ], $attributes));
        });
    }

    public function test_authenticated_user_can_get_registration_options(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/webauthn/register/options');

        // デバッグ用: 実際のレスポンスを確認
        // dump($response->json());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'challenge',
                'rp' => ['name', 'id'],
                'user' => ['id', 'name', 'displayName'],
                'pubKeyCredParams' => [
                    '*' => ['type', 'alg'],
                ],
                'timeout',
                'attestation',
                'authenticatorSelection',
            ]);
    }

    public function test_guest_cannot_get_registration_options(): void
    {
        $response = $this->postJson('/webauthn/register/options');

        $response->assertStatus(401);
    }

    public function test_guest_can_get_login_options(): void
    {
        $response = $this->postJson('/webauthn/login/options');

        // デバッグ用: 実際のレスポンスを確認
        // dump($response->json());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'challenge',
                'rpId',
                'timeout',
            ]);
    }

    public function test_authenticated_user_can_get_their_credentials_list(): void
    {
        $user = User::factory()->create();

        // ユーザーにパスキーを登録
        $this->createCredential($user);

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
        $this->createCredential($user1, ['alias' => 'User1 Device']);

        // user2にパスキーを登録
        $this->createCredential($user2, ['alias' => 'User2 Device']);

        // user1でログインして一覧取得
        $response = $this->actingAs($user1)
            ->getJson('/webauthn/credentials');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'User1 Device');
    }

    public function test_user_can_delete_their_own_credential(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'), // バックアップ認証があるため削除可能
        ]);

        $credential = $this->createCredential($user);

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

        $credential = $this->createCredential($user2, ['alias' => 'User2 Device']);

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

        $credential = $this->createCredential($user);

        $response = $this->deleteJson('/webauthn/credentials/'.urlencode($credential->id));

        $response->assertStatus(401);

        // パスキーは削除されていないことを確認
        $this->assertDatabaseHas('webauthn_credentials', [
            'id' => $credential->id,
        ]);
    }

    public function test_user_cannot_delete_last_credential_without_backup_auth(): void
    {
        $user = User::factory()->create([
            'password' => null,
            'google_id' => null,
        ]);

        $credential = $this->createCredential($user);

        $response = $this->actingAs($user)
            ->deleteJson('/webauthn/credentials/'.urlencode($credential->id));

        $response->assertStatus(403)
            ->assertJson([
                'message' => '最後のパスキーは削除できません。他の認証方法を設定してください。',
            ]);

        $this->assertDatabaseHas('webauthn_credentials', [
            'id' => $credential->id,
        ]);
    }

    public function test_user_can_delete_last_credential_with_password(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $credential = $this->createCredential($user);

        $response = $this->actingAs($user)
            ->deleteJson('/webauthn/credentials/'.urlencode($credential->id));

        $response->assertStatus(200);

        $this->assertDatabaseMissing('webauthn_credentials', [
            'id' => $credential->id,
        ]);
    }

    public function test_user_can_delete_last_credential_with_google_oauth(): void
    {
        $user = User::factory()->create([
            'password' => null,
            'google_id' => 'google-id-12345',
        ]);

        $credential = $this->createCredential($user);

        $response = $this->actingAs($user)
            ->deleteJson('/webauthn/credentials/'.urlencode($credential->id));

        $response->assertStatus(200);

        $this->assertDatabaseMissing('webauthn_credentials', [
            'id' => $credential->id,
        ]);
    }

    public function test_user_can_delete_non_last_credential(): void
    {
        $user = User::factory()->create([
            'password' => null,
            'google_id' => null,
        ]);

        $credential1 = $this->createCredential($user, ['alias' => 'Device 1']);
        $credential2 = $this->createCredential($user, ['alias' => 'Device 2']);

        $response = $this->actingAs($user)
            ->deleteJson('/webauthn/credentials/'.urlencode($credential1->id));

        $response->assertStatus(200);

        $this->assertDatabaseMissing('webauthn_credentials', [
            'id' => $credential1->id,
        ]);
        $this->assertDatabaseHas('webauthn_credentials', [
            'id' => $credential2->id,
        ]);
    }

    public function test_cannot_delete_nonexistent_credential(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->deleteJson('/webauthn/credentials/'.urlencode('nonexistent-id'));

        $response->assertStatus(404);
    }

    public function test_returns_empty_array_when_user_has_no_credentials(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/webauthn/credentials');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_credentials_list_is_ordered_by_updated_at_desc(): void
    {
        $user = User::factory()->create();

        $credential1 = $this->createCredential($user, ['alias' => 'Old Device']);
        sleep(1); // 時間差を確実にする
        $credential2 = $this->createCredential($user, ['alias' => 'New Device']);

        $response = $this->actingAs($user)
            ->getJson('/webauthn/credentials');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.name', 'New Device')
            ->assertJsonPath('data.1.name', 'Old Device');
    }
}
