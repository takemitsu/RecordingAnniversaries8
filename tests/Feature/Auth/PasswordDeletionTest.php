<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laragear\WebAuthn\Models\WebAuthnCredential;
use Tests\TestCase;

class PasswordDeletionTest extends TestCase
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

    public function test_user_can_delete_password_with_google_oauth(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
            'google_id' => 'google-id-12345',
        ]);

        $response = $this->actingAs($user)
            ->delete(route('password.destroy'), [
                'current_password' => 'password',
            ]);

        $response->assertSessionHas('status', 'password-deleted');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'password' => null,
        ]);
    }

    public function test_user_can_delete_password_with_passkey(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $this->createCredential($user);

        $response = $this->actingAs($user)
            ->delete(route('password.destroy'), [
                'current_password' => 'password',
            ]);

        $response->assertSessionHas('status', 'password-deleted');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'password' => null,
        ]);
    }

    public function test_user_cannot_delete_password_without_backup_auth(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
            'google_id' => null,
        ]);

        $response = $this->actingAs($user)
            ->delete(route('password.destroy'), [
                'current_password' => 'password',
            ]);

        $response->assertSessionHasErrors('current_password');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
        ]);

        // パスワードがまだ存在することを確認
        $this->assertNotNull($user->fresh()->password);
    }

    public function test_password_deletion_requires_correct_password(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
            'google_id' => 'google-id-12345',
        ]);

        $response = $this->actingAs($user)
            ->delete(route('password.destroy'), [
                'current_password' => 'wrong-password',
            ]);

        $response->assertSessionHasErrors('current_password');

        $this->assertNotNull($user->fresh()->password);
    }

    public function test_guest_cannot_delete_password(): void
    {
        $response = $this->delete(route('password.destroy'), [
            'current_password' => 'password',
        ]);

        $response->assertRedirect(route('login'));
    }
}
