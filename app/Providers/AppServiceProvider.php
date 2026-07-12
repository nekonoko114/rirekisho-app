<?php

namespace App\Providers;

use App\Models\Cv;
use App\Models\Resume;
use App\Policies\CvPolicy;
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

        // Register policies so controllers can use $this->authorize()
        Gate::policy(Resume::class, ResumePolicy::class);
        Gate::policy(Cv::class, CvPolicy::class);

        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
