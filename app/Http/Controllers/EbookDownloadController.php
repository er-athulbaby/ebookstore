<?php

namespace App\Http\Controllers;

use App\Models\EbookLicense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class EbookDownloadController extends Controller
{
    /** Generates a short-lived signed URL for the actual download — never exposes the file path directly. */
    public function link(EbookLicense $license)
    {
        abort_unless($license->user_id === Auth::id(), 403);
        abort_unless($license->canDownload(), 403, 'You\'ve used all your downloads for this title. Contact support if you need another copy.');

        $minutes = config('redcherry.ebook_download_link_minutes', 30);

        $url = URL::temporarySignedRoute('ebook.download', now()->addMinutes($minutes), ['license' => $license->id]);

        return back()->with('downloadUrl', $url);
    }

    /** The actual file stream, gated by the signed URL AND a live re-check of the license (defense in depth). */
    public function download(Request $request, EbookLicense $license)
    {
        abort_unless($request->hasValidSignature(), 403, 'This download link has expired. Generate a new one from My Downloads.');
        abort_unless($license->user_id === Auth::id(), 403);
        abort_unless($license->canDownload(), 403, 'Download limit reached for this title.');

        $variant = $license->variant;
        abort_unless($variant && $variant->ebook_file_path && Storage::disk('ebooks')->exists($variant->ebook_file_path), 404, 'File not found.');

        $license->increment('downloads_used');
        $license->logs()->create([
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'downloaded_at' => now(),
        ]);

        $filename = \Illuminate\Support\Str::slug($variant->book->title).'.'.($variant->ebook_format ?? 'pdf');

        return Storage::disk('ebooks')->download($variant->ebook_file_path, $filename);
    }
}
