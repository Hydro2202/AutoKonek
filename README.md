# AutoKonek

**A Web-Based Automotive Services Discovery and Management System.** *Find the Services Your Vehicle Needs.*

AutoKonek lets vehicle owners search gas stations, car washes, repair shops, tire shops, parking facilities and battery services, read ratings and reviews, and report bad listings. Business owners submit and manage their establishments; administrators approve listings and moderate the platform.

Built with Laravel 13, PHP 8.4, PostgreSQL, Blade, Tailwind CSS 4 and Vite. This is a college final project.

## Features by role

| Role | Can do |
|---|---|
| **Customer** | Browse/search/filter the directory, view details, write/edit/delete own reviews, report listings and reviews, manage profile |
| **Business owner** | Submit establishments for approval, edit them, manage services (optional prices) and images, see rejection reasons and resubmit, view reviews and stats |
| **Administrator** | Dashboard statistics, activate/deactivate users, approve/reject listings (with reason), manage categories, moderate reviews, resolve/dismiss reports, read contact messages |

### Approval policy

- A new listing is always **pending** and is invisible to the public until an administrator approves it.
- Editing the **details** of an **approved** listing (name, category, description, address, contact, hours) sends it back to **pending**, so changes are re-checked. If nothing actually changed, it stays approved.
- Editing a **rejected** listing and saving **resubmits** it as pending and clears the rejection reason.
- Services and images can be changed at any time without re-approval.
- Owners can never set `status` themselves: the field is not mass-assignable and is not read from the request.

## Requirements

- PHP 8.3+ with extensions: `pdo_pgsql`, `pgsql`, `mbstring`, `openssl`, `fileinfo`, `gd`, `zip`, `curl`
- Composer 2
- Node.js 20+ and npm
- PostgreSQL 14+

## Installation

### 1. Install dependencies

```bash
composer install
npm install
```

### 2. Create the databases

Using `psql` (it will ask for the `postgres` password):

```bash
psql -U postgres -c "CREATE DATABASE autokonek;" -c "CREATE DATABASE autokonek_test;"
```

On Windows, `psql` is usually in `C:\Program Files\PostgreSQL\<version>\bin`. You can also create the two databases in pgAdmin 4 (Databases > Create > Database).

`autokonek_test` is used only by the automated tests. **Never point the tests at a database that holds real data**: the tests wipe it.

### 3. Configure the environment

```bash
cp .env.example .env        # Windows PowerShell: Copy-Item .env.example .env
php artisan key:generate
```

Edit `.env`:

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=autokonek
DB_USERNAME=postgres
DB_PASSWORD=your_postgres_password

