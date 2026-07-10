# PixelsSoft — Hostinger Deployment Guide

## Architecture

| Component | URL | Folder |
|-----------|-----|--------|
| Public website | `https://pixelssoft.com` | Next.js static export → `public_html/` |
| Admin + API | `https://admin.pixelssoft.com` | Laravel → subdomain document root = `public/` |

## 1. Hostinger MySQL Setup

1. Log in to hPanel → **Databases** → **MySQL Databases**
2. Create database: `pixelssoft_cms`
3. Create user and assign to database
4. Note: host, database name, username, password

## 2. Laravel Admin Deployment

Upload `pixelssoft-admin/` to subdomain folder (e.g. `admin.pixelssoft.com`).

Configure `.env`:

```env
APP_NAME="PixelsSoft Admin"
APP_ENV=production
APP_URL=https://admin.pixelssoft.com
APP_KEY=base64:... # run: php artisan key:generate

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=pixelssoft_cms
DB_USERNAME=your_user
DB_PASSWORD=your_password

CORS_ALLOWED_ORIGINS=https://pixelssoft.com,https://www.pixelssoft.com
```

Run on server (via SSH or Hostinger terminal):

```bash
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan db:seed --force
mkdir -p public/uploads
chmod -R 755 public/uploads storage bootstrap/cache
```

Media uploads are stored in `public/uploads/` (served directly, no `storage:link` required for images).

**Default admin login:** `admin@pixelssoft.com` / `password` — change immediately after first login.

Point subdomain document root to `pixelssoft-admin/public/`.

## 3. Next.js Static Site Deployment

On your local machine:

```bash
# Set production API URL
echo "NEXT_PUBLIC_API_URL=https://admin.pixelssoft.com/api/v1" > .env.local
echo "NEXT_PUBLIC_SITE_URL=https://pixelssoft.com" >> .env.local

yarn build
yarn export
```

Upload contents of `out/` folder to `public_html/`.

Also copy `public/robots.txt`, `public/sitemap.xml`, `public/ads.txt` to `public_html/`.

## 4. SSL

Enable free SSL (Let's Encrypt) for both domains in hPanel.

## 5. CORS

Ensure `config/cors.php` / `CORS_ALLOWED_ORIGINS` in Laravel `.env` includes your production domain.

## 6. Post-Deploy Checklist

- [ ] Admin login works at `admin.pixelssoft.com/login`
- [ ] API returns data: `admin.pixelssoft.com/api/v1/blogs`
- [ ] Contact form submits successfully
- [ ] Image uploads work in admin media library
- [ ] Google Services page saves settings
- [ ] Privacy policy and terms pages load
- [ ] Cookie consent banner appears
- [ ] Change default admin password

## 7. AdSense Application

1. Keep AdSense **disabled** in admin until approved
2. Ensure privacy policy is live
3. Apply at https://adsense.google.com
4. After approval: enter Publisher ID in admin → Google Services → enable AdSense
