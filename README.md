# Fariha — Healthcare Module (with full Admin Panel)

Visit `http://localhost/healthsheba/index.php`.

Or with PHP's built-in server (MySQL must still be running separately):

```bash
C:\xampp\php\php.exe -S localhost:8000 -t C:\xampp\htdocs\healthsheba
```

A PHP + MySQL healthcare section of the Fariha marketplace: premium 3D-animated
hero, glassmorphism UI, light/dark mode, online healthcare services, doctor
booking, and now a **complete in-hospital admin panel** covering every module
shown on the homepage strip — with the database schema and demo data created
automatically on first run.

## What's new in this build

The 12 "in-hospital modules" shown on the homepage (Admin Panel, Users,
Patients, Physicians, Hospital Services, Transactions, User Details, Room
Management, Discounts, Appointments, Doctors, Doctor Specialization) are now
real, working pages under `/admin`, each backed by the same MySQL database as
the public site — not placeholders.

Every module supports list, add, edit, and delete, protected by:

- `require_login()` + a new `require_admin()` check — only users with
  `role = 'admin'` can reach `/admin/*`
- CSRF tokens on every form, same as the rest of the site
- Prepared statements for all reads and writes

When you're logged in as an admin, the module tiles on the homepage become
clickable links straight into the matching admin page, and an **Admin Panel**
button appears in the top nav.

## Patient-facing pages (new)

Every "in-hospital" module now has a real page for patients too, not just
admins:

| Page                  | What it does                                                                           |
| --------------------- | -------------------------------------------------------------------------------------- |
| `doctors.php`         | Full doctor directory, filterable by specialization, books straight through            |
| `specializations.php` | Browse care areas, each linking to its doctors                                         |
| `services.php`        | Every online + in-hospital service in one catalog                                      |
| `rooms.php`           | Room availability by type/price, "Reserve" carries the room into booking               |
| `discounts.php`       | Active discount codes                                                                  |
| `appointment.php`     | Booking flow — now also captures room reservation, payment method, and nursing details |
| `transactions.php`    | Billing — view charges, pick a method, and pay                                         |
| `profile.php`         | Edit your own contact + personal details                                               |

On the homepage, the module tiles route admins into `/admin` and everyone else
into these patient pages. The four staff-only modules (Admin Panel, Users,
Patients, Physicians) stay admin-only and are marked "Staff access only" for
patients.

## Payments

Transactions now support **bKash, Nagad, Rocket, Bank Transfer, Card, and Cash
on Visit**. Patients choose a preferred method when booking, and can change it
right before paying from the Billing page. This is a simulated "Pay Now" (no
live gateway wired in) — swap the update in `transactions.php` for a real
bKash/Nagad/Rocket/bank API call before going to production.

## Nursing service details

Booking the **Home Nursing Service** now reveals extra fields: type of care
(general, post-surgery, elderly, pediatric, palliative, wound dressing,
medication administration), duration in days, and care instructions for the
nurse. These get folded into the appointment notes so admins see them in
Appointments without any schema changes.

## How the admin panel is built

Rather than writing 11 nearly-identical CRUD scripts, each `admin/*.php` page is
a short config array (table name, fields, labels, dropdown sources) handed to a
shared engine:

```
includes/
├── admin_functions.php     # require_admin()
├── admin_layout_header.php # sidebar shell + nav
├── admin_layout_footer.php
├── crud_engine.php         # generic list / add / edit / delete logic
└── crud_view.php           # generic list table + form renderer
```

So `admin/doctors.php` is just:

```php
$config = [
  'table' => 'doctors',
  'title' => 'Doctors',
  'fields' => [
    'full_name' => ['label' => 'Full Name', 'type' => 'text', 'required' => true],
    'specialization_id' => ['label' => 'Specialization', 'type' => 'select', 'options' => $specs],
    // ...
  ],
];
render_crud($pdo, $config);
```

