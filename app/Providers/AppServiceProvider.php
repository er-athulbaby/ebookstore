<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\App\Services\CartService::class);

        // Livewire file uploads in a subfolder install (/demo): see App\Support\SubfolderSignedUploadUrl.
        $this->app->bind(
            \Livewire\Features\SupportFileUploads\GenerateSignedUploadUrl::class,
            \App\Support\SubfolderSignedUploadUrl::class,
        );
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

        try {
            \App\Support\SiteSettings::applyToConfig();
        } catch (\Throwable) {
            // No database yet (fresh install, before migrate) — keep the .env values.
        }
    }
}
