<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterWebAuthnRequest;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
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
}
