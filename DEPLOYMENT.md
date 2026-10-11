# RedCherry Books — Setup & Deployment

This is a Laravel 13 app. No Node.js/npm anywhere in this project — the storefront
is Blade + Bootstrap 5 loaded from a CDN, and the admin panel (Filament) ships
its own precompiled assets. You only ever need PHP + Composer + MySQL.

## 1. Local setup on Windows

You said you weren't sure what's installed. Easiest path: **Laravel Herd for
Windows** — one installer gives you PHP, Composer, and a local web server,
with MySQL available as a paid add-on (Herd Pro) or you can point it at any
MySQL you already have (your `mysql-portable` folder suggests you might).

1. Install Herd: https://herd.laravel.com/windows
2. Open a terminal in this project folder and run:
   ```
   composer install
   copy .env.example .env
   php artisan key:generate
   ```
3. Point `.env` at your database. Two options:
   - **MySQL** (recommended, matches production): create an empty database
     called `redcherry`, then set `DB_CONNECTION=mysql` and the
     `DB_HOST`/`DB_USERNAME`/`DB_PASSWORD` lines in `.env` to match.
   - **SQLite** (zero setup, fine for local dev only): comment out the
     `DB_HOST`..`DB_PASSWORD` lines, set `DB_CONNECTION=sqlite`, then run
     `type nul > database\database.sqlite` (or just create an empty file
     at that path).
4. Run migrations + seed sample data:
   ```
   php artisan migrate --seed
   ```
   This creates an admin login: **admin@redcherrybooks.example / password**
   — change this immediately (see step 6).
5. Install the admin panel's own assets (no npm involved — this is a plain
   artisan command):
   ```
   php artisan filament:install --panels
   php artisan storage:link
   ```
6. **Change the seeded admin password right away**: `php artisan tinker`
   then:
   ```php
   $u = App\Models\User::where('email', 'admin@redcherrybooks.example')->first();
   $u->update(['password' => Hash::make('a-real-password'), 'email' => 'you@yourdomain.com']);
   ```
7. Serve it: `php artisan serve` → http://localhost:8000. Admin panel is at
   `/admin`.

## 2. Razorpay

1. Sign up at https://dashboard.razorpay.com and grab your **test mode**
   Key ID / Key Secret from Settings → API Keys.
2. Put them in `.env`: `RAZORPAY_KEY`, `RAZORPAY_SECRET`.
3. For the webhook (Settings → Webhooks → Add New Webhook), point it at
   `https://yourdomain.com/webhooks/razorpay`, subscribe to
   `payment.captured` and `payment.failed`, and copy the webhook secret
   into `RAZORPAY_WEBHOOK_SECRET`. This is a belt-and-braces path — normal
   checkouts are confirmed instantly client-side — but it's what saves an
   order if the customer closes the tab right after paying.
4. Razorpay test cards/UPI: https://razorpay.com/docs/payments/payments/test-card-upi-details/
5. Switch to live keys only once you've tested the full flow end to end.

## 3. Uploading real books

Everything in the seeded catalog is placeholder data — invented titles so
you have something to click through, **not** a copy of any real publisher's
catalog. Replace it via `/admin`:

- **Categories / Authors** — straightforward CRUD.
- **Books** — set title, description, ISBN, etc.
- **Variants** (paperback / hardcover / ebook) — price, MRP (for the
  strike-through %off badge), stock (physical) or the actual PDF/EPUB file
  (ebook). Ebook files upload to a **private** disk
  (`storage/app/private/ebooks`) that's never web-accessible directly —
  they're only ever served through the signed, purchase-gated download
  route. Don't put ebook files in `public/`.
- **Images** — cover art per book.

You can also just delete the seed data (`php artisan db:wipe && php artisan migrate --seed=false`
then re-seed only `AdminUserSeeder`) and start from a clean catalog.

## 4. Shared hosting (cPanel) deployment

### Prerequisites — confirm with your host

- PHP 8.2, 8.3, 8.4 or 8.5 available (cPanel → **MultiPHP Manager**)
- Composer available (cPanel → **Terminal**, or ask your host to enable
  SSH — many budget hosts don't expose Composer by default; if truly none
  is available, run `composer install --no-dev -o` locally and upload the
  resulting `vendor/` folder via FTP instead)
- A MySQL database + user (cPanel → **MySQL Databases**)
- Cron access (cPanel → **Cron Jobs**) — optional for v1 (see §6), but
  worth confirming it's there for later

### Where the code goes

Laravel's web root is `public/`, but cPanel usually points your domain at
`public_html/` directly. Two ways to reconcile that — pick whichever your
host supports:

**Option A — your host lets you set a custom document root** (check
cPanel → Domains → "Document Root", some hosts allow this): upload the
whole project to e.g. `/home/youruser/redcherry-app/` and point the
domain's document root at `/home/youruser/redcherry-app/public`. Nothing
else to do — this is the clean, standard Laravel deployment layout.

**Option B — document root is locked to `public_html/`** (the common
case): upload the project *outside* `public_html`, e.g. to
`/home/youruser/redcherry-app/`, then:

1. Copy everything **inside** `redcherry-app/public/` into `public_html/`
   (the files, not the folder itself — `index.php`, `.htaccess`,
   `favicon.ico`, `assets/`, etc.)
2. Edit the `index.php` you just copied into `public_html/` — change the
   two `require` paths to point one level up into `redcherry-app/`:
   ```php
   $app = require_once __DIR__.'/../redcherry-app/bootstrap/app.php';
   // and
   require __DIR__.'/../redcherry-app/vendor/autoload.php';
   ```
   (adjust the relative path to match wherever you actually put
   `redcherry-app/` relative to `public_html/`)
3. Any time you update `public/assets/*` in the app, re-copy those files
   into `public_html/` too — they're now two physical locations.

### Deploy steps (either option)

```
composer install --no-dev --optimize-autoloader
cp .env.example .env      # then fill in real DB/Razorpay/mail values
php artisan key:generate
php artisan migrate --force
php artisan db:seed --class=AdminUserSeeder --force   # NOT the demo catalog — see below
php artisan filament:install --panels
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Don't run the full `--seed` (with placeholder catalog) in production —
just `AdminUserSeeder`, then add your real catalog through `/admin`.

Set in `.env` for production:
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
```
and make sure the site is served over HTTPS (Let's Encrypt is free via
cPanel → SSL/TLS Status → AutoSSL) — Razorpay requires HTTPS in live mode,
and `AppServiceProvider` force-upgrades URLs to `https://` outside local
env, so mixed content will otherwise break asset/link generation.

### File permissions

`storage/` and `bootstrap/cache/` must be writable by the web server user
(typically already true after upload via cPanel File Manager; if using
SSH, `chmod -R 775 storage bootstrap/cache`).

## 4b. Hostinger specifics

Hostinger uses its own panel (**hPanel**), not cPanel, but the same steps
in §4 apply — just under different menu names:

- **PHP version**: hPanel → Websites → your domain → **Advanced → PHP
  Configuration** → select 8.2, 8.3 or 8.4. Check the extension list
  there too — `pdo_mysql`, `mbstring`, `curl`, `fileinfo`, `openssl`,
  `bcmath`, `zip`, `gd` should all be on (Hostinger enables these by
  default, but worth confirming).
- **SSH access**: hPanel → Advanced → **SSH Access** — available on
  Premium/Business/Cloud plans, not the cheapest shared tier. This is
  what lets you run `composer install` and `artisan` commands directly
  on the server; without it you're stuck uploading a pre-built `vendor/`
  folder from your machine (§4 Prerequisites already covers that
  fallback).
- **MySQL database**: hPanel → Databases → **MySQL Databases** — create
  the DB and a user, then attach the user to the DB. `DB_HOST` is
  normally `localhost` on Hostinger shared/cloud hosting.
- **Document root**: whether Hostinger lets you point a domain straight
  at a `public/` subfolder (Option A above) varies by plan — look for a
  document-root / website-root field under the domain's settings in
  hPanel. If you don't see one, use Option B (copy `public/`'s contents
  into `public_html/` and adjust `index.php`'s two `require` paths).
- **SSL**: hPanel → Websites → your domain → **SSL** — free Let's Encrypt
  SSL, usually one click. Turn it on before going live with Razorpay
  (live mode requires HTTPS).
- **Cron**: hPanel → Advanced → **Cron Jobs**, same entry as §6.
- **Git-based deploys**: if you'd rather not upload files by hand each
  time, hPanel → Websites → your domain → **Git** can pull from a GitHub/
  GitLab/Bitbucket repo on each deploy — but this project isn't in a git
  repo yet, so that path needs pushing it to a remote first.

Exact menu labels shift between Hostinger UI versions — if something's
named slightly differently in your account, look for the equivalent
section rather than the exact words above.

## 5. Ongoing changes

After editing code on the server (or re-uploading), clear cached config:
```
php artisan config:clear && php artisan config:cache
```
Migrations for future schema changes: `php artisan migrate --force`.

## 6. Cron (optional for v1, needed later)

Nothing in v1 strictly requires cron — order emails send synchronously at
checkout, not via a queue worker. If you later add scheduled tasks
(abandoned-cart emails, report generation, etc.), add this cron entry in
cPanel → Cron Jobs, running every minute:
```
* * * * * cd /home/youruser/redcherry-app && php artisan schedule:run >> /dev/null 2>&1
```

## 7. What's deliberately not in v1

Cut for scope, not forgotten — the schema and code don't need rework to
add these later:
- "Rush Hours" flash-deal countdowns
- Automated test suite (given the scope of this build, this was traded
  for coverage breadth; add PHPUnit feature tests for checkout and the
  ebook download gate before you rely on this in production)
- Queued email (currently synchronous — fine at small volume, revisit if
  checkout starts feeling slow under load)

Bundle offers and gift vouchers/wallet **are** implemented — see the
"Bundles" and "Gift Vouchers" admin resources and the storefront's
Bundle Offers / Gift Cards pages.
