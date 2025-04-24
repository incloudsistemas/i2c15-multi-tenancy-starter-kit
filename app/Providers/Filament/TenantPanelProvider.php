<?php

namespace App\Providers\Filament;

use App\Filament\Pages\System\EditProfile;
use App\Filament\Tenant\Pages\System\EditTenantAccount;
use App\Filament\Tenant\Pages\System\RegisterTenantAccount;
use App\Filament\Tenant\Widgets\AppInfoWidget;
use App\Models\System\TenantAccount;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class TenantPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('tenant')
            ->path('admin')
            ->login()
            ->passwordReset()
            ->colors([
                'primary' => Color::Yellow,
            ])
            ->favicon(url: asset('images/i2c-favicon.ico'))
            ->brandLogo(asset('images/i2c-logo.png'))
            ->brandLogoHeight('2rem')
            ->sidebarCollapsibleOnDesktop()
            ->profile(EditProfile::class)
            ->maxContentWidth(MaxWidth::Full)
            ->userMenuItems([
                'profile' => Navigation\MenuItem::make()
                    ->label('Meu Perfil'),
                Navigation\MenuItem::make()
                    ->label('Website')
                    ->url('/')
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-globe-alt'),
                'logout' => Navigation\MenuItem::make()
                    ->label('Sair'),
            ])
            ->discoverResources(in: app_path('Filament/Tenant/Resources'), for: 'App\\Filament\\Tenant\\Resources')
            ->discoverPages(in: app_path('Filament/Tenant/Pages'), for: 'App\\Filament\\Tenant\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Tenant/Widgets'), for: 'App\\Filament\\Tenant\\Widgets')
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
            ])
            ->tenant(TenantAccount::class, slugAttribute: 'slug')
            ->tenantRegistration(RegisterTenantAccount::class)
            ->tenantProfile(EditTenantAccount::class)
            ->tenantMenu(
                fn(): bool =>
                !auth()->user()->hasAnyRole(['Superadministrador'])
            )
            ->databaseTransactions();
    }
}
