# Secure Registration & Profile System

A secure, responsive, full‑stack web application with Register, Login, and Profile flow.

- Frontend: Bootstrap 5 for UI + jQuery for AJAX. No inline JS/CSS, no form submissions.
- Backend: PHP (no PHP sessions), MySQL (prepared statements), Redis (session tokens), MongoDB (profile extras).

## Project structure

```
.
├── index.html
├── login.html
├── profile.html
├── register.html
├── css/
│   └── styles.css
├── js/
│   ├── login.js
│   ├── profile.js
│   └── register.js
└── php/
    ├── config.php
    ├── db.php
    ├── login.php
    ├── profile.php
    ├── register.php
    ├── schema.sql
    └── utils.php
```

## Prerequisites

- PHP 8.1+ with extensions:
  - pdo_mysql
  - redis (phpredis)
  - mongodb (ext-mongodb)
- MySQL 8+
- Redis 6+
- MongoDB 5+

## Configuration

Edit `php/config.php` or set environment variables before starting PHP:

- MySQL: `MYSQL_HOST`, `MYSQL_PORT`, `MYSQL_DB`, `MYSQL_USER`, `MYSQL_PASSWORD`
- Redis: `REDIS_HOST`, `REDIS_PORT`, `REDIS_DB`, `REDIS_PASSWORD`, `SESSION_TTL`
- MongoDB: `MONGO_URI`, `MONGO_DB`, `MONGO_COLLECTION`

Defaults assume all services on localhost with open auth.

## Database setup (MySQL)

Import schema:

```sql
SOURCE d:/guvi/php/schema.sql;
```

Or copy/paste contents of `php/schema.sql` in your MySQL client.

## Running locally (Windows PowerShell)

From this folder, start the PHP dev server:

```powershell
$env:MYSQL_HOST="127.0.0.1"; $env:MYSQL_DB="guvi_app"; $env:REDIS_HOST="127.0.0.1"; $env:MONGO_URI="mongodb://127.0.0.1:27017"; php -S localhost:8000 -t d:\guvi
```

Then open http://localhost:8000 in your browser.

## API behavior

- `php/register.php` (POST form-encoded): `username`, `email`, `password`
  - Creates user in MySQL using prepared statements and password_hash.
- `php/login.php` (POST form-encoded): `username`, `password`
  - Validates via MySQL, generates a random token, stores token→user in Redis with TTL, returns `{ token, user }`.
- `php/profile.php`:
  - GET with header `X-Session-Token: <token>` → returns `{ user, profile }` (user from Redis; profile from MongoDB by user_id).
  - POST JSON `{ action:"update", profile:{ age, dob, contact, address } }` with `X-Session-Token` → upserts profile in MongoDB.

## Security notes

- No PHP sessions; browser uses localStorage for the token.
- Passwords hashed with `password_hash()` (bcrypt/argon depending on PHP config).
- Prepared statements only (no string concatenation SQL).
- Token TTL is sliding: each profile call refreshes TTL.

## Key Compliance Checklist

- [x] HTML, JS, CSS, PHP in separate files
- [x] Only using jQuery AJAX for backend communication
- [x] Strictly no form submission
- [x] Forms styled using Bootstrap for responsiveness
- [x] Storing core registration data in MySQL
- [x] Using Prepared Statements in MySQL (no simple SQL)
- [x] Storing additional profile details in MongoDB
- [x] Maintaining login session via browser localstorage
- [x] No usage of PHP Session
- [x] Using Redis to store backend session information

## Troubleshooting

- Ensure `ext-redis` and `ext-mongodb` are enabled in php.ini
- Verify MySQL/MongoDB/Redis are running and accessible from PHP host
- Check browser devtools network tab for AJAX errors
