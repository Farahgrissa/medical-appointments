# Medical Appointments

## 1. Project Overview
**Medical Appointments** is a web application designed to facilitate the booking and management of medical appointments. The system allows patients to register, view available doctors, and book appointments, while doctors can manage their schedules and appointments efficiently.  

This project is developed using a **Laravel/PHP backend** and a **Vue.js frontend** connected via **Inertia.js**, incorporating **TailwindCSS** for styling and **MySQL** for data storage. 



## 2. Features
- Patient registration and authentication  
- Doctor dashboard to manage schedules  
- Appointment booking system  
- Responsive design for desktop and mobile  



## 3. Installation

### Backend
1. Clone the repository:
```bash
git clone https://github.com/Farahgrissa/medical-appointments.git
cd medical-appointments
```
2.Install PHP dependencies:
```bash
composer install
```
3.Copy .env.example and configure environment variables:
```bash
cp .env.example .env
```
4.Set your MySQL database credentials in .env:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
5.Run database migrations:
```bash
php artisan migrate
```
6.Start the backend server:
```bash
php artisan serve
```
### Frontend
1.Install Node.js dependencies
```bash
npm install
```
2.Build assets or start development server
```bash
npm run dev
# or to build for production
npm run build
 ```

## 4.Project Structure
### Backend (Laravel/PHP)
```text
app/                     # Application logic
  ├── Http/              # Controllers, Requests, Middleware
  ├── Models/            # Eloquent models
  ├── Policies/          # Authorization policies
  └── Providers/         # Service providers
bootstrap/               # Bootstrap files
config/                  # Configuration files
database/                # Migrations, seeders, factories
  ├── migrations/
  └── seeders/
public/                  # Public assets and index.php
resources/               # Views, frontend templates, static assets
routes/                  # Application routes
  ├── auth.php
  ├── console.php
  ├── settings.php
  └── web.php
storage/                 # Logs, cache, uploads
  ├── app/
  ├── framework/
  └── logs/
tests/                   # Automated tests
artisan                  # Laravel CLI tool
.env.example             # Environment variables template
composer.json            # Backend dependencies
composer.lock            # Locked backend dependencies
```
### Frontend (Vue.js + Inertia)
```text
resources/
├── css/                   # Styles globaux (Tailwind ou custom CSS)
├── js/
│   ├── Components/        # Composants Vue réutilisables
│   ├── Layouts/           # Layouts principaux (app.blade.php / Vue layouts)
│   ├── Pages/             # Pages Vue gérées par Inertia
│   │   ├── Auth/          # Pages d'authentification (Login, Register)
│   │   ├── Dashboards/    # Dashboards des utilisateurs (Doctor, Patient)
│   │   ├── Profile/       # Pages de profil utilisateur
│   │   └── welcome.vue    # Page d'accueil
│   ├── App.vue            # Composant racine Vue (monté via Inertia)
│   ├── app.js             # Point d'entrée JS (initialise Inertia et Vue)
│   └── bootstrap.js       # Initialisation JS (plugins, axios, etc.)
└── public/                # Assets publics (images, favicon, etc.)

```

## 5. Usage

Register as a patient or log in as a doctor.
Patients can browse available doctors and book appointments.
Doctors can manage their schedule and view booked appointments.


## 6. Security Notes

Keep all .env variables private.
Do not commit sensitive data (database credentials) to a public repository.
Use .env.example as a template for collaborators.

## 7. License

This project is licensed under the MIT License. See the LICENSE file for details.



