<?php

namespace App\Providers;

use App\Models\KunjunganSitus;
use Illuminate\Support\Facades\View;
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
        // Data footer situs publik (statistik pengunjung di-cache, lihat KunjunganSitus::statistik()).
        View::composer('layouts.publik', function ($view) {
            $view->with([
                'situs' => config('site'),
                'statistikPengunjung' => KunjunganSitus::statistik(),
            ]);
        });
    }
}
