<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\StudioProfile;

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
        // Mengirim variabel $studio ke semua view (atau khusus view layout/footer)
        View::composer('*', function ($view) {
            $view->with('studio', StudioProfile::first());
        });
    }
}
