<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\GoogleAuthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;


class SocialiteController extends Controller
{
    private GoogleAuthService $googleAuthService;

    public function __construct(GoogleAuthService $googleAuthService)
    {
        $this->googleAuthService = $googleAuthService;
    }


    public function redirectGoogle(): \Symfony\Component\HttpFoundation\RedirectResponse|\Illuminate\Http\RedirectResponse
    {
        Log::info('redirect!!');
        return Socialite::driver('google')->redirect();
    }

    public function callbackGoogle()
    {
        $authUser = auth()->user();
        $googleUser = Socialite::driver('google')->user();
        $googleId = $googleUser->getId();

        if ($authUser) {
            return $this->handleLoggedInUser($authUser, $googleId);
        }
        
        return $this->handleGuestUser($googleUser, $googleId);
    }

    /**
     * ログイン済みユーザーの Google アカウント結合処理
     */
    private function handleLoggedInUser(User $authUser, string $googleId)
    {
        $result = $this->googleAuthService->linkGoogleAccount($authUser, $googleId);
        
        if (!$result['success']) {
            return redirect()->route('login')->with('status', $result['message']);
        }

        return redirect()->route('profile.edit');
    }

    /**
     * 未ログインユーザーの Google アカウント処理
     */
    private function handleGuestUser($googleUser, string $googleId)
    {
        // 既存ユーザーでのログインを試行
        $existingUser = $this->googleAuthService->attemptGoogleLogin($googleId);
        if ($existingUser) {
            return redirect()->route('dashboard');
        }

        // 新規ユーザー作成を試行
        $result = $this->googleAuthService->createUserFromGoogle($googleUser);
        
        if (!$result['success']) {
            return redirect()->route('login')->with('status', $result['message']);
        }

        return redirect()->route('dashboard');
    }
}
