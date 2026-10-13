<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/** Fills a blank slug from the title/name on save, adding -2, -3… if it is already taken. */
trait GeneratesSlug
{
    protected static function bootGeneratesSlug(): void
    {
        static::saving(function ($model) {
            if (filled($model->slug)) {
                return;
            }
            // Non-Latin titles (e.g. Malayalam-only) can slug to "", so fall back to a short random slug.
            $base = Str::slug((string) ($model->title ?? $model->name)) ?: Str::lower(Str::random(8));
            $slug = $base;
            for ($i = 2; static::where('slug', $slug)->whereKeyNot($model->getKey())->exists(); $i++) {
                $slug = "{$base}-{$i}";
            }
            $model->slug = $slug;
        });
    }
}
