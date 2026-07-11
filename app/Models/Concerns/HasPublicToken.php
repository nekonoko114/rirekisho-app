<?php

namespace App\Models\Concerns;

/**
 * Shared public-token behavior for guest-shareable documents (Resume, Cv).
 * The token grants read access via ?token= URLs; compare with hash_equals().
 */
trait HasPublicToken
{
    public static function generateUniquePublicToken(): string
    {
        do {
            $token = bin2hex(random_bytes(16));
        } while (static::where('public_token', $token)->exists());

        return $token;
    }

    public function matchesPublicToken(?string $token): bool
    {
        return $this->public_token && $token && hash_equals($this->public_token, $token);
    }

    public function revokePublicToken(): void
    {
        $this->public_token = null;
        $this->save();
    }
}
