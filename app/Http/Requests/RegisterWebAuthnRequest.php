<?php

namespace App\Http\Requests;

use Laragear\WebAuthn\Http\Requests\AttestationRequest;

class RegisterWebAuthnRequest extends AttestationRequest
{
    /**
     * Validate the class instance.
     */
    public function validateResolved(): void
    {
        // 認証済みユーザーがいるかチェック
        if (! $this->user()) {
            $this->failedAuthorization();
        }
    }
}
