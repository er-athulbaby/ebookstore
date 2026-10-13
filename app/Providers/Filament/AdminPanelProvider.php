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
                // Explicit burgundy scale (logo #8C1C2B at 600): the auto-generated one made 600 too light,
                // so Filament fell back to pink buttons with dark text.
                'primary' => array_map(fn ($hex) => Color::convertToOklch($hex), [
                    50 => '#fbf1f2', 100 => '#f6e2e4', 200 => '#edc5ca', 300 => '#df9aa3', 400 => '#c9606e',
                    500 => '#ab3445', 600 => '#8c1c2b', 700 => '#741723', 800 => '#5c1220', 900 => '#4a101b', 950 => '#2b080e',
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
            ->brandName('RedCherry Books Admin')
            ->brandLogo(asset('assets/img/logo.svg'))
            ->brandLogoHeight('2.75rem')
            ->favicon(asset('favicon.ico'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->pages([\App\Filament\Pages\ShippingSettings::class])
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
