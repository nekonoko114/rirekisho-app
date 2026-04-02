<?php

namespace App\Policies;

use App\Models\Resume;
use App\Models\User;

class ResumePolicy
{
    /**
     * Determine whether the user can view the resume (owner or admin).
     */
    public function view(User $user, Resume $resume): bool
    {
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        return ($resume->user_id !== null) && $resume->user_id === $user->id;
    }

    /**
     * Determine whether the user can update the resume.
     */
    public function update(User $user, Resume $resume): bool
    {
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        return ($resume->user_id !== null) && $resume->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the resume.
     */
    public function delete(User $user, Resume $resume): bool
    {
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        return ($resume->user_id !== null) && $resume->user_id === $user->id;
    }

    /**
     * Determine whether the user can revoke public token.
     */
    public function revokePublic(User $user, Resume $resume): bool
    {
        return $this->update($user, $resume);
    }
}
