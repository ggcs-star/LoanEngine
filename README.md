# LoanEngine — Login & Register Only

This ZIP intentionally contains **only login, registration, logout, a minimal authenticated landing page, and the reusable `loanengine/auth` package**. It does not contain loan, payment, repayment, loan-product, or admin-dashboard modules.

## Requirements
- PHP 8.2+
- Composer 2
- MySQL
- Laravel 12 compatible PHP extensions

## Setup
1. Extract this folder to `C:\xampp\htdocs\LoanEngine`.
2. Create a MySQL database named `loanengine` (or update `.env`).
3. Copy `.env.example` to `.env`.
4. In CMD, run:
   ```bat
   cd /d C:\xampp\htdocs\LoanEngine
   composer install
   php artisan key:generate
   php artisan migrate
   php artisan serve
   ```
5. Open `http://127.0.0.1:8000`.

## Routes
- `GET /login` — login page
- `POST /login` — login submit
- `GET /register` — registration page
- `POST /register` — registration submit
- `POST /logout` — logout (authenticated)
- `GET /` — minimal authenticated landing page; guests are sent to login

## Custom package
Package source: `packages/loanengine/auth/`

The package provides the web routes, controller, and Blade views. Sanctum is included as a dependency for Laravel API authentication; this minimal version uses Laravel's session-based web authentication for the browser forms.

## Important
This is a minimal starter source project, not a pre-installed Laravel `vendor` directory. `composer install` must complete successfully before Artisan commands will work.
