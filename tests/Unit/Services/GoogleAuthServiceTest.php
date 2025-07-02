<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\GoogleAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private GoogleAuthService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GoogleAuthService();
    }

    /** @test */
    public function linkGoogleAccount_success_when_google_id_is_not_used()
    {
        $user = User::factory()->create();
        $googleId = 'google123';

        $result = $this->service->linkGoogleAccount($user, $googleId);

        $this->assertTrue($result['success']);
        $this->assertNull($result['message']);
        $this->assertEquals($googleId, $user->fresh()->google_id);
    }

    /** @test */
    public function linkGoogleAccount_fails_when_google_id_is_used_by_another_user()
    {
        $existingUser = User::factory()->create(['google_id' => 'google123']);
        $currentUser = User::factory()->create();

        $result = $this->service->linkGoogleAccount($currentUser, 'google123');

        $this->assertFalse($result['success']);
        $this->assertEquals('used_other_user', $result['message']);
        $this->assertNull($currentUser->fresh()->google_id);
    }

    /** @test */
    public function linkGoogleAccount_success_when_user_updates_own_google_id()
    {
        $user = User::factory()->create(['google_id' => 'old_google_id']);
        $newGoogleId = 'new_google_id';

        $result = $this->service->linkGoogleAccount($user, $newGoogleId);

        $this->assertTrue($result['success']);
        $this->assertNull($result['message']);
        $this->assertEquals($newGoogleId, $user->fresh()->google_id);
    }

    /** @test */
    public function attemptGoogleLogin_success_when_user_exists()
    {
        $user = User::factory()->create(['google_id' => 'google123']);

        $result = $this->service->attemptGoogleLogin('google123');

        $this->assertEquals($user->id, $result->id);
        $this->assertEquals($user->id, Auth::id());
    }

    /** @test */
    public function attemptGoogleLogin_returns_null_when_user_does_not_exist()
    {
        $result = $this->service->attemptGoogleLogin('nonexistent_google_id');

        $this->assertNull($result);
        $this->assertNull(Auth::id());
    }

    /** @test */
    public function createUserFromGoogle_success_when_email_is_not_used()
    {
        $googleUser = Mockery::mock(SocialiteUser::class);
        $googleUser->shouldReceive('getName')->andReturn('John Doe');
        $googleUser->shouldReceive('getEmail')->andReturn('john@example.com');
        $googleUser->shouldReceive('getId')->andReturn('google123');

        $result = $this->service->createUserFromGoogle($googleUser);

        $this->assertTrue($result['success']);
        $this->assertNull($result['message']);
        $this->assertInstanceOf(User::class, $result['user']);
        $this->assertEquals('John Doe', $result['user']->name);
        $this->assertEquals('john@example.com', $result['user']->email);
        $this->assertEquals('google123', $result['user']->google_id);
        $this->assertEquals($result['user']->id, Auth::id());
    }

    /** @test */
    public function createUserFromGoogle_fails_when_email_already_exists()
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $googleUser = Mockery::mock(SocialiteUser::class);
        $googleUser->shouldReceive('getName')->andReturn('John Doe');
        $googleUser->shouldReceive('getEmail')->andReturn('existing@example.com');
        $googleUser->shouldReceive('getId')->andReturn('google123');

        $result = $this->service->createUserFromGoogle($googleUser);

        $this->assertFalse($result['success']);
        $this->assertEquals('email_already_exist', $result['message']);
        $this->assertNull($result['user']);
        $this->assertNull(Auth::id());
    }

    /** @test */
    public function createUserFromGoogle_creates_user_with_correct_attributes()
    {
        $googleUser = Mockery::mock(SocialiteUser::class);
        $googleUser->shouldReceive('getName')->andReturn('田中太郎');
        $googleUser->shouldReceive('getEmail')->andReturn('tanaka@example.com');
        $googleUser->shouldReceive('getId')->andReturn('google_japanese_user');

        $result = $this->service->createUserFromGoogle($googleUser);

        $this->assertTrue($result['success']);
        $createdUser = User::where('email', 'tanaka@example.com')->first();
        $this->assertNotNull($createdUser);
        $this->assertEquals('田中太郎', $createdUser->name);
        $this->assertEquals('google_japanese_user', $createdUser->google_id);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}