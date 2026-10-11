# RedCherry Books — Online Bookstore

A Laravel e-bookstore for RedCherry Books LLP, selling both physical books
(paperback/hardcover, shipped) and eBooks (PDF/EPUB, instant secure
download) from a single catalog. Built to mirror the feature set of the
[DC Books](https://dcbookstore.com) reference site: category/author
browsing, filters & sort, Top 100 / Best Sellers / New Releases rails,
reviews, wishlist, cart, and checkout.

**Stack**: Laravel 13, MySQL, Filament 4 (admin panel), Blade + Bootstrap 5
(CDN, no build step), Razorpay (+ Cash on Delivery for physical orders).
**No Node.js/npm anywhere** — everything ships as plain PHP/Blade/CSS/JS.

## Quick start

See **[DEPLOYMENT.md](DEPLOYMENT.md)** for full local setup (Windows/Herd
included) and shared-hosting (cPanel) deployment instructions.

```
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan filament:install --panels
php artisan storage:link
php artisan serve
```

Storefront: http://localhost:8000 — Admin panel: http://localhost:8000/admin
(seeded login: `admin@redcherrybooks.example` / `password` — **change this
immediately**, see DEPLOYMENT.md §1.6).

## How pricing/money is stored

All prices are integer **paise** (INR minor unit) in the database and in
every cart/order calculation — never floats — to avoid rounding drift.
Formatting to a display string (`₹432`) happens only at the view layer via
the `money()` helper in `app/Support/helpers.php`.

## Physical + digital variants

A `Book` can have 1–3 `BookVariant`s (`paperback`, `hardcover`, `ebook`),
each with its own price, stock (physical) or file (ebook). Cart, checkout
and orders operate on variants, not books — an order can freely mix
physical and digital items, and `Order::fulfillment_type` records which.

## eBook delivery security

Ebook files live on a **private** filesystem disk (`storage/app/private/ebooks`),
never inside `public/`. Purchasing one grants an `EbookLicense` (download
limit + counter). Downloads only ever happen through a Laravel **signed,
time-limited URL** (`EbookDownloadController`), which re-checks the license
on every hit before streaming the file — the file path itself is never
exposed to the client.

## Payments

Razorpay handles cards/UPI/netbanking; Cash on Delivery is available for
physical-only orders (blocked automatically if the cart contains an
ebook, since digital delivery is instant and needs payment upfront).
Order fulfillment (stock decrement, ebook license grant, confirmation
email) happens in `OrderService::markPaid()`, which is idempotent and
called from both the client-side payment-verify endpoint and the Razorpay
webhook — so a customer closing their browser right after paying still
gets fulfilled via the webhook.

## What's not in v1

See DEPLOYMENT.md §7 — bundle offers, emailed gift vouchers, flash deals,
and an automated test suite were cut for scope. The schema doesn't need
rework to add the first three later.
