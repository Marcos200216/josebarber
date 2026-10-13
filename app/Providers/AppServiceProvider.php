<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
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
        try {
    if (! file_exists(public_path('storage'))) {
        File::link(storage_path('app/public'), public_path('storage'));
    }
} catch (\Throwable $e) {
    // si falla, no rompe el sitio
}
    }
}
