<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Quick Start

### 1) Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

### 2) Run

Option A (recommended helper script):

```bash
./start.sh
```

Option B (manual):

```bash
php artisan serve --host=127.0.0.1 --port=8001
npm run dev
```

App URL: `http://127.0.0.1:8001`

### Database Environment Commands (Dummy vs Real)

Project uses two DB profiles:
- `dummy` for Development/Testing
- `real` for Production

Prepare env templates first:
- Edit `.env.dummy` with dummy DB credentials
- Edit `.env.real` with real DB credentials

Switch to **Dummy DB**:

```bash
./scripts/switch-env.sh dummy
php artisan app:init-dummy
```

Notes:
- `app:init-dummy` only works when `APP_ENV=testing`
- It will migrate missing tables and seed dummy data
- Minimum generated data: 10 users, 200 students, 1000 transactions

Switch to **Real DB**:

```bash
./scripts/switch-env.sh real
php artisan migrate --force
php artisan db:seed --class=RolePermissionSeeder --force
php artisan db:seed --class=SettingsSeeder --force
php artisan db:seed --class=FixedLoginSeeder --force
```

Notes:
- `switch-env.sh real` requires typing `REAL` as confirmation
- Real profile must use `APP_ENV=production`
- Do not run `app:init-dummy` on real DB

### 3) Login

Login yang dipastikan aktif (seeded by `FixedLoginSeeder`):

- **Admin TU**
  - Email: `admin.tu@sakumi.local`
  - Password: `AdminTU#2026`
  - Roles: `admin_tu`, `super_admin`

- **Staff**
  - Email: `staff@sakumi.local`
  - Password: `Staff#2026`
  - Roles: `staff`, `operator_tu`

- **Bendahara**
  - Email: `bendahara@sakumi.local`
  - Password: `Bendahara#2026`
  - Roles: `bendahara`

- **Kepala Sekolah**
  - Email: `kepala.sekolah@sakumi.local`
  - Password: `KepalaSekolah#2026`
  - Roles: `kepala_sekolah`

Jika akun belum ada/tertimpa, jalankan:

```bash
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=FixedLoginSeeder
php artisan permission:cache-reset
```

### 4) Troubleshooting

- Port `8001` already used:
  - Stop existing process: `./stop.sh`
  - Or run manual server on another port:
    `php artisan serve --host=127.0.0.1 --port=8002`
- Frontend assets not loading:
  - Ensure Vite is running: `npm run dev`
  - Reinstall packages if needed: `npm install`
- Database / migration errors:
  - Check `.env` DB settings
  - Jalankan non-destruktif: `php artisan migrate` lalu `php artisan db:seed`
  - Jangan gunakan `php artisan migrate:fresh --seed` pada DB yang sudah berisi data entry (command ini menghapus semua tabel/data)
- Permission/cache oddities:
  - Clear Laravel cache: `php artisan optimize:clear`

### 5) Security (OWASP ZAP)

This repository includes an OWASP ZAP baseline scan workflow:

- Workflow file: `.github/workflows/zap-baseline.yml`
- Triggers: pull requests, manual dispatch, and weekly schedule
- Target: local app at `http://127.0.0.1:8000`
- Reports: uploaded as `zap-baseline-report` artifact (`html`, `json`, `xml`, `md`)
- Gate: workflow fails if any **Medium** or **High** risk alerts are detected
- Pull requests: posts an automatic comment with Medium/High alert counts

To run it manually from GitHub UI:

1. Open **Actions** → **OWASP ZAP Baseline**
2. Click **Run workflow**
3. Open run artifacts and download `zap-baseline-report`

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
