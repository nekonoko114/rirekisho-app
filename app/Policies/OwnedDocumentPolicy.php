<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Base policy for user-owned documents (Resume, Cv): the owner or an admin
 * may view/update/delete. Guest-created documents (user_id NULL) are never
 * accessible through these abilities — guests go through the public token.
 */
abstract class OwnedDocumentPolicy
{
    public function view(User $user, Model $document): bool
    {
        return $this->ownsOrAdmin($user, $document);
    }

    public function update(User $user, Model $document): bool
    {
        return $this->ownsOrAdmin($user, $document);
    }

    public function delete(User $user, Model $document): bool
    {
        return $this->ownsOrAdmin($user, $document);
    }

    public function revokePublic(User $user, Model $document): bool
    {
        return $this->ownsOrAdmin($user, $document);
    }

    protected function ownsOrAdmin(User $user, Model $document): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $document->user_id !== null && $document->user_id === $user->id;
    }
}
