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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Nunito+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    @stack('styles')
</head>
<body>

<div class="top-bar py-1">
    <div class="container d-flex justify-content-between align-items-center small">
        <div>Free delivery on physical orders above {{ money(config('redcherry.free_delivery_threshold') * 100) }}</div>
        <div class="d-none d-md-flex gap-3">
            @auth
                <div class="dropdown account-dropdown">
                    <a href="{{ route('account.dashboard') }}" class="dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
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
                <a href="{{ route('login') }}">Login</a>
                <a href="{{ route('register') }}">Create Account</a>
            @endauth
        </div>
    </div>
</div>

<header class="site-header py-3">
    <div class="container d-flex align-items-center gap-3">
        <a class="navbar-brand me-2" href="{{ route('home') }}">
            <img src="{{ asset('assets/img/logo.png') }}" srcset="{{ asset('assets/img/logo@2x.png') }} 2x" alt="{{ config('redcherry.store_name') }}">
        </a>

        <form action="{{ route('search') }}" method="GET" class="flex-grow-1 mx-md-4 d-none d-md-block">
            <div class="input-group">
                <input type="search" name="q" class="form-control" placeholder="Search books, authors, ISBN…" value="{{ request('q') }}">
                <button class="btn btn-brand" type="submit"><i class="bi bi-search"></i></button>
            </div>
        </form>

        <div class="ms-auto d-flex align-items-center gap-1">
            @auth
                <a href="{{ route('wishlist.index') }}" class="icon-link" title="Wishlist"><i class="bi bi-heart fs-5"></i></a>
            @else
                <a href="{{ route('login') }}" class="icon-link" title="Login for wishlist"><i class="bi bi-heart fs-5"></i></a>
            @endauth
            <a href="{{ route('cart.index') }}" class="icon-link" title="Cart">
                <i class="bi bi-bag fs-5"></i>
                <span class="badge rounded-pill bg-danger" data-cart-count @if(($cartItemCount ?? 0) === 0) hidden @endif>{{ $cartItemCount ?? 0 }}</span>
            </a>
        </div>
    </div>
    <div class="container mt-2 d-md-none">
        <form action="{{ route('search') }}" method="GET">
            <div class="input-group">
                <input type="search" name="q" class="form-control" placeholder="Search books, authors, ISBN…" value="{{ request('q') }}">
                <button class="btn btn-brand" type="submit"><i class="bi bi-search"></i></button>
            </div>
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

<nav class="category-nav">
    <div class="container d-flex align-items-center gap-2">
        <div class="nav-item dropdown flex-shrink-0">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Categories</a>
            <ul class="dropdown-menu">
                @foreach(\App\Models\Category::whereNull('parent_id')->orderBy('sort_order')->get() as $navCategory)
                    <li><a class="dropdown-item" href="{{ route('catalog.category', $navCategory) }}">{{ $navCategory->name }}</a></li>
                @endforeach
            </ul>
        </div>
        <div class="nav flex-nowrap overflow-auto">
            <a class="nav-link" href="{{ route('home') }}">Home</a>
            <a class="nav-link" href="{{ route('catalog.top100') }}">Top 100</a>
            <a class="nav-link" href="{{ route('catalog.bestsellers') }}">Best Sellers</a>
            <a class="nav-link" href="{{ route('catalog.new-releases') }}">New Releases</a>
            <a class="nav-link" href="{{ route('bundles.index') }}">Bundle Offers</a>
            <a class="nav-link" href="{{ route('gift-cards.index') }}">Gift Cards</a>
        </div>
    </div>
</nav>

<main>
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

<div class="trust-strip py-4 mt-5">
    <div class="container">
        <div class="row text-center g-3">
            <div class="col-6 col-md-3">
                <i class="bi bi-truck fs-3 text-brand"></i>
                <div class="fw-semibold small mt-1">Free Delivery</div>
                <div class="text-muted small">On orders above {{ money(config('redcherry.free_delivery_threshold') * 100) }}</div>
            </div>
            <div class="col-6 col-md-3">
                <i class="bi bi-arrow-repeat fs-3 text-brand"></i>
                <div class="fw-semibold small mt-1">Easy Returns</div>
                <div class="text-muted small">{{ config('redcherry.return_window_days') }}-day hassle-free returns</div>
            </div>
            <div class="col-6 col-md-3">
                <i class="bi bi-shield-check fs-3 text-brand"></i>
                <div class="fw-semibold small mt-1">Secure Payments</div>
                <div class="text-muted small">100% safe checkout</div>
            </div>
            <div class="col-6 col-md-3">
                <i class="bi bi-headset fs-3 text-brand"></i>
                <div class="fw-semibold small mt-1">Support</div>
                <div class="text-muted small">{{ config('redcherry.support_email') }}</div>
            </div>
        </div>
    </div>
</div>

<footer class="site-footer pt-5 pb-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <img src="{{ asset('assets/img/logo.png') }}" alt="{{ config('redcherry.store_name') }}" style="height:36px;filter:brightness(0) invert(1);">
                <p class="mt-3 small">The publishing company since 2014. Physical books and eBooks, delivered across India.</p>
            </div>
            <div class="col-6 col-md-2">
                <h6>Shop</h6>
                <ul class="list-unstyled small">
                    <li><a href="{{ route('catalog.top100') }}">Top 100</a></li>
                    <li><a href="{{ route('catalog.bestsellers') }}">Best Sellers</a></li>
                    <li><a href="{{ route('catalog.new-releases') }}">New Releases</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-2">
                <h6>Account</h6>
                <ul class="list-unstyled small">
                    @auth
                        <li><a href="{{ route('account.dashboard') }}">My Account</a></li>
                        <li><a href="{{ route('account.orders') }}">My Orders</a></li>
                        <li><a href="{{ route('account.downloads') }}">My Downloads</a></li>
                    @else
                        <li><a href="{{ route('login') }}">Login</a></li>
                        <li><a href="{{ route('register') }}">Create Account</a></li>
                    @endauth
                </ul>
            </div>
            <div class="col-md-4">
                <h6>Support</h6>
                <ul class="list-unstyled small">
                    <li><i class="bi bi-envelope me-1"></i> {{ config('redcherry.support_email') }}</li>
                    @if(config('redcherry.support_phone'))
                        <li><i class="bi bi-telephone me-1"></i> {{ config('redcherry.support_phone') }}</li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
    <div class="container mt-4 pt-3 border-top border-secondary small text-center">
        &copy; {{ now()->year }} RedCherry Books LLP. All rights reserved.
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
