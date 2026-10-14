<?php

namespace App\Support;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\GenerateSignedUploadUrl;

/**
 * Livewire upload/preview links for a site installed in a subfolder (APP_URL=https://site/demo).
 *
 * Livewire's own version signs a path made relative to the *current request*. Admin saves arrive
 * via the root /livewire/* rewrite, where Laravel can't see the /demo base path, so the link came
 * out as /demo/demo/livewire/upload-file (404) with a signature for the wrong path. Here the path
 * is taken relative to the configured site root and signed exactly as Laravel's
 * hasValidRelativeSignature() checks it: HMAC of "/livewire/upload-file?expires=…" with app.key.
 */
class SubfolderSignedUploadUrl extends GenerateSignedUploadUrl
{
    public function signedRoute($name, $expiration, $parameters = [])
    {
        $root = rtrim(URL::to('/'), '/');
        $absolute = route($name, $parameters + ['expires' => $expiration->getTimestamp()]);
        $relative = '/'.ltrim(Str::after($absolute, $root), '/');

        $signature = hash_hmac('sha256', $relative, config('app.key'));

        return $root.$relative.'&signature='.$signature;
    }
}
