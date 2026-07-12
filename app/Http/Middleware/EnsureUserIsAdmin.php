<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user || ! method_exists($user, 'isAdmin') || ! $user->isAdmin()) {
            // If guest, redirect to login; otherwise show 403
            if (! $user) {
                return Redirect::guest(route('login'));
            }
            abort(403, '管理者権限が必要です');
        }

        return $next($request);
    }
}
