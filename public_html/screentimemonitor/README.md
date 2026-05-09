# ScreenMonitor — Employee Monitoring System

A production-ready, browser-based employee monitoring web application built with **PHP, MySQL, HTML, CSS, and JavaScript**. Designed for shared hosting (cPanel/Apache).

---

## Features

- **🔐 Authentication** — Secure login/logout with PHP sessions & bcrypt password hashing
- **🖥️ Screen Capture** — Browser-based screenshots via `getDisplayMedia()` at configurable intervals
- **🖱️ Activity Tracking** — Mouse clicks, keyboard activity, and idle time detection
- **⚙️ Dynamic Config** — Admin-controlled screenshot intervals, updated without page reload
- **📊 Admin Dashboard** — Real-time employee status, screenshot timelines, Chart.js activity graphs
- **📤 CSV Export** — Download employee activity reports
- **🌙 Dark/Light Mode** — Theme toggle with localStorage persistence
- **🗑️ Auto-Cleanup** — Cron job to delete old screenshots based on retention settings

---

## Quick Start (Step-by-Step)

### 1. Upload Files

Upload the entire `ScreenMonitor` folder to your web hosting root (e.g., `public_html/` or a subdirectory).

### 2. Create the Database

1. Log in to **phpMyAdmin** (via cPanel)
2. Create a new database (e.g., `screen_monitor`)
3. Select the database, go to **Import** tab
4. Upload `sql/schema.sql` and click **Go**

### 3. Configure Database Connection

Edit `config/database.php` and update these values:

```php
define('DB_HOST', '193.203.184.197');   // Your MySQL host
define('DB_NAME', 'screen_monitor');     // Your database name
define('DB_USER', 'your_username');      // Your MySQL username
define('DB_PASS', 'your_password');      // Your MySQL password
```

### 4. Set Directory Permissions

```bash
chmod 755 uploads/
chmod 755 uploads/screenshots/
```

Or set via cPanel File Manager → Permissions → 755 for the `uploads` folder.

### 5. Run Setup

Open your browser and navigate to:

```
https://yourdomain.com/setup.php
```

This will:
- Create the default admin account
- Create a test employee account
- Verify the uploads directory

### 6. Login

Navigate to `https://yourdomain.com/` (or `/index.php`)

| Role     | Email              | Password      |
|----------|--------------------|---------------|
| Admin    | admin@monitor.com  | admin123      |
| Employee | john@monitor.com   | employee123   |

### 7. Delete setup.php

⚠️ **IMPORTANT**: Delete `setup.php` from your server immediately after setup!

---

## File Structure

```
ScreenMonitor/
├── .htaccess                    # Security & URL rules
├── index.php                    # Login page
├── dashboard.php                # Employee monitoring dashboard
├── admin.php                    # Admin panel
├── setup.php                    # First-time setup (DELETE after use)
├── cron_cleanup.php             # Automated screenshot cleanup
│
├── config/
│   └── database.php             # Database connection (PDO)
│
├── includes/
│   ├── auth.php                 # Session & role management
│   └── helpers.php              # Utility functions
│
├── api/
│   ├── login.php                # POST - Authenticate user
│   ├── logout.php               # POST - End session
│   ├── upload_screenshot.php    # POST - Save screenshot
│   ├── activity_log.php         # POST - Log activity data
│   ├── get_config.php           # GET  - Fetch settings
│   ├── get_employees.php        # GET  - List employees (admin)
│   ├── get_screenshots.php      # GET  - Employee screenshots (admin)
│   ├── get_activity.php         # GET  - Activity charts data (admin)
│   ├── update_config.php        # POST - Update settings (admin)
│   └── export_report.php        # GET  - CSV download (admin)
│
├── assets/
│   ├── css/
│   │   └── style.css            # Complete stylesheet
│   └── js/
│       ├── app.js               # Employee: capture & tracking
│       ├── admin.js             # Admin: dashboard logic
│       └── chart-config.js      # Chart.js configuration
│
├── uploads/
│   └── screenshots/             # Stored: {user_id}/{date}/{file}.jpg
│
└── sql/
    └── schema.sql               # Database schema
```

---

## Admin Panel Features

| Section       | Description                                           |
|---------------|-------------------------------------------------------|
| **Employees** | Live grid of all employees with status, hours, login time |
| **Screenshots** | Browse screenshots per employee per date with lightbox |
| **Activity**  | Chart.js graphs: mouse clicks, keyboard, idle time    |
| **Settings**  | Configure screenshot interval, retention, idle threshold |

---

## API Endpoints

| Method | Endpoint                    | Auth     | Description              |
|--------|-----------------------------|----------|--------------------------|
| POST   | `/api/login.php`            | None     | Authenticate user        |
| POST   | `/api/logout.php`           | Session  | End session              |
| POST   | `/api/upload_screenshot.php`| Session  | Upload screenshot (base64)|
| POST   | `/api/activity_log.php`     | Session  | Send activity metrics    |
| GET    | `/api/get_config.php`       | Session  | Get system settings      |
| GET    | `/api/get_employees.php`    | Admin    | List all employees       |
| GET    | `/api/get_screenshots.php`  | Admin    | Get screenshots by user  |
| GET    | `/api/get_activity.php`     | Admin    | Get activity chart data  |
| POST   | `/api/update_config.php`    | Admin    | Update system settings   |
| GET    | `/api/export_report.php`    | Admin    | Download CSV report      |

---

## Cron Job (Auto-Cleanup)

Set up a cron job to automatically delete old screenshots:

```bash
# Run daily at 2:00 AM
0 2 * * * php /path/to/ScreenMonitor/cron_cleanup.php >> /path/to/cleanup.log 2>&1
```

In cPanel: Go to **Cron Jobs** → Add the command above.

---

## Security

- ✅ PDO prepared statements (SQL injection protection)
- ✅ bcrypt password hashing
- ✅ PHP session-based authentication
- ✅ Input sanitization (XSS prevention)
- ✅ Directory access restrictions via `.htaccess`
- ✅ Security headers (X-Content-Type-Options, X-Frame-Options, etc.)
- ✅ HTTP-only session cookies
- ✅ File type validation for uploads
- ✅ Admin-only API restrictions

---

## Requirements

- **PHP** 7.4+ (with PDO, GD extensions)
- **MySQL** 5.7+ / MariaDB 10.3+
- **Apache** with mod_rewrite enabled
- **HTTPS/SSL** (required for screen capture in Chrome)
- **Chrome** browser (primary target)

---

## Important Notes

1. **HTTPS Required**: `getDisplayMedia()` only works over HTTPS. Ensure your hosting has SSL.
2. **Chrome Recommended**: Screen capture API works best in Chrome/Chromium.
3. **Change Default Passwords**: Update admin and employee passwords immediately after deployment.
4. **Storage**: Each screenshot is ~50-200KB (JPEG, quality 0.6). Plan storage accordingly.
5. **No Webcam**: This system captures screens only — no camera functionality.

---

## License

This project is proprietary. All rights reserved.
