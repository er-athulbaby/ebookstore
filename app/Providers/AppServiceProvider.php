<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\App\Services\CartService::class);
    }

    public function boot(): void
    {
        if (! $this->app->environment('local')) {
            URL::forceScheme('https');
        }

        // Hosted in a subfolder (APP_URL=https://site/demo): Livewire posts to /livewire/update via the root
        // .htaccess rewrite, which hides the /demo base path, so redirects after saving lost it and 404'd.
        if (parse_url((string) config('app.url'), PHP_URL_PATH)) {
            URL::forceRootUrl(config('app.url'));
        }
    }
}
