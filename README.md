# Aurora Grand Hotel & Spa — demo hotel website

A complete hotel website with online booking and an admin panel, built with **Laravel 13**, **Livewire 4**, **Filament 5** and **Tailwind CSS 4**. The hotel, its content and all data are fictional; this project is a portfolio demo.

## Features

**Public website (EN / RU, light & dark theme, responsive)**
Home with availability search, rooms & suites with live filters and pricing, room pages with gallery and live price widget, experiences, special offers with promo codes, gallery with lightbox, about, contact form with map, FAQ, guest reviews, blog, privacy/terms, SEO (meta tags, JSON-LD, sitemap.xml, robots.txt).

**Booking engine**
4-step wizard: dates & room → extras → guest details → payment. Live availability per room, seasonal and weekend pricing, minimum stay rules, extras (per stay / night / guest-night), promo codes, taxes, demo card payment (Stripe-like form) or pay at hotel, confirmation email with PDF invoice, add-to-calendar, manage booking by reference + email, free cancellation policy.

**Guest account**
Registration/login, upcoming and past stays, cancel, pay balance, PDF invoices, reviews after checkout, profile.

**Admin panel (`/admin`, Filament)**
Dashboard (occupancy, ADR, RevPAR, revenue charts, arrivals/departures), tape chart (rooms × days), bookings with check-in/out, payments and refunds, room types, rooms & housekeeping, seasons, promo codes, extras, guests, reviews moderation, blog, offers, facilities, gallery, FAQ, messages, newsletter subscribers (CSV export), site settings, staff roles (admin / manager / reception).

## Demo accounts (password: `password`)

| Role | Email |
|---|---|
| Admin | admin@demo.com |
| Manager | manager@demo.com |
| Reception | reception@demo.com |
| Guest | guest@demo.com |

Test cards: `4242 4242 4242 4242` (success), `4000 0000 0000 0002` (declined). Promo codes: `WELCOME10`, `AURORA20` (3+ nights), `SPRING50`.

## Local setup

```bash
composer install && npm install && npm run build
cp .env.example .env && php artisan key:generate
touch database/database.sqlite && php artisan migrate --seed
php artisan serve
```

## Deploying to Vercel

`vercel.json` + `api/index.php` run Laravel on the `vercel-php` runtime. Set `APP_KEY` in the project's environment variables.

- **Without a database add-on**, each serverless instance starts from the bundled snapshot `database/demo.sqlite` (dates are shifted to today on boot). Changes live only in that instance, which is fine for a demo.
- **With Postgres** (e.g. Neon from the Vercel Marketplace, which sets `POSTGRES_URL`/`DATABASE_URL`), data persists; the database is migrated and seeded automatically on first request. Set `CRON_SECRET` to let the daily cron (`/demo/reset`) restore fresh demo data.

Regenerate the snapshot after changing the seeder:

```bash
rm -f database/demo.sqlite && touch database/demo.sqlite
DB_DATABASE=$PWD/database/demo.sqlite php artisan migrate --seed --force
```
