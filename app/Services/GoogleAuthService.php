<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class GoogleAuthService
{
    /**
     * 既存ユーザーと Google アカウントを紐づけ
     *
     * @param User $authUser 現在ログイン中のユーザー
     * @param string $googleId Google ID
     * @return array ['success' => bool, 'message' => string|null]
     */
    public function linkGoogleAccount(User $authUser, string $googleId): array
    {
        // 他のユーザーが既にその Google ID を使用していないかチェック
        $existingUser = User::where('google_id', $googleId)
            ->whereNot('id', $authUser->id)
            ->first();

        if ($existingUser) {
            return [
                'success' => false,
                'message' => 'used_other_user'
            ];
        }

        // Google ID を設定して保存
        $authUser->google_id = $googleId;
        $authUser->save();

        return ['success' => true, 'message' => null];
    }

    /**
     * Google アカウントでログインを試行
     *
     * @param string $googleId Google ID
     * @return User|null ログインできた場合はユーザー、できなかった場合は null
     */
    public function attemptGoogleLogin(string $googleId): ?User
    {
        $user = User::where('google_id', $googleId)->first();
        
        if ($user) {
            Auth::login($user);
            return $user;
        }

        return null;
    }

    /**
     * Google アカウント情報から新規ユーザーを作成
     *
     * @param SocialiteUser $googleUser Socialite ユーザー情報
     * @return array ['success' => bool, 'user' => User|null, 'message' => string|null]
     */
    public function createUserFromGoogle(SocialiteUser $googleUser): array
    {
        // メールアドレスが既に登録されているかチェック
        $existingUser = User::where('email', $googleUser->getEmail())->first();
        
        if ($existingUser) {
            return [
                'success' => false,
                'user' => null,
                'message' => 'email_already_exist'
            ];
        }

        // 新規ユーザー作成
        $user = new User();
        $user->name = $googleUser->getName();
        $user->email = $googleUser->getEmail();
        $user->google_id = $googleUser->getId();
        $user->save();

        Auth::login($user);

        return [
            'success' => true,
            'user' => $user,
            'message' => null
        ];
    }
}