<?php

namespace App\Http\Controllers;

use App\Models\Bundle;

class BundleController extends Controller
{
    public function index()
    {
        $bundles = Bundle::where('is_active', true)
            ->with('variants.book.images')
            ->orderByDesc('created_at')
            ->get()
            ->filter(fn (Bundle $bundle) => $bundle->isAvailable());

        return view('bundles.index', compact('bundles'));
    }
}
