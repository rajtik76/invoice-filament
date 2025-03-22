<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
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
    public function boot(): void
    {
        Model::unguard();

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
            DB::prohibitDestructiveCommands();
        }

        if (! $this->app->environment('production')) {
            Model::preventLazyLoading();
        }
    }
}
