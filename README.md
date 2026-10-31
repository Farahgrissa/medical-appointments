# Medical Appointments

[![CI](https://github.com/Farahgrissa/medical-appointments/actions/workflows/main.yml/badge.svg)](https://github.com/Farahgrissa/medical-appointments/actions/workflows/main.yml)

A web application for booking and managing medical appointments. Patients book a doctor's free time slots, doctors manage their availability and their appointments, and an administrator manages doctor accounts and follows the activity of the clinic.

**Stack:** Laravel 12 (PHP 8.2+) · Vue 3 · Inertia.js 2 · Tailwind CSS · Spatie Laravel Permission · MySQL or SQLite

---

## Features

### Patient
- Sign up and log in (public registration always creates a **patient** account)
- Find a doctor by name or specialty and see their free slots, grouped by day
- Book a slot, with an optional reason
- Reschedule an appointment to another free slot of the same doctor, or cancel it
- Full history of appointments, with filters (upcoming / past, status, doctor)
- Edit the profile, change the password (current password required), delete the account

### Doctor
- Create availability slots (consultation, follow-up, emergency); overlapping slots are refused
- Accept or cancel incoming appointments
- After the start time, mark an appointment as **completed** or **no-show**
- Appointment list with filters (upcoming / past, status, patient)
- Profile and password management

### Administrator
- Dashboard: number of patients, doctors and appointments, upcoming appointments, cancellation rate, no-show rate, appointments by status
- Create doctor accounts with their specialty: the doctor receives an email link to choose their own password
- Moderation: deactivate / reactivate patient and doctor accounts. A deactivated user cannot log in, and a deactivated doctor can no longer be booked.

---

## Business rules

**Appointment lifecycle**

```text
pending ──► confirmed ──► completed
   │            │    └──► no_show
   └────────────┴──────► cancelled
```

- A slot can never be booked twice: every booking runs in a database transaction that locks the slot.
- A patient cannot have two appointments at overlapping times.
- Cancelling an appointment frees its slot, which can be booked again.
- Moving an appointment sends it back to `pending`: the doctor has to confirm the new time.
- Once the start time has passed, an appointment can no longer be confirmed, moved or cancelled. The doctor can only close it as `completed` or `no_show`.

**Data retention**

- Accounts are **soft deleted**: deleting an account keeps the appointment history, cancels the upcoming appointments and withdraws a doctor's free slots.
- Appointments are never deleted. The database refuses (`ON DELETE RESTRICT`) to remove a user or a slot that still has appointments.

---

## Getting started

### Requirements
- PHP 8.2 or later, with the `mbstring`, `openssl`, `fileinfo`, `intl` and `pdo_sqlite` (or `pdo_mysql`) extensions
- Composer
- Node.js 20.19 or later (required by Vite 7), and npm
- MySQL 8 / MariaDB, or nothing at all if you use SQLite

### Installation

```bash
git clone https://github.com/Farahgrissa/medical-appointments.git
cd medical-appointments

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Then configure the database in `.env`.

**Option A: SQLite** (simplest, no database server needed)

```dotenv
DB_CONNECTION=sqlite
```

```bash
touch database/database.sqlite
```

On Windows PowerShell, use `New-Item database\database.sqlite` instead of `touch`.

**Option B: MySQL**

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=medical_appointment
DB_USERNAME=root
DB_PASSWORD=your_password
```

Create the tables and the demo data:

```bash
php artisan migrate --seed
```

### Running the application

In two terminals:

```bash
php artisan serve   # http://127.0.0.1:8000
npm run dev         # Vite dev server (hot reload)
```

For a production build of the front-end, run `npm run build` instead of `npm run dev`.

### Demo accounts

Created by `php artisan migrate --seed`. All of them use the password `password`.

| Role | Email |
|---|---|
| Administrator | `admin@example.com` |
| Doctor (General practitioner) | `eline@example.com` |
| Patient | `patient@test.com` |

### Emails

With the default `MAIL_MAILER=log`, emails (password reset, doctor invitation) are not sent: they are written to `storage/logs/laravel.log`. Configure the `MAIL_*` variables in `.env` to send real emails.

---

## Tests

```bash
php artisan test
```

The test suite uses an in-memory SQLite database, so it never touches your data. It covers authentication, booking and double-booking, slot overlaps, authorization by role, rescheduling and cancellation, appointment statuses, soft delete and the admin space. A test also checks that every page rendered by a controller exists in `resources/js/Pages`.

The front-end must have been built once (`npm run build`) before the tests run, because the Inertia layout needs the Vite manifest.

### Continuous integration

GitHub Actions (`.github/workflows/main.yml`) runs on every push to `main` and `devops` and on every pull request to `main`. It installs the dependencies, builds the front-end and runs the full test suite.

---

## Project structure

```text
app/
├── Enums/                 # AppointmentStatus, SlotStatus
├── Http/
│   ├── Controllers/
│   │   ├── Auth/          # Login, registration, password reset, email verification
│   │   ├── Users/         # PatientController, DoctorController, AdminController, ProfileController
│   │   └── SlotController.php
│   ├── Middleware/        # EnsureAccountIsActive, HandleInertiaRequests, ...
│   └── Requests/          # Form validation (booking, slots, doctor creation)
├── Models/                # User, Appointment, Slot, PatientFile
├── Policies/              # AppointmentPolicy, SlotPolicy, UserPolicy
└── Services/              # AppointmentBookingService, AccountService
database/
├── factories/             # UserFactory (patient / doctor / admin states)
├── migrations/
└── seeders/               # Roles & permissions, demo users, slots, appointments
resources/js/
├── Components/            # Sidebar, StatusBadge, Pagination, AppointmentFilters, ...
├── Layouts/AppLayout.vue
├── Pages/
│   ├── Admin/             # Users, CreateDoctor
│   ├── Auth/              # Login, Register, ForgotPassword, ResetPassword, ...
│   ├── Dashboards/        # Admin, Doctor and Patient dashboards
│   ├── Doctor/            # Appointments, Slots, Profile
│   ├── Patient/           # Doctors, DoctorSlots, Appointments, EditAppointment
│   └── Profile/           # Patient profile pages
└── utils/                 # Date, time and status formatting
routes/
├── auth.php               # Authentication routes
└── web.php                # Patient (/patient), doctor (/doctor) and admin (/admin) routes
tests/
├── Feature/               # HTTP tests, one file per area
└── Unit/
```

### Roles and permissions

Roles (`patient`, `doctor`, `admin`) and permissions are managed with [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission) and defined in `database/seeders/RolesAndPermissionsSeeder.php`. After changing them, run:

```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

---

## Known limitations

- **No notifications:** when an appointment is booked, confirmed or cancelled, the other party is not notified. They see the change the next time they load a page.
- **No automatic refresh:** an open page is not updated in real time.
- **Time zone:** the application runs in UTC (`config/app.php`). Set `timezone` to your local time zone so that the "upcoming" / "past" rules match the clinic's clock.

---

## Security

- Never commit `.env` or database dumps: `.env` and `*.sql` are listed in `.gitignore`.
- Use `.env.example` as the template for collaborators.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file.
