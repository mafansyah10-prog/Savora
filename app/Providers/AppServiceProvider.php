<?php

namespace App\Providers;

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
        // Force Sendmail and Sync queue on public server
        if (app()->environment('production') || (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'savorakuliner.my.id')) {
            config([
                'mail.default' => 'sendmail',
                'mail.from.address' => 'm.afansyah10@gmail.com',
                'mail.from.name' => 'Savora',
                'queue.default' => 'sync',
            ]);
        }
    }
}
