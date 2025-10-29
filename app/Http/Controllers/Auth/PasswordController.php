<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeletePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back();
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back();
    }

    /**
     * パスワードを削除（パスワードレス認証に移行）
     */
    public function destroy(DeletePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();

        // バックアップ認証方法があるかチェック
        $hasGoogleOAuth = ! is_null($user->google_id);
        $hasPasskey = $user->webAuthnCredentials()->exists();

        if (! $hasGoogleOAuth && ! $hasPasskey) {
            return back()->withErrors([
                'current_password' => 'パスワードを削除するには、Google認証またはパスキーを設定してください。',
            ]);
        }

        // パスワードを削除
        $user->password = null;
        $user->save();

        return back()->with('status', 'password-deleted');
    }
}
