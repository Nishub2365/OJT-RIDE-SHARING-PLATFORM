# 🚗 BROCAR Nepal — Setup & Installation Guide

## Quick Start (Laptop Presentation)

### Prerequisites
- PHP 8.2+ with extensions: BCMath, Mbstring, OpenSSL, PDO, Tokenizer, XML, JSON
- MySQL 8.0+ or MariaDB 10.6+
- Composer 2.x
- Node.js 18+ & npm

### 1. Extract & Configure
```bash
unzip brocar-laravel.zip -d brocar
cd brocar
cp .env.example .env
```

### 2. Edit `.env` (minimum required)
```
APP_NAME=BROCAR
APP_ENV=local
APP_KEY=           # auto-generated in step 4
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=brocar_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3. Create Database
```sql
CREATE DATABASE brocar_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4. Install & Migrate
```bash
composer install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

### 5. Start Server
```bash
php artisan serve
```
Visit: **http://localhost:8000**

---

## Demo Accounts (created by seeder)

| Role   | Phone        | OTP (Debug) | Password |
|--------|-------------|-------------|----------|
| Admin  | 9800000001  | shown on screen | brocar123 |
| Rider  | 9800000002  | shown on screen | brocar123 |
| Driver | 9800000003  | shown on screen | brocar123 |

> **OTP Note**: In `APP_DEBUG=true` mode, the 6-digit OTP is shown directly on the WhatsApp-style verification screen — NO external SMS API needed. Just enter the code shown.

---

## Key Features Implemented

### Authentication
- ✅ Phone number login with 6-digit OTP
- ✅ **Forgot Password** button on login screen
- ✅ **Remember Me** checkbox
- ✅ WhatsApp-style OTP display (no API needed)
- ✅ 10-minute OTP expiry timer
- ✅ 6-box auto-advance OTP input with paste support

### Maps & Navigation
- ✅ Leaflet.js dark-theme maps on all pages
- ✅ **Leaflet Routing Machine** — routes drawn along real roads (OSRM)
- ✅ Leaflet attribution watermark completely removed
- ✅ **BROCAR logo click** → refreshes/navigates home
- ✅ Live driver GPS tracking (5-second poll)
- ✅ Nominatim address autocomplete (Jhapa-focused)

### Jhapa Boundary Check
- ✅ Destination outside Jhapa shows **"Coming Soon"** banner
- ✅ Bounding box: lat 26.40–26.85 / lng 87.60–88.20
- ✅ Submit button disabled if outside Jhapa
- ✅ Covers: Birtamod, Mechinagar, Damak, Kankai, Bhadrapur, Arjundhara

### Vehicle System
- ✅ 4 vehicle types: **Bike 🏍️ / Auto 🛺 / Car 🚗 / SUV 🚙**
- ✅ Vehicle-specific pricing:
  - Bike:  NPR 80 base + 15/km  (max ~200)
  - Auto:  NPR 100 base + 20/km (max ~300)
  - Car:   NPR 150 base + 30/km (max ~500)
  - SUV:   NPR 200 base + 40/km (max ~700)
- ✅ **Only online drivers** for selected vehicle type shown
- ✅ Vehicle badge in driver sidebar & dashboard
- ✅ Driver dashboard only shows rides matching their vehicle type
- ✅ Real-time online driver count per vehicle (30s refresh)

### Currency
- ✅ **All prices in Nepali Rupees (NPR)**
- ✅ Wallet balance shows NPR everywhere
- ✅ Fare estimates, bids, earnings all in NPR

### Rider Features
- ✅ Book ride with vehicle selection & fare offer
- ✅ Promo code validation
- ✅ Schedule rides (future date/time)
- ✅ Real-time bid tracking from drivers
- ✅ Accept/reject bids
- ✅ **Detailed ride info page** with full timeline
- ✅ **Edit ride** (address, vehicle, fare) for pending rides
- ✅ Live map tracking during ride
- ✅ Trip history with Details button
- ✅ Rate driver after completion
- ✅ Share trip link

### Driver Features
- ✅ Vehicle type badge in sidebar & dashboard header
- ✅ Online/Offline toggle
- ✅ Only sees rides matching their vehicle type
- ✅ Place bids on available rides
- ✅ Live navigation with road-following route
- ✅ Earnings dashboard (daily/weekly/monthly)
- ✅ Document upload (license, insurance)
- ✅ Profile management

### Admin Features
- ✅ Full dashboard with analytics
- ✅ Driver approval/rejection workflow
- ✅ User management (ban/unban)
- ✅ Ride monitoring
- ✅ SOS alert management
- ✅ Promo code creation
- ✅ Support ticket replies
- ✅ Broadcast notifications

### Loading & UI
- ✅ Full-screen page loader on navigation
- ✅ Button spinners on form submit
- ✅ Toast notifications (success/error/info)
- ✅ Real-time notification bell with unread dot
- ✅ Responsive dark-theme design
- ✅ Mobile hamburger sidebar

---

## Architecture
```
app/
├── Http/Controllers/     # 13 controllers
├── Models/               # 16 models
├── Middleware/           # RoleCheck, BanCheck
└── Providers/            # AppServiceProvider (Blade directives)

resources/views/
├── layouts/app.blade.php  # Master layout (sidebar, topbar, loader, toasts)
├── auth/                  # phone.blade.php, otp.blade.php, register.blade.php
├── rider/                 # dashboard, request-ride, active-ride, history, ride-detail, edit-ride
├── driver/                # dashboard, active-ride, earnings, documents
├── admin/                 # dashboard, analytics, drivers, users, rides
├── wallet/, delivery/, support/
└── home.blade.php         # Landing page

database/
└── migrations/2024_brocar_complete.php  # All 17 tables in one file
```

## Tech Stack
- **Backend**: Laravel 12 / PHP 8.2
- **Frontend**: Blade + Vanilla JS (no Node build needed)
- **Maps**: Leaflet.js 1.9.4 + Leaflet Routing Machine 3.2.12
- **Charts**: Chart.js 4.4
- **Tiles**: CartoDB Dark (no API key needed)
- **Routing**: OSRM public API (no API key needed)
- **Geocoding**: Nominatim OSM (no API key needed)
- **Database**: MySQL 8.0+
- **Auth**: Session-based OTP (no SMS API needed)
