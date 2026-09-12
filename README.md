# Diaspo Immo

Diaspo Immo is a Laravel-based property management platform built for
diaspora property owners who need to manage real estate back home without
being physically present. It connects three kinds of users — **admins**,
**owners**, and **tenants** — around properties, leases, rent payments,
maintenance issues, and public visit requests from prospective renters.

## Tech stack

| Layer      | Technology                                   |
|------------|-----------------------------------------------|
| Backend    | PHP 8.2+, Laravel 12                          |
| Frontend   | Blade templates, Tailwind CSS 4, Vite 7       |
| Database   | PostgreSQL (default; any Laravel-supported DB works) |
| Queue/Cache/Session | Database driver                     |
| Dev tooling| Laravel Sail, Pail, Pint (via Composer scripts) |

## User roles

The `users.role` column drives access to the authenticated app
(`app/Http/Middleware/EnsureUserHasRole.php`, aliased as `role` in
`bootstrap/app.php`; most checks are additionally enforced per-action inside
the controllers):

- **Admin** — full access: manages users, properties, leases, approves or
  rejects tenant-submitted payments, records payments on a tenant's behalf,
  triages maintenance issues, and handles public visit requests.
- **Owner** — views their own properties, active leases, and payment history.
- **Tenant** — views their lease, submits rent payments for admin approval,
  reports and tracks maintenance issues.

Unauthenticated visitors can browse the public property catalog and submit a
visit request without logging in.

## Core domain

| Model         | Purpose                                                        |
|---------------|------------------------------------------------------------------|
| `User`        | Admins, owners, and tenants (`role`, `is_active`).             |
| `Property`    | A unit owned by an owner (`type`, `monthly_rent`, `status`: vacant / occupied / maintenance). |
| `Lease`       | Binds a tenant to a property (`billing_cycle`, `status`: active / ended, optional signed-document path). |
| `Payment`     | A rent payment on a lease — either recorded directly by an admin (`approved`) or submitted by a tenant and awaiting review (`pending` → `approved`/`rejected`), with an auto-generated receipt number once approved. |
| `Issue`       | A maintenance ticket on a property (`priority`, `status`: open / in_progress / resolved / closed), reportable by a tenant and assignable to staff. |
| `VisitRequest`| A lead from the public catalog (`status`: new / contacted / closed), optionally handled by an admin. |

## Features

**Public site**
- Browse the property catalog and view individual listings (`/`, `/browse/{property}`).
- Submit a visit request on a listing without an account.

**Admin**
- Manage users (create, list, activate/deactivate).
- Full CRUD on properties.
- Assign and end leases.
- Approve/reject tenant-submitted payments, record payments directly, generate receipts.
- Triage and update maintenance issues.
- Track and update the status of public visit requests.

**Owner**
- Dashboard summary of their properties, active leases, and rent collected.

**Tenant**
- Dashboard with lease and payment status.
- Submit rent payments for admin approval and view a printable receipt.
- Report maintenance issues and follow their status.

## Getting started

### Requirements
- PHP >= 8.2 with the extensions Laravel requires
- Composer
- Node.js + npm
- PostgreSQL (or update `.env` for another driver)

### Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
```

Configure your database in `.env`, then:

```bash
php artisan migrate --seed
npm run build   # or `npm run dev` during development
php artisan serve
```

> This repo is set up to run under [Laravel Herd](https://herd.laravel.com);
> if you're using Herd, point it at this folder and skip `php artisan serve`.

### Seeded demo accounts

`database/seeders/DatabaseSeeder.php` creates one admin, two owners, and two
tenants (with sample properties, leases, payments, issues, and visit
requests) so the app is usable immediately after a fresh install. Every
seeded account uses the password `password`; check the seeder for exact
emails before sharing this on anything but a local/demo environment.

### One-shot setup

`composer setup` runs install → `.env` copy → key generation → migrations →
`npm install` → `npm run build` in one go (see `composer.json`).

### Running in development

```bash
composer dev
```

Runs the PHP server, queue listener, and Vite dev server concurrently.

### Tests

```bash
php artisan test
```

## Project structure

```
app/
  Http/Controllers/     Feature controllers (Property, Lease, Payment, Issue, User, VisitRequest, public site, auth)
  Http/Middleware/       Role-based access guard
  Models/                Eloquent models for the domain above
database/
  migrations/            Schema for users, properties, leases, payments, issues, visit_requests
  seeders/                Demo data
resources/
  views/                 Blade templates, organized by feature (dashboard/, properties/, leases/, payments/, issues/, users/, public/)
routes/web.php           All application routes (public, guest, authenticated)
```

## Known gaps / suggested next steps

- **Automated tests** — `tests/` still only contains Laravel's default
  boilerplate; there is no feature coverage for leases, payments, or role
  enforcement yet.
- **Form Requests & Policies** — validation and authorization currently live
  inline in the controllers; extracting `FormRequest` and `Policy` classes
  would make the access rules easier to audit and unit test.
- **Version control** — this directory is not yet a Git repository. Run
  `git init` and make an initial commit before iterating further, ideally
  with the `.env` and seeded credentials rotated for any shared/deployed copy.
- **Notifications** — payments, issues, and visit requests change status
  silently; wiring up Laravel notifications (email or in-app) for these
  events would close the loop for owners and tenants.
- **File uploads** — `leases.document_path` exists in the schema but no
  controller currently handles uploading a signed lease document.
