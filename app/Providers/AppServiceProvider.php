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
        // Force SMTP and Sync queue on public server
        if (app()->environment('production') || (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'savorakuliner.my.id')) {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.transport' => 'smtp',
                'mail.mailers.smtp.host' => 'smtp.gmail.com',
                'mail.mailers.smtp.port' => 587,
                'mail.mailers.smtp.encryption' => 'tls',
                'mail.mailers.smtp.scheme' => null,
                'mail.mailers.smtp.username' => 'm.afansyah10@gmail.com',
                'mail.mailers.smtp.password' => 'hfyozosdkveaqywb',
                'mail.mailers.smtp.stream' => [
                    'ssl' => [
                        'allow_self_signed' => true,
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ],
                ],
                'mail.from.address' => 'm.afansyah10@gmail.com',
                'mail.from.name' => 'Savora',
                'queue.default' => 'sync',
            ]);
        }
    }
}