This keeps every module on the same secure code path (same CSRF handling, same
escaping, same query pattern) instead of drifting apart over time.

## Modules included

| Page                        | What it manages                                                                                         |
| --------------------------- | ------------------------------------------------------------------------------------------------------- |
| `admin/index.php`           | Dashboard — live counts (users, patients, physicians, doctors, appointments, revenue) + recent bookings |
| `admin/doctors.php`         | Doctor roster: name, specialization, fee, rating, availability                                          |
| `admin/specializations.php` | Doctor specializations list                                                                             |
| `admin/services.php`        | Online/in-hospital service catalog                                                                      |
| `admin/rooms.php`           | Hospital rooms: type, price/day, availability                                                           |
| `admin/discounts.php`       | Discount/promo codes                                                                                    |
| `admin/appointments.php`    | All bookings — assign patient/doctor/service, change status                                             |
| `admin/transactions.php`    | Payment records — status and method (creation happens via booking, admin manages status)                |
| `admin/patients.php`        | Users with role = patient                                                                               |
| `admin/physicians.php`      | Users with role = physician                                                                             |
| `admin/users.php`           | All accounts, any role, including admins                                                                |
| `admin/user_details.php`    | Extended profile info (address, DOB, gender, blood group)                                               |

## Requirements

- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+ (via XAMPP, Laragon, MAMP, or standalone)
- A modern browser

No Node.js, Composer, or npm install needed — plain PHP, vanilla JS, and CSS,
with Bootstrap loaded from a CDN.

## Run it

1. Install XAMPP or Laragon, start Apache + MySQL.
2. Copy this folder into your web root, e.g. `htdocs/fariha-hospital`.
3. Check `config/database.php` credentials match your local MySQL (defaults work
   for a fresh XAMPP/Laragon install — no manual database creation needed, it
   self-provisions on first request).
4. Visit `http://localhost/fariha-hospital/index.php`.

Or with PHP's built-in server (MySQL must still be running separately):

```bash
php -S localhost:8000
```

## Demo login

| Role           | Email            | Password  |
| -------------- | ---------------- | --------- |
| Admin (seeded) | admin@fariha.com | Admin@123 |

Log in with that account, then click **Admin Panel** in the nav, or any of the
module tiles under "In-Hospital System" on the homepage.

## Folder structure

```
fariha-hospital/
├── config/database.php          # auto-setup DB connection + schema + seed data
├── includes/
│   ├── functions.php             # CSRF, sanitization, session/auth helpers
│   ├── admin_functions.php       # require_admin()
│   ├── icons.php                 # inline SVG icon set
│   ├── header.php / footer.php   # public site layout
│   ├── admin_layout_header.php   # admin sidebar shell
│   ├── admin_layout_footer.php
│   ├── crud_engine.php           # generic CRUD logic
│   └── crud_view.php             # generic CRUD list/form rendering
├── admin/                        # one thin config file per module (see table above)
├── assets/css/style.css          # design tokens + admin panel styles
├── assets/js/main.js
├── index.php, login.php, register.php, logout.php, appointment.php
└── README.md
```

## Security features

- Password hashing via `password_hash()` / `password_verify()` (bcrypt)
- Prepared statements everywhere, no string-concatenated SQL
- CSRF tokens on every form, verified with `hash_equals()`
- `require_admin()` gate on every `/admin` page, checked server-side on every
  request
- Session regeneration on login/register, `httponly` + `SameSite=Lax` cookies
- Server-side input validation, `strip_tags()` on free text,
  `htmlspecialchars()` on all output

## Notes on scope

Transactions are created automatically when a patient books an appointment (see
`appointment.php`); the admin transactions page is for reviewing and updating
their status (pending → paid → refunded) rather than creating them from scratch,
since a transaction should always trace back to a real booking.

The fashion and electronics sides of the wider Fariha marketplace aren't part of
this module — this build is scoped to healthcare + hospital administration end
to end.
