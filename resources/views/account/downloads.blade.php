@extends('layouts.app')

@section('title', 'My Downloads — '.config('redcherry.store_name'))

@section('content')
<div class="container py-4">
    <h1 class="h4 mb-4">My Downloads</h1>
    <div class="row">
        <div class="col-md-3">@include('account._nav')</div>
        <div class="col-md-9">
            @if(session('downloadUrl'))
                <div class="alert alert-success">
                    Your download is ready: <a href="{{ session('downloadUrl') }}" class="alert-link">Click here to download</a> (link expires in {{ config('redcherry.ebook_download_link_minutes') }} minutes).
                </div>
            @endif

            @forelse($licenses as $license)
                <div class="d-flex align-items-center gap-3 border rounded p-3 mb-2">
                    <img src="{{ $license->variant->book->coverUrl() }}" style="width:50px;height:68px;object-fit:cover" class="rounded border">
                    <div class="flex-grow-1">
                        <div class="fw-semibold">{{ $license->variant->book->title }}</div>
                        <div class="text-muted small">{{ strtoupper($license->variant->ebook_format ?? 'PDF') }} &middot; {{ $license->remaining() }} of {{ $license->download_limit }} downloads remaining</div>
                    </div>
                    @if($license->canDownload())
                        <form method="POST" action="{{ route('ebook.link', $license) }}">
                            @csrf
                            <button class="btn btn-brand btn-sm"><i class="bi bi-download me-1"></i>Get Download Link</button>
                        </form>
                    @else
                        <span class="text-muted small">Limit reached</span>
                    @endif
                </div>
            @empty
                <p class="text-muted">You don't own any eBooks yet. <a href="{{ route('home') }}">Browse the catalog</a>.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
