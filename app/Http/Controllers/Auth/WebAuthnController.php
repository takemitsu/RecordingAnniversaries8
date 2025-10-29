<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebAuthnController extends Controller
{
    /**
     * パスキー登録用のオプションを生成
     */
    public function registerOptions(Request $request): JsonResponse
    {
        return response()->json(
            $request->user()->makeWebAuthnRegister()
        );
    }

    /**
     * パスキーを登録
     */
    public function register(Request $request): JsonResponse
    {
        $request->user()->confirmWebAuthnRegister($request);

        return response()->json([
            'message' => 'パスキーが登録されました',
        ]);
    }
}
