<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterWebAuthnRequest;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Laragear\WebAuthn\Http\Requests\AssertedRequest;
use Laragear\WebAuthn\Http\Requests\AssertionRequest;
use Laragear\WebAuthn\Http\Requests\AttestedRequest;

class WebAuthnController extends Controller
{
    /**
     * パスキー登録用のオプションを生成
     */
    public function registerOptions(RegisterWebAuthnRequest $request): Responsable
    {
        return $request->toCreate();
    }

    /**
     * パスキーを登録
     */
    public function register(AttestedRequest $request): JsonResponse
    {
        $request->save();

        return response()->json([
            'message' => 'パスキーが登録されました',
        ]);
    }

    /**
     * パスキーログインのオプションを生成
     */
    public function loginOptions(): Responsable
    {
        return app(AssertionRequest::class)->toVerify();
    }

    /**
     * パスキーでログイン
     */
    public function login(AssertedRequest $request): JsonResponse
    {
        $user = $request->login();

        if (! $user) {
            return response()->json([
                'message' => '認証に失敗しました',
            ], 401);
        }

        // セッション固定攻撃を防ぐためセッションを再生成
        $request->session()->regenerate();

        return response()->json([
            'message' => 'ログインしました',
        ]);
    }
}
