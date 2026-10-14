<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            // Visual only: brand palette, fonts and grouped navigation; see public/assets/css/admin.css.
            ->colors([
                // Explicit red scale built on the logo red (#ED1C24 at 500). 600 is deepened so white button
                // text passes contrast; otherwise Filament falls back to pink buttons with dark text.
                'primary' => array_map(fn ($hex) => Color::convertToOklch($hex), [
                    50 => '#fef2f2', 100 => '#fde3e4', 200 => '#fbc7c9', 300 => '#f69a9e', 400 => '#f15a60',
                    500 => '#ed1c24', 600 => '#c8161d', 700 => '#a51218', 800 => '#861016', 900 => '#6e0f14', 950 => '#3c0609',
                ]),
                'gray' => Color::Stone,             // warm neutrals to match the storefront paper tones
                'success' => Color::hex('#066839'),
                'warning' => Color::Amber,
                'danger' => Color::Red,
                'info' => Color::Sky,
            ])
            ->font('Raleway')
            // "No" in yes/no columns was a red ✕ on every row, which reads as an error; make it a quiet grey dash.
            ->bootUsing(fn () => \Filament\Tables\Columns\IconColumn::configureUsing(
                fn (\Filament\Tables\Columns\IconColumn $column) => $column->falseIcon('heroicon-o-minus')->falseColor('gray')
            ))
            ->darkMode(false)
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups(['Catalog', 'Sales', 'Storefront', 'Settings'])
            ->renderHook(PanelsRenderHook::HEAD_END, fn () => new HtmlString(
                '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>'
                .'<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&display=swap">'
                .'<link rel="stylesheet" href="'.asset('assets/css/admin.css').'?v='.filemtime(public_path('assets/css/admin.css')).'">'
            ))
            ->brandName(fn () => config('redcherry.store_name').' Admin')
            ->brandLogo(fn () => \App\Support\SiteSettings::logoUrl())
            ->brandLogoHeight('2.75rem')
            ->favicon(fn () => \App\Support\SiteSettings::faviconUrl())
            ->widgets([
                \App\Filament\Widgets\SalesOverview::class, \App\Filament\Widgets\SalesChart::class,
                \App\Filament\Widgets\TopBooks::class, \App\Filament\Widgets\LatestOrders::class,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->pages([\Filament\Pages\Dashboard::class, \App\Filament\Pages\SiteSettings::class, \App\Filament\Pages\OfferPopup::class, \App\Filament\Pages\ShippingSettings::class])
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
