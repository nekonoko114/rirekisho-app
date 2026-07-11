<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Access check for documents viewable either by their owner/admin (policy)
 * or by guests holding a valid ?token= public token.
 */
trait AuthorizesPublicDocuments
{
    /**
     * Returns null when access is allowed. For guests without a valid token,
     * returns a redirect to login. Logged-in users without permission get a 403.
     */
    protected function authorizePublicView(Model $document): ?RedirectResponse
    {
        if (Auth::user()) {
            $this->authorize('view', $document);

            return null;
        }

        if ($document->matchesPublicToken(request()->query('token'))) {
            return null;
        }

        return redirect()->route('login');
    }
}
