<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Enums\ThemeMode;
use App\Filament\Widgets\GreetingWidget;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\RevenueChart;
use App\Filament\Widgets\WebsiteVisitorsChart;
use App\Filament\Widgets\TopSellingProducts;
use App\Filament\Widgets\NewCustomers;
use App\Filament\Widgets\SalesCalendarWidget;
use App\Filament\Widgets\CustomerRankWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Support\Assets\Css;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->authGuard('admin')
            ->login()
            ->brandName('Savora Admin')
            ->font('Outfit')
            ->defaultThemeMode(ThemeMode::Dark)
            ->databaseNotifications()
            ->databaseNotificationsPolling('15s')
            ->colors([
                'primary' => Color::Orange, // Main orange theme
                'gray' => Color::Slate,     // Deep blue-gray background
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'info' => Color::Sky,
                'danger' => Color::Rose,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
                \App\Filament\Pages\ManageSettings::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                \App\Filament\Widgets\RealtimeOrderAlert::class,
                StatsOverview::class,
                RevenueChart::class,
                WebsiteVisitorsChart::class,
                TopSellingProducts::class,
                NewCustomers::class,
                SalesCalendarWidget::class,
                CustomerRankWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->assets([
                Css::make('admin-animations', asset('css/admin-animations.css')),
            ])
            ->renderHook(
                \Filament\View\PanelsRenderHook::BODY_END,
                fn (): string => \Illuminate\Support\Facades\Blade::render('
                    <audio id="order-notification-sound" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" preload="auto"></audio>
                    <script>
                        (function() {
                            let lastCount = null;
                            setInterval(function() {
                                let badge = document.querySelector(".fi-topbar-database-notifications-btn .fi-badge") || document.querySelector("[aria-label*=\"notification\"] .fi-badge");
                                if (badge) {
                                    let count = parseInt(badge.innerText.replace(/\\D/g, "")) || 0;
                                    if (lastCount !== null && count > lastCount) {
                                        let audio = document.getElementById("order-notification-sound");
                                        if (audio) {
                                            audio.currentTime = 0;
                                            audio.play().catch(function(err) {});
                                        }
                                    }
                                    lastCount = count;
                                } else {
                                    if (lastCount === null) lastCount = 0;
                                }
                            }, 2000);
                        })();
                    </script>
                ')
            );
    }
}
