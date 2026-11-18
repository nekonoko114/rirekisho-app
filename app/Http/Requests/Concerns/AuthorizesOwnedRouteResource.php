<?php

namespace App\Http\Requests\Concerns;

trait AuthorizesOwnedRouteResource
{
    /**
     * Authorize when the authenticated user owns the bound route resource or is an admin.
     */
    protected function authorizeOwnedRouteResource(string $routeParameter): bool
    {
        $resource = $this->route($routeParameter);
        $user = $this->user();

        if (! $user || ! $resource) {
            return false;
        }

        $isOwner = $resource->user_id && $resource->user_id === $user->id;
        $isAdmin = method_exists($user, 'isAdmin') && $user->isAdmin();

        return $isOwner || $isAdmin;
    }
}
