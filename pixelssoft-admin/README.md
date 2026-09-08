# PixelsSoft Admin (Laravel)

Laravel admin panel + REST API for the PixelsSoft website.

## Local Setup

```bash
cd pixelssoft-admin
composer install --no-dev
cp .env.example.local .env
php artisan key:generate
touch database/database.sqlite   # or configure MySQL in .env
php artisan migrate --seed
php artisan serve
```

**Admin URL:** http://localhost:8000/login  
**Default login:** `admin@pixelssoft.com` / `password`

**API base:** http://localhost:8000/api/v1

## Website Connection

The Next.js frontend reads content from this API. Set these in the project root `.env.local`:

```
NEXT_PUBLIC_API_URL=http://localhost:8000/api/v1
NEXT_PUBLIC_SITE_URL=http://localhost:3000
```

In `pixelssoft-admin/.env`, set:

```
FRONTEND_URL=http://localhost:3000
CORS_ALLOWED_ORIGINS=http://localhost:3000
```

Changes made in admin (blogs, portfolio, showcase, services, settings) appear on the website after a page refresh.

- Blogs, Portfolio, Showcase, Services CRUD
- Page content (JSON sections)
- Contact message inbox
- Media library
- Tawk.to chat settings
- Google Services hub (Analytics, GTM, AdSense, Search Console, reCAPTCHA, Maps, Google Ads)
- Auto-generates `ads.txt` when AdSense publisher ID is saved
- Agency finance: lead sources (Upwork / Freelancer.com / Direct), wallets (Wise, Payoneer, Pakistani bank, Stripe), milestone release with platform + sales commission, ledger

After pulling this update, run:

```bash
cd pixelssoft-admin
php artisan migrate --seed
```

That adds default sources and wallets. Adjust portal % under **CRM → Lead Sources**. Release money from the project page; it posts into **Accounts → Ledger**, **Payments**, and **Sales Commissions**.

## Production

See [DEPLOYMENT.md](../DEPLOYMENT.md) in the project root.
