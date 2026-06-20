# 🚗 BROCAR — Premium Ride-Hailing Application

> **Production-ready Laravel 12 ride-hailing platform for Nepal**  
> Competitive bidding · Real-time GPS · In-app chat · SOS · Delivery · Wallet

---

## 📋 Table of Contents

1. [Features](#features)
2. [Tech Stack](#tech-stack)
3. [Quick Start](#quick-start)
4. [Installation](#installation)
5. [Configuration](#configuration)
6. [Demo Credentials](#demo-credentials)
7. [Folder Structure](#folder-structure)
8. [Feature Walkthrough](#feature-walkthrough)
9. [API Endpoints](#api-endpoints)
10. [Deployment](#deployment)

---

## ✨ Features

### 🧑 Rider
| Feature | Details |
|---------|---------|
| **Phone OTP Login** | Session-based OTP — no SMS API needed |
| **Ride Request** | Nominatim geocoding, interactive Leaflet map, 5 vehicle types |
| **Bidding System** | Real-time driver bids, accept best offer |
| **Live GPS Tracking** | Driver position updated every 5 seconds |
| **In-App Chat** | AJAX polling, no number sharing |
| **SOS Emergency** | GPS alert to trusted contacts + admin |
| **Trip Sharing** | Share live trip link with family |
| **Rating System** | 5-star interactive, emoji labels |
| **Ride Scheduling** | Book up to 7 days in advance |
| **Promo Codes** | Apply codes at checkout |
| **Wallet** | Top up, pay, withdraw |
| **Delivery** | Document/parcel/freight ordering |

### 🧑‍✈️ Driver
| Feature | Details |
|---------|---------|
| **Approval Flow** | Admin-reviewed document verification |
| **Online Toggle** | Green dot indicates availability |
| **Bid Placement** | Bid on available rides with custom fare |
| **Live Location** | Auto-share GPS during trips |
| **Ride Management** | Arrived → Started → Completed status flow |
| **Earnings Dashboard** | Today / Week / Month / All-time + chart |
| **Document Upload** | Citizenship, License, Vehicle Reg, Photo |
| **Wallet Withdrawal** | Request to bank/eSewa/Khalti |

### 👑 Admin
| Feature | Details |
|---------|---------|
| **Dashboard** | Live stats, 7-day charts, SOS alerts |
| **Driver Management** | Approve/Reject/Suspend + document verify |
| **User Management** | Ban/Unban, full profile view |
| **Ride Monitor** | Filter by status, search by user |
| **Analytics** | 30-day ride/revenue charts, vehicle breakdown |
| **Finance** | All wallet transactions, revenue overview |
| **SOS Management** | GPS map link, one-click resolve |
| **Support Tickets** | Reply with status updates |
| **Promo Codes** | Create, toggle, track usage |
| **Broadcast** | Send notification to all users |
| **Complaints** | Manage rider/driver disputes |

---

## 🛠 Tech Stack

```
Backend:     Laravel 12 / PHP 8.2
Database:    MySQL 8.0+
Frontend:    Blade + Inline Tailwind-inspired CSS (no build step)
Maps:        Leaflet.js 1.9.4 + OpenStreetMap (dark tiles)
Geocoding:   Nominatim (free, no API key)
Charts:      Chart.js 4.4
Real-time:   AJAX polling (4–20s intervals)
Auth:        Session-based OTP (no external SMS)
Payments:    BROCAR Wallet (no Stripe/Razorpay)
Storage:     Laravel Storage (local/S3)
```

---

## ⚡ Quick Start

```bash
# 1. Extract the folder
cd laravel-brocar

# 2. Install dependencies
composer install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Configure database (edit .env)
DB_DATABASE=brocar
DB_USERNAME=root
DB_PASSWORD=your_password

# 5. Run migrations & seed
php artisan migrate --seed

# 6. Create storage link
php artisan storage:link

# 7. Start server
php artisan serve
```

Open **http://localhost:8000** — done! 🎉

---

## 📦 Installation

### Prerequisites
- PHP >= 8.2 with extensions: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML
- MySQL 8.0+
- Composer 2.x

### Detailed Steps

```bash
# Clone / extract project
cd laravel-brocar

# Install PHP dependencies
composer install --optimize-autoloader

# Environment setup
cp .env.example .env
php artisan key:generate

# Database
# Create database: CREATE DATABASE brocar CHARACTER SET utf8mb4;
php artisan migrate

# Optional: Demo data
php artisan db:seed

# File storage
php artisan storage:link

# Optimize (production)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start development server
php artisan serve --port=8000
```

---

## ⚙️ Configuration

### `.env` Key Settings

```env
APP_URL=http://your-domain.com
APP_DEBUG=false          # Set false in production

DB_HOST=127.0.0.1
DB_DATABASE=brocar
DB_USERNAME=root
DB_PASSWORD=secret

SESSION_DRIVER=database  # Required for multi-server
FILESYSTEM_DISK=local    # Change to 's3' for cloud storage
APP_TIMEZONE=Asia/Kathmandu
```

### OTP (Development Mode)
In `APP_DEBUG=true`, the OTP is auto-filled on the verify screen — no SMS needed.  
In production, integrate any SMS API in `AuthController::sendOtp()`.

---

## 🔑 Demo Credentials

After running `php artisan db:seed`:

| Role | Phone | OTP |
|------|-------|-----|
| **Admin** | 9800000001 | _(shown on screen in debug mode)_ |
| **Rider** | 9841000001 | _(shown on screen in debug mode)_ |
| **Driver (Approved)** | 9851000001 | _(shown on screen in debug mode)_ |
| **Driver (Pending)** | 9851000006 | _(shown on screen in debug mode)_ |

> **Note:** OTP is stored in session and displayed on screen in debug mode.  
> In production, you'd send it via SMS (Sparrow SMS, Aakash SMS, etc.)

---

## 📁 Folder Structure

```
laravel-brocar/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AdminController.php      # Admin dashboard, drivers, users, rides
│   │   │   ├── AuthController.php       # OTP login, registration
│   │   │   ├── ChatController.php       # AJAX chat polling
│   │   │   ├── DeliveryController.php   # Parcel delivery orders
│   │   │   ├── DriverController.php     # Driver dashboard, bids, earnings
│   │   │   ├── HomeController.php       # Landing page + role redirect
│   │   │   ├── NotificationController.php
│   │   │   ├── PromoController.php      # Promo code validation
│   │   │   ├── RatingController.php
│   │   │   ├── RiderController.php      # Ride request, tracking, bidding
│   │   │   ├── SosController.php        # Emergency SOS
│   │   │   ├── SupportController.php    # Help tickets
│   │   │   └── WalletController.php     # Top-up, withdraw
│   │   └── Middleware/
│   │       └── CheckRole.php            # Role-based access control
│   ├── Models/ (16 models)
│   └── Providers/AppServiceProvider.php
├── bootstrap/app.php                    # Laravel 12 middleware registration
├── database/
│   ├── migrations/
│   │   └── 2024_01_01_000001_create_brocar_tables.php  # All 17 tables
│   └── seeders/DatabaseSeeder.php       # Demo data
├── resources/views/
│   ├── layouts/app.blade.php            # Master dark layout
│   ├── home.blade.php                   # Landing page
│   ├── auth/                            # phone, otp, register
│   ├── rider/                           # 8 views + partials
│   ├── driver/                          # 8 views
│   ├── admin/                           # 11 views
│   ├── wallet/
│   ├── support/
│   └── delivery/
├── routes/web.php                       # 65+ endpoints
├── composer.json
└── .env.example
```

---

## 🔄 Feature Walkthrough

### Ride Flow (Rider → Driver)
```
1. Rider opens "Book a Ride"
2. Enters pickup (Nominatim autocomplete) + destination
3. Selects vehicle type, enters offered fare
4. Optionally applies promo code
5. Submits → Ride created (status: pending)
6. Driver dashboard shows available ride
7. Driver places bid with their price
8. Rider sees bids, accepts best one
9. Ride status: accepted → driver_arrived → in_progress → completed
10. Both rate each other (1–5 stars)
```

### SOS Flow
```
1. Rider/Driver taps SOS button during active ride
2. GPS coordinates sent to server
3. All admin accounts receive notification
4. Admin sees SOS on dashboard with map link
5. Admin resolves after attending to situation
```

---

## 🌐 API Endpoints

| Method | URL | Description |
|--------|-----|-------------|
| GET | `/` | Landing page |
| POST | `/auth/otp` | Request OTP |
| POST | `/auth/verify` | Verify OTP |
| POST | `/auth/register` | Create account |
| GET | `/rider/dashboard` | Rider home |
| POST | `/rider/request-ride` | Create ride |
| POST | `/rider/accept-bid/{ride}/{bid}` | Accept driver bid |
| GET | `/rider/bids/{rideId}` | Poll bids (AJAX) |
| GET | `/rider/driver-location/{ride}` | Get driver GPS (AJAX) |
| GET | `/chat/{rideId}/{lastId}` | Poll chat messages |
| POST | `/chat/{rideId}` | Send chat message |
| POST | `/sos` | Trigger SOS alert |
| GET | `/driver/dashboard` | Driver home |
| POST | `/driver/bid/{rideId}` | Place ride bid |
| POST | `/driver/update-status/{id}` | Update ride status |
| POST | `/driver/location` | Update driver GPS |
| GET | `/admin/dashboard` | Admin overview |
| POST | `/admin/drivers/{id}/approve` | Approve driver |
| POST | `/promo/validate` | Validate promo code |
| GET | `/notifications` | Get notifications (AJAX) |

---

## 🚀 Deployment

### cPanel / Shared Hosting
```bash
1. Upload files to /public_html/brocar/
2. Point document root to /public_html/brocar/public/
3. Import database, run migrations
4. Update .env with production values
5. php artisan config:cache && php artisan route:cache
```

### VPS (Ubuntu + Nginx)
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/brocar/public;

    index index.php;
    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Production Checklist
- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] Strong `APP_KEY` generated
- [ ] SSL certificate (HTTPS)
- [ ] `php artisan config:cache`
- [ ] `php artisan route:cache`
- [ ] `php artisan view:cache`
- [ ] Storage symlink: `php artisan storage:link`
- [ ] File permissions: `chmod -R 775 storage bootstrap/cache`
- [ ] Cron for scheduled rides: `* * * * * php artisan schedule:run`
- [ ] (Optional) SMS API integration in `AuthController`

---

## 📞 Support & Contact

- **Platform:** BROCAR Nepal
- **Email:** admin@brocar.np
- **Version:** 1.0.0
- **Laravel:** 12.x | **PHP:** 8.2+

---

*Built with ❤️ in Nepal 🇳🇵 — Powered by Laravel 12*
