<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('redcherry.store_name'))</title>
    <meta name="description" content="@yield('meta_description', 'RedCherry Books — physical and eBooks, delivered across India.')">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon-32.png') }}" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/apple-touch-icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Geist:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    @stack('styles')
</head>
<body>

<a class="skip-link" href="#main">Skip to content</a>

<div class="top-bar">
    <div class="container">Free delivery on physical orders above {{ money(config('redcherry.free_delivery_threshold') * 100) }} &middot; {{ config('redcherry.return_window_days') }}-day returns</div>
</div>

<header class="site-header">
    <div class="container header-row">
        <a class="brand" href="{{ route('home') }}">
            <img src="{{ asset('assets/img/logo.png') }}" srcset="{{ asset('assets/img/logo@2x.png') }} 2x" alt="{{ config('redcherry.store_name') }}">
        </a>

        <nav class="main-nav" aria-label="Main">
            <div class="dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Categories</a>
                <ul class="dropdown-menu">
                    @foreach(\App\Models\Category::whereNull('parent_id')->orderBy('sort_order')->get() as $navCategory)
                        <li><a class="dropdown-item" href="{{ route('catalog.category', $navCategory) }}">{{ $navCategory->name }}</a></li>
                    @endforeach
                </ul>
            </div>
            <a class="nav-link @if(request()->routeIs('catalog.top100')) active @endif" href="{{ route('catalog.top100') }}">Top 100</a>
            <a class="nav-link @if(request()->routeIs('catalog.bestsellers')) active @endif" href="{{ route('catalog.bestsellers') }}">Best Sellers</a>
            <a class="nav-link @if(request()->routeIs('catalog.new-releases')) active @endif" href="{{ route('catalog.new-releases') }}">New Releases</a>
            <a class="nav-link @if(request()->routeIs('bundles.*')) active @endif" href="{{ route('bundles.index') }}">Bundles</a>
            <a class="nav-link @if(request()->routeIs('gift-cards.*')) active @endif" href="{{ route('gift-cards.index') }}">Gift Cards</a>
        </nav>

        <div class="header-actions">
            <form action="{{ route('search') }}" method="GET" class="search-pill d-none d-md-flex" role="search">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="search" name="q" placeholder="Search books, authors, ISBN" value="{{ request('q') }}" aria-label="Search">
            </form>
            @auth
                <a href="{{ route('wishlist.index') }}" class="icon-link" title="Wishlist" aria-label="Wishlist"><i class="bi bi-heart"></i></a>
                <div class="dropdown account-dropdown">
                    <a href="{{ route('account.dashboard') }}" class="icon-link" data-bs-toggle="dropdown" aria-label="Account" title="{{ auth()->user()->name }}"><i class="bi bi-person"></i></a>
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
            @else
                <a href="{{ route('login') }}" class="icon-link" title="Login" aria-label="Login"><i class="bi bi-person"></i></a>
            @endauth
            <a href="{{ route('cart.index') }}" class="icon-link" title="Cart" aria-label="Cart">
                <i class="bi bi-bag"></i>
                <span class="badge rounded-pill" data-cart-count @if(($cartItemCount ?? 0) === 0) hidden @endif>{{ $cartItemCount ?? 0 }}</span>
            </a>
        </div>
    </div>
    <div class="container mobile-row d-md-none">
        <form action="{{ route('search') }}" method="GET" class="search-pill" role="search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" name="q" placeholder="Search books, authors, ISBN" value="{{ request('q') }}" aria-label="Search">
        </form>
    </div>
</header>

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

<section class="trust-strip">
    <div class="container trust-grid">
        <div><i class="bi bi-truck"></i><b>Free delivery</b><span>On orders above {{ money(config('redcherry.free_delivery_threshold') * 100) }}</span></div>
        <div><i class="bi bi-arrow-counterclockwise"></i><b>Easy returns</b><span>{{ config('redcherry.return_window_days') }}-day returns on print books</span></div>
        <div><i class="bi bi-lock"></i><b>Secure checkout</b><span>Razorpay, wallet or cash on delivery</span></div>
        <div><i class="bi bi-envelope"></i><b>Talk to us</b><span>{{ config('redcherry.support_email') }}</span></div>
    </div>
</section>

<footer class="site-footer">
    <div class="container">
        <div class="footer-top">
            <div class="footer-brand">
                <img src="{{ asset('assets/img/logo.png') }}" alt="{{ config('redcherry.store_name') }}">
                <p>Publishing and selling books since 2014. Print and eBooks, delivered across India.</p>
            </div>
            <div class="footer-links">
                <div>
                    <h6>Shop</h6>
                    <a href="{{ route('catalog.top100') }}">Top 100</a>
                    <a href="{{ route('catalog.bestsellers') }}">Best Sellers</a>
                    <a href="{{ route('catalog.new-releases') }}">New Releases</a>
                    <a href="{{ route('bundles.index') }}">Bundles</a>
                    <a href="{{ route('gift-cards.index') }}">Gift Cards</a>
                </div>
                <div>
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
                <div>
                    <h6>Support</h6>
                    <a href="mailto:{{ config('redcherry.support_email') }}">{{ config('redcherry.support_email') }}</a>
                    @if(config('redcherry.support_phone'))
                        <a href="tel:{{ config('redcherry.support_phone') }}">{{ config('redcherry.support_phone') }}</a>
                    @endif
                </div>
            </div>
        </div>
        <div class="footer-bottom">&copy; {{ now()->year }} RedCherry Books LLP</div>
    </div>
</footer>

@if(config('redcherry.support_whatsapp'))
    <a class="whatsapp-fab" target="_blank" rel="noopener"
       href="https://api.whatsapp.com/send?phone={{ config('redcherry.support_whatsapp') }}&text={{ urlencode('I need help with a book purchase') }}">
        <i class="bi bi-whatsapp"></i>
    </a>
@endif

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('assets/js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
