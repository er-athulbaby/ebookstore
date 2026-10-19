<?php

namespace App\Filament\Concerns;

/**
 * Hides a resource/page from the menu and blocks its URLs unless the signed-in admin
 * has been given its section. Each class sets: protected static string $adminSection = '…';
 */
trait RequiresAdminSection
{
    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->canAccessSection(static::$adminSection);
    }
}
