<?php

namespace App\Providers\Filament;

use App\Filament\I2cAdmin\Widgets\AppInfoWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class I2cAdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('i2c-admin')
            ->path('i2c-admin')
            ->login()
            ->colors([
                'primary' => Color::Violet,
            ])
            ->favicon(url: asset('images/i2c-favicon.ico'))
            ->brandLogo(asset('images/i2c-logo.png'))
            ->brandLogoHeight('2rem')
            ->sidebarCollapsibleOnDesktop()
            ->userMenuItems([
                Navigation\MenuItem::make()
                    ->label('Website')
                    ->url('/')
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-globe-alt'),
                'logout' => Navigation\MenuItem::make()
                    ->label('Sair'),
            ])
            ->discoverResources(in: app_path('Filament/I2cAdmin/Resources'), for: 'App\\Filament\\I2cAdmin\\Resources')
            ->discoverPages(in: app_path('Filament/I2cAdmin/Pages'), for: 'App\\Filament\\I2cAdmin\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/I2cAdmin/Widgets'), for: 'App\\Filament\\I2cAdmin\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
                AppInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
