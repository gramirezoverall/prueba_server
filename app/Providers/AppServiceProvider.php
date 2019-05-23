<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\GetLinks;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(\GetLinks::class, function(){
            return new GetLinks();
        });
    }
}
