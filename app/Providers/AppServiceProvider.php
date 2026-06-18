<?php

namespace App\Providers;

use App\Models\Resume;
use App\Policies\ResumePolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(Router $router): void
    {
        // Register 'admin' middleware alias so routes can use ->middleware('admin')
        $router->aliasMiddleware('admin', \App\Http\Middleware\EnsureUserIsAdmin::class);

        Paginator::useBootstrapFive();

        // Register Resume policy so controllers can use $this->authorize()
        Gate::policy(Resume::class, ResumePolicy::class);

        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
