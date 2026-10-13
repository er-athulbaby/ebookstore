<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;

/**
 * Store-wide settings edited in Admin → Settings → Site Settings.
 * Anything left blank falls back to the .env / config value, so a fresh install still works.
 */
class SiteSettings
{
    public static function logoUrl(): string
    {
        $path = Setting::get('logo_path');

        return $path ? asset('storage/'.$path) : asset('assets/img/logo.svg');
    }

    public static function faviconUrl(): string
    {
        $path = Setting::get('favicon_path');

        return $path ? asset('storage/'.$path) : asset('favicon.ico');
    }

    public static function tagline(): string
    {
        return (string) Setting::get('tagline', 'Physical Books & eBooks, Delivered Across India');
    }

    public static function metaDescription(): string
    {
        return (string) Setting::get('meta_description', 'RedCherry Books — physical and eBooks, delivered across India.');
    }

    /** Admin-pasted tracking snippets (Google tag, Meta Pixel…), output unescaped on store pages only. */
    public static function headCode(): string
    {
        return (string) Setting::get('head_code', '');
    }

    public static function bodyCode(): string
    {
        return (string) Setting::get('body_code', '');
    }

    /** Called on every request: store name and SMTP from the admin override .env when filled in. */
    public static function applyToConfig(): void
    {
        if ($name = Setting::get('store_name')) {
            config(['redcherry.store_name' => $name]);
        }

        if ($host = Setting::get('smtp_host')) {
            $password = Setting::get('smtp_password');
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $host,
                'mail.mailers.smtp.port' => (int) Setting::get('smtp_port', 587),
                // 'ssl' = implicit TLS (usually port 465); 'tls' (587) upgrades via STARTTLS automatically.
                'mail.mailers.smtp.scheme' => (Setting::get('smtp_encryption') ?? ((int) Setting::get('smtp_port') === 465 ? 'ssl' : 'tls')) === 'ssl' ? 'smtps' : 'smtp',
                'mail.mailers.smtp.username' => Setting::get('smtp_username'),
                'mail.mailers.smtp.password' => $password ? Crypt::decryptString($password) : null,
            ]);
        }

        if ($from = Setting::get('mail_from_address')) {
            config(['mail.from.address' => $from]);
        }
        if ($fromName = Setting::get('mail_from_name')) {
            config(['mail.from.name' => $fromName]);
        }
    }
}