ADMIN_NAME="AutoKonek Admin"
ADMIN_EMAIL=you@example.com
ADMIN_PASSWORD=choose_a_strong_password
```

`.env` is git-ignored. Never commit it.

### 4. Run migrations and seeders

```bash
php artisan migrate --seed
```

This creates the tables, the six default categories, and the first administrator from `ADMIN_EMAIL` / `ADMIN_PASSWORD`. If those are empty, no administrator is created.

When `APP_ENV` is not `production`, it also loads **fictional demo data** (see below).

To start over completely: `php artisan migrate:fresh --seed`.

### 5. Create the storage symlink

Uploaded images are stored on Laravel's `public` disk:

```bash
php artisan storage:link
```

### 6. Build frontend assets and start the server

```bash
npm run build          # one-off build
php artisan serve      # http://localhost:8000
```

While developing styles, run `npm run dev` in a second terminal instead of `npm run build`.

### 7. Run the tests

```bash
php artisan test
```

Tests use the `autokonek_test` PostgreSQL database (configured in `phpunit.xml`; the password comes from your `.env`).

## First administrator

The administrator is created by the seeder from `ADMIN_EMAIL` and `ADMIN_PASSWORD` in your `.env`. Nobody can become an administrator through registration or profile editing. To create or reset the administrator later, change those two values and run:

```bash
php artisan db:seed --class=AdminSeeder
```

## Demo credentials (local development only)

> **Do not use these in production.** Demo data is only seeded when `APP_ENV` is not `production`.

All demo accounts use the password `password`:

| Role | Email |
|---|---|
| Business owner | `owner1@autokonek.test`, `owner2@autokonek.test` |
| Customer | `customer1@autokonek.test` ... `customer4@autokonek.test` |

The administrator uses whatever you set in `ADMIN_EMAIL` / `ADMIN_PASSWORD`.

The demo establishments (for example "Demo Sparkle Car Wash") are **fictional** and are not real businesses. They include approved, pending and rejected listings, sample reviews and sample reports.

## Security notes

- Passwords are hashed (bcrypt via Laravel). CSRF protection is on for every form.
- Roles are enforced on the server by the `role` middleware and policies, not by hiding buttons. `role` and `is_active` are not mass-assignable.
- Deactivated users are signed out on their next request and cannot log in.
- Owners can only touch their own establishments (policy check plus route scoping for nested services and images).
- Uploads: JPG/PNG/WebP only, max 2 MB, stored under a random generated file name.
- User text is escaped by Blade. All queries go through Eloquent or bindings.
- PostgreSQL constraints back up the rules: role/status/rating `CHECK`s, unique reviews per user and establishment, a report must reference exactly one target, and only one open report per user per target.

This has been checked by automated tests, but no automated test suite proves an application is completely secure.

## Legal pages and cookies

- **Privacy Policy** (`/privacy`), **Terms of Service** (`/terms`) and **Cookie Policy** (`/cookies`) are linked from the footer, the sign-up form and the auth pages.
- Registration requires ticking the box that accepts the Terms and Privacy Policy.
- A **cookie banner** appears on first visit. AutoKonek sets only essential cookies (login session, form security token, an optional "remember me" cookie, and the banner choice itself), so the banner records an acknowledgement. "Cookie settings" in the footer reopens it so the choice can be withdrawn. Fonts are bundled by Vite and served from the app, so no third party sets cookies.
- There is no Refund Policy because AutoKonek takes no payments (the Terms say so).
- The "last updated" date and contact email come from `config/autokonek.php` and `AUTOKONEK_CONTACT_EMAIL` in `.env`.
- These pages are a reasonable starting point written for this project's real behavior, not legal advice. Have a qualified person review them before using AutoKonek for real.

## Places, maps and GPS

AutoKonek is based in **Lucena City, Quezon** (change it in `config/autokonek.php`). Everything is built for the Philippines:

- **Official places.** `php artisan migrate --seed` loads every region, province, city and municipality from `database/data/ph-locations.json`: 17 regions, 81 provinces and 1,634 cities and municipalities (146 flagged as cities). The data comes from the Philippine Standard Geographic Code (PSA) and is a snapshot, so recently created cities may be missing. Re-running `php artisan db:seed --class=PhilippineLocationSeeder` is safe.
- **Structured location.** A listing stores its city (which determines its province and region), an optional barangay, and the street address. Owners pick the place with region, province and city selectors; Metro Manila has no provinces, so its cities are listed directly. New listings start in Lucena City.
- **Browse by place.** `/places` lists every region with its cities (or every city and municipality), with listing counts. The directory can be filtered by region, province and city.
- **Directions.** Each listing has "Get directions" (Google Maps), "Open in Waze" and "OpenStreetMap" buttons. They are plain links, so no map API key is needed. Listings with a GPS pin send the exact position; others search the address.
- **Map.** The OpenStreetMap map on a listing page loads only after the visitor presses "Show map", so opening a page contacts no third party.
- **GPS.** "Near me" in the directory asks the browser for the visitor's position and sorts by distance (straight-line distance, calculated in PostgreSQL). "How far is it?" on a listing is calculated in the browser. Owners can fill in their business's latitude and longitude with "Use my current location". Browsers require HTTPS (or `localhost`) for location access.

## Page transitions

When a link is clicked, a car drives along a road past roadside place signs and keeps going once the next page has loaded. It runs for every link in the public site, owner area and admin area, and is skipped for visitors who prefer reduced motion. The code is in `resources/js/modules/route-transition.js` and `resources/css/components/transition.css`.

## Design system

Road-signage and workshop vocabulary: navy asphalt, one orange "center line", square sign tiles, license-plate ratings and job-card listing tickets. Headings use Barlow Condensed, body text Barlow, and the admin area IBM Plex Sans. Design tokens live in `resources/css/theme.css`. Motion is limited to the homepage service board, toasts, the cookie banner, dialogs and button loading states, and it is switched off for visitors who prefer reduced motion.

## Project structure

```
app/
  Http/Controllers/
    Auth/        LoginController, RegisterController
    Account/     DashboardController (role redirect), ProfileController
    Site/        HomeController, EstablishmentController (directory), LocationController (places), ContactController
    Customer/    ReviewController, ReportController, ActivityController
    Owner/       Dashboard, Establishment, Service, Image, Review controllers
    Admin/       Dashboard, User, Establishment, Category, Review, Report, Message controllers
  Http/Middleware/   EnsureRole, EnsureUserIsActive
  Http/Requests/     Form requests grouped like the controllers (Auth, Account, Customer, Owner, Admin, Site)
  Models/            User, Category, Establishment, Service, EstablishmentImage, Review, Report, ContactMessage, Region, Province, City
  Policies/          EstablishmentPolicy, ReviewPolicy
  Services/          ListingApprovalService (approval rules), EstablishmentImageService (uploads), LocationLookup (place lists)
  Support/           MapLinks (directions and map links)
  View/Composers/    AdminSidebarComposer (live counters)
