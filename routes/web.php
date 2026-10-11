<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BundleController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\EbookDownloadController;
use App\Http\Controllers\GiftCardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RazorpayWebhookController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/search', [CatalogController::class, 'search'])->name('search');
Route::get('/top-100', [CatalogController::class, 'top100'])->name('catalog.top100');
Route::get('/best-sellers', [CatalogController::class, 'bestsellers'])->name('catalog.bestsellers');
Route::get('/new-releases', [CatalogController::class, 'newReleases'])->name('catalog.new-releases');
Route::get('/category/{category:slug}', [CatalogController::class, 'category'])->name('catalog.category');
Route::get('/author/{author:slug}', [CatalogController::class, 'author'])->name('catalog.author');

Route::get('/books/{book:slug}', [BookController::class, 'show'])->name('books.show');

Route::get('/bundles', [BundleController::class, 'index'])->name('bundles.index');
Route::get('/gift-cards', [GiftCardController::class, 'index'])->name('gift-cards.index');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/bundles/{bundle:slug}', [CartController::class, 'addBundle'])->name('cart.bundle.add');
Route::post('/cart/gift-card', [CartController::class, 'addGiftCard'])->name('cart.gift-card.add');
Route::patch('/cart/items/{cartItem}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/items/{cartItem}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon.apply');
Route::delete('/cart/coupon', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');

Route::post('/webhooks/razorpay', RazorpayWebhookController::class)->name('webhooks.razorpay');

// Guest auth routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::post('/books/{book:slug}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{book:slug}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::post('/checkout/razorpay/verify', [CheckoutController::class, 'verify'])->name('checkout.razorpay.verify');
    Route::get('/order/confirmation/{order:order_number}', [OrderController::class, 'confirmation'])->name('order.confirmation');

    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/', [AccountController::class, 'dashboard'])->name('dashboard');
        Route::get('/orders', [AccountController::class, 'orders'])->name('orders');
        Route::get('/orders/{order:order_number}', [AccountController::class, 'orderShow'])->name('orders.show');
        Route::get('/addresses', [AccountController::class, 'addresses'])->name('addresses');
        Route::post('/addresses', [AccountController::class, 'storeAddress'])->name('addresses.store');
        Route::delete('/addresses/{address}', [AccountController::class, 'destroyAddress'])->name('addresses.destroy');
        Route::get('/downloads', [AccountController::class, 'downloads'])->name('downloads');
        Route::get('/wallet', [AccountController::class, 'wallet'])->name('wallet');
        Route::post('/wallet/redeem', [AccountController::class, 'redeemVoucher'])->name('wallet.redeem');
    });

    Route::post('/downloads/{license}/link', [EbookDownloadController::class, 'link'])->name('ebook.link');
});

Route::get('/downloads/{license}/file', [EbookDownloadController::class, 'download'])
    ->name('ebook.download')
    ->middleware(['auth', 'signed']);
