# Church Management System

A web-based platform for The Church of Pentecost, Oforikrom Central Assembly, for managing member records and Sunday service attendance registers.

## Overview

The system is built with a static HTML/JavaScript front end and a PHP/MySQL back end (PDO), organized into two core modules:

- **Member Management** — registers and maintains a searchable directory of church members, family/dependant records, and dashboard statistics.
- **Service Register** — records weekly attendance figures (by category), offerings/tithes, communion and Bible study numbers, and guest/visitor/new-convert details.

Authentication, session handling, and CSRF protection are shared across both modules.

## Features

- Secure login with bcrypt password hashing and session-fixation protection
- Self-service account signup gated by an admin-verified activation code, with brute-force lockout
- Member registration with server-side validation and auto-calculated age
- Member directory with search, ministry/status filters, and quick stats
- Home dashboard with membership totals and a 30-day growth chart
- Attendance register with per-service headcounts, financial totals, and linked guest/visitor/new-convert entries
- Paginated attendance history and record deletion
- CSRF-protected write operations (save/delete) via session-issued tokens

## Tech Stack

| Layer      | Technology                     |
|------------|---------------------------------|
| Front end  | HTML5, CSS3, vanilla JavaScript |
| Back end   | PHP 7.4+ (PDO)                  |
| Database   | MySQL 5.7+ / MariaDB 10.3+      |
| Charts     | Chart.js                        |

## Project Structure

```
├── Login.html, loginscript.js, loginstyle.css   # Authentication UI
├── login.php, register.php, logout.php          # Auth endpoints
├── db_config.php                                 # DB credentials & admin code
│
├── PAGES/
│   ├── Home_Page.html, dashboard_stats.php, graph_data.php
│   ├── Members.html, member_records.php
│   ├── Register_New_Member.html, save_member.php
│   └── CHURCH_REGISTER/
│       ├── Church_Register.html, Church_Register_Records.html
│       ├── get_attendance.php, get_attendance_records.php
│       ├── save_attendance.php, delete_attendance.php
│       └── db_connect.php
│
└── check_session.php                             # Session/auth guard + CSRF issuance
```

## Setup

1. **Create the database**
   Import the schema file(s) (e.g. `church_management_schema.sql`, `church_register.sql`) into MySQL/MariaDB.

2. **Configure database credentials**
   Edit `db_config.php` (and `db_connect.php` where applicable) with your host, database name, username, and password.

3. **Set the admin registration code**
   Update `ADMIN_REGISTRATION_CODE` in `db_config.php` to a private value known only to administrators. This code is required to activate new user accounts.

4. **Create the initial admin account**
   Generate a bcrypt hash with `password_hash('YourPassword', PASSWORD_BCRYPT)` and insert it into the `users` table, or sign up through the UI using the admin code.

5. **Serve the application**
   Deploy to a PHP-enabled web server (Apache/Nginx + PHP-FPM) with the file structure above preserved, and browse to `Login.html`.

## Security Notes

- All database queries use parameterized statements (PDO) to prevent SQL injection.
- Passwords are hashed with PHP's `password_hash()`/`password_verify()`.
- State-changing requests (save/delete) require a valid session and a matching CSRF token.
- Before production deployment: replace the default admin code and any placeholder credentials, and ensure `session.cookie_secure` and `session.cookie_httponly` are enabled.

## License

Internal use — The Church of Pentecost, Oforikrom Central Assembly.