config/autokonek.php   App-specific settings (contact email, legal date, image limits, page sizes)
database/            migrations, seeders, factories
routes/web.php       Loads routes/web/{site,auth,account,owner,admin}.php
resources/
  css/               app.css imports theme.css, base.css and components/*.css
  js/                app.js imports small modules from js/modules/
  views/
    layouts/         base, site, auth, owner, admin
    components/      ui/, form/, listing/, layout/, panel/, legal/, brand/
    site/ auth/ account/ customer/ owner/ admin/ legal/
tests/Feature/       Automated tests
```

## Troubleshooting

**`php` or `composer` is not recognized.** Close and reopen your terminal (and VS Code) so it picks up the new PATH.

**`could not find driver` / `PDOException`.** Enable PostgreSQL in `php.ini` by removing the `;` before `extension=pdo_pgsql` and `extension=pgsql`, then check with `php -m`.

**`SQLSTATE[08006] connection refused`.** PostgreSQL is not running or the port is wrong. Check the service is running and that `DB_HOST` / `DB_PORT` match. Test with `psql -U postgres -h 127.0.0.1`.

**`password authentication failed for user "postgres"`.** `DB_PASSWORD` in `.env` is wrong. After editing `.env`, run `php artisan config:clear`.

**`database "autokonek" does not exist`.** Create it (step 2).

**Tests fail with `database "autokonek_test" does not exist`.** Create it (step 2).

**Page shows unstyled HTML or `Vite manifest not found`.** Run `npm install` then `npm run build` (or `npm run dev`).

**`Cannot apply unknown utility class` during build.** A custom class in `resources/css/app.css` must be defined with `@utility` so other classes can `@apply` it.

**Uploaded images do not show.** Run `php artisan storage:link`, and make sure `APP_URL` matches the address you open in the browser.

**Changes to `.env` are ignored.** Run `php artisan config:clear`.

**Password reset.** Not included, because it needs a configured mail server. Use the profile page to change a password, or the administrator can be reset with the `AdminSeeder` command above.
