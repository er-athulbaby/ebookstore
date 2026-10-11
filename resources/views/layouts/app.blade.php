<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('redcherry.store_name'))</title>
    <meta name="description" content="@yield('meta_description', 'RedCherry Books — physical and eBooks, delivered across India.')">
    <meta name="theme-color" content="#fbf8f3">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon-32.png') }}" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/apple-touch-icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Raleway:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    @stack('styles')
</head>
<body>
@php
    $navCategories = \App\Models\Category::whereNull('parent_id')
        ->whereHas('books', fn ($q) => $q->published()) // don't link to empty shelves
        ->orderBy('sort_order')->get();
@endphp

<a class="skip-link" href="#main">Skip to content</a>

<div class="top-bar">
    <div class="container">Free delivery on physical orders above {{ money(config('redcherry.free_delivery_threshold') * 100) }}</div>
</div>

<header class="site-header">
    <div class="container header-main">
        <button class="icon-link menu-toggle d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav" aria-controls="mobileNav" aria-label="Open menu">
            <i class="bi bi-list"></i>
        </button>

        <a class="brand" href="{{ route('home') }}">
            <img src="{{ asset('assets/img/logo.svg') }}" alt="{{ config('redcherry.store_name') }}" width="312" height="113">
        </a>

        <form action="{{ route('search') }}" method="GET" class="search-field d-none d-md-flex" role="search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" name="q" placeholder="Search books, authors, ISBN…" value="{{ request('q') }}" aria-label="Search books, authors, ISBN">
            <button type="submit" class="search-submit">Search</button>
        </form>

        <div class="header-actions">
            @auth
                <div class="dropdown account-dropdown">
                    <a href="{{ route('account.dashboard') }}" class="icon-link account-link" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Account menu">
                        <i class="bi bi-person"></i><span class="d-none d-xl-inline">{{ \Illuminate\Support\Str::limit(auth()->user()->name, 14) }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="dropdown-header">{{ auth()->user()->name }}</li>
                        <li><a class="dropdown-item" href="{{ route('account.dashboard') }}">Dashboard</a></li>
                        <li><a class="dropdown-item" href="{{ route('account.downloads') }}">My Downloads</a></li>
                        <li><a class="dropdown-item" href="{{ route('account.wallet') }}">Wallet</a></li>
                        <li><a class="dropdown-item" href="{{ route('wishlist.index') }}">Wishlist</a></li>
                        <li><a class="dropdown-item" href="{{ route('account.addresses') }}">Addresses</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
                <a href="{{ route('wishlist.index') }}" class="icon-link d-none d-sm-inline-flex" title="Wishlist" aria-label="Wishlist"><i class="bi bi-heart"></i></a>
            @else
                <a href="{{ route('login') }}" class="icon-link account-link" aria-label="Login"><i class="bi bi-person"></i><span class="d-none d-xl-inline">Login</span></a>
                <a href="{{ route('login') }}" class="icon-link d-none d-sm-inline-flex" title="Login for wishlist" aria-label="Login for wishlist"><i class="bi bi-heart"></i></a>
            @endauth
            <a href="{{ route('cart.index') }}" class="icon-link" title="Cart" aria-label="Cart">
                <i class="bi bi-bag"></i>
                <span class="badge rounded-pill" data-cart-count @if(($cartItemCount ?? 0) === 0) hidden @endif>{{ $cartItemCount ?? 0 }}</span>
            </a>
        </div>
    </div>

    <div class="container d-md-none mobile-search">
        <form action="{{ route('search') }}" method="GET" class="search-field" role="search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" name="q" placeholder="Search books, authors, ISBN…" value="{{ request('q') }}" aria-label="Search books, authors, ISBN">
        </form>
    </div>

    <nav class="primary-nav d-none d-lg-block" aria-label="Main">
        <div class="container primary-nav-inner">
            <div class="dropdown categories-menu">
                <button class="nav-link categories-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-grid-3x3-gap"></i> Categories <i class="bi bi-chevron-down small"></i>
                </button>
                <div class="dropdown-menu categories-panel">
                    <div class="categories-panel-head">Browse by category</div>
                    <ul class="categories-list">
                        @foreach($navCategories as $navCategory)
                            <li><a class="dropdown-item" href="{{ route('catalog.category', $navCategory) }}">{{ $navCategory->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <span class="nav-divider" aria-hidden="true"></span>
            <a class="nav-link @if(request()->routeIs('home')) active @endif" href="{{ route('home') }}">Home</a>
            <a class="nav-link @if(request()->routeIs('catalog.top100')) active @endif" href="{{ route('catalog.top100') }}">Top 100</a>
            <a class="nav-link @if(request()->routeIs('catalog.bestsellers')) active @endif" href="{{ route('catalog.bestsellers') }}">Best Sellers</a>
            <a class="nav-link @if(request()->routeIs('catalog.new-releases')) active @endif" href="{{ route('catalog.new-releases') }}">New Releases</a>
            <a class="nav-link @if(request()->routeIs('bundles.*')) active @endif" href="{{ route('bundles.index') }}">Bundle Offers</a>
            <a class="nav-link @if(request()->routeIs('gift-cards.*')) active @endif" href="{{ route('gift-cards.index') }}">Gift Cards</a>
        </div>
    </nav>
</header>

<div class="offcanvas offcanvas-start mobile-nav" tabindex="-1" id="mobileNav" aria-labelledby="mobileNavLabel">
    <div class="offcanvas-header">
        <a href="{{ route('home') }}" class="brand" id="mobileNavLabel">
            <img src="{{ asset('assets/img/logo.svg') }}" alt="{{ config('redcherry.store_name') }}" width="312" height="113">
        </a>
        <button type="button" class="icon-link" data-bs-dismiss="offcanvas" aria-label="Close menu"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="offcanvas-body">
        <nav class="mobile-links" aria-label="Main">
            <a href="{{ route('home') }}" @if(request()->routeIs('home')) class="active" @endif>Home</a>
            <a href="{{ route('catalog.top100') }}" @if(request()->routeIs('catalog.top100')) class="active" @endif>Top 100</a>
            <a href="{{ route('catalog.bestsellers') }}" @if(request()->routeIs('catalog.bestsellers')) class="active" @endif>Best Sellers</a>
            <a href="{{ route('catalog.new-releases') }}" @if(request()->routeIs('catalog.new-releases')) class="active" @endif>New Releases</a>
            <a href="{{ route('bundles.index') }}" @if(request()->routeIs('bundles.*')) class="active" @endif>Bundle Offers</a>
            <a href="{{ route('gift-cards.index') }}" @if(request()->routeIs('gift-cards.*')) class="active" @endif>Gift Cards</a>
        </nav>
        @if($navCategories->isNotEmpty())
            <div class="mobile-heading">Categories</div>
            <nav class="mobile-categories" aria-label="Categories">
                @foreach($navCategories as $navCategory)
                    <a href="{{ route('catalog.category', $navCategory) }}">{{ $navCategory->name }}</a>
                @endforeach
            </nav>
        @endif
        <div class="mobile-heading">Account</div>
        <nav class="mobile-links small-links" aria-label="Account">
            @auth
                <a href="{{ route('account.dashboard') }}">Dashboard</a>
                <a href="{{ route('account.orders') }}">My Orders</a>
                <a href="{{ route('account.downloads') }}">My Downloads</a>
                <a href="{{ route('account.wallet') }}">Wallet</a>
                <a href="{{ route('wishlist.index') }}">Wishlist</a>
            @else
                <a href="{{ route('login') }}">Login</a>
                <a href="{{ route('register') }}">Create Account</a>
            @endauth
        </nav>
    </div>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:1080">
    <div id="cartToast" class="toast align-items-center text-bg-dark" role="alert">
        <div class="d-flex">
            <div class="toast-body"><i class="bi bi-check-circle-fill text-success me-1"></i> Added to your cart.</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<main id="main">
    @if(session('status'))
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif
    @if($errors->any())
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @yield('content')
</main>

<section class="trust-strip" aria-label="Our promises">
    <div class="container trust-grid">
        <div class="trust-item">
            <span class="trust-icon"><i class="bi bi-truck"></i></span>
            <div><b>Free Delivery</b><span>On orders above {{ money(config('redcherry.free_delivery_threshold') * 100) }}</span></div>
        </div>
        <div class="trust-item">
            <span class="trust-icon"><i class="bi bi-arrow-repeat"></i></span>
            <div><b>Easy Returns</b><span>{{ config('redcherry.return_window_days') }}-day hassle-free returns</span></div>
        </div>
        <div class="trust-item">
            <span class="trust-icon"><i class="bi bi-shield-check"></i></span>
            <div><b>Secure Payments</b><span>100% safe checkout</span></div>
        </div>
        <div class="trust-item">
            <span class="trust-icon"><i class="bi bi-headset"></i></span>
            <div><b>Support</b><span>{{ config('redcherry.support_email') }}</span></div>
        </div>
    </div>
</section>

<footer class="site-footer">
    <div class="container footer-top">
        <div class="footer-brand">
            <a href="{{ route('home') }}"><img src="{{ asset('assets/img/logo.svg') }}" alt="{{ config('redcherry.store_name') }}" width="312" height="113"></a>
            <p>The publishing company since 2014. Physical books and eBooks, delivered across India.</p>
        </div>
        <div class="footer-col">
            <h6>Shop</h6>
            <a href="{{ route('catalog.top100') }}">Top 100</a>
            <a href="{{ route('catalog.bestsellers') }}">Best Sellers</a>
            <a href="{{ route('catalog.new-releases') }}">New Releases</a>
            <a href="{{ route('bundles.index') }}">Bundle Offers</a>
            <a href="{{ route('gift-cards.index') }}">Gift Cards</a>
        </div>
        <div class="footer-col">
            <h6>Account</h6>
            @auth
                <a href="{{ route('account.dashboard') }}">My Account</a>
                <a href="{{ route('account.orders') }}">My Orders</a>
                <a href="{{ route('account.downloads') }}">My Downloads</a>
            @else
                <a href="{{ route('login') }}">Login</a>
                <a href="{{ route('register') }}">Create Account</a>
            @endauth
        </div>
        <div class="footer-col">
            <h6>Support</h6>
            <span><i class="bi bi-envelope"></i> {{ config('redcherry.support_email') }}</span>
            @if(config('redcherry.support_phone'))
                <span><i class="bi bi-telephone"></i> {{ config('redcherry.support_phone') }}</span>
            @endif
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">&copy; {{ now()->year }} RedCherry Books LLP. All rights reserved.</div>
    </div>
</footer>

@if(config('redcherry.support_whatsapp'))
    <a class="whatsapp-fab" target="_blank" rel="noopener" aria-label="Chat with us on WhatsApp"
       href="https://api.whatsapp.com/send?phone={{ config('redcherry.support_whatsapp') }}&text={{ urlencode('I need help with a book purchase') }}">
        <i class="bi bi-whatsapp"></i>
    </a>
@endif

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('assets/js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
