<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Laragear\WebAuthn\Models\WebAuthnCredential;

/**
 * @mixin WebAuthnCredential
 */
class WebAuthnCredentialResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->alias ?? 'デバイス',
            'transports' => $this->transports ?? [],
            'created_at' => $this->created_at->toIso8601String(),
            'last_used_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
