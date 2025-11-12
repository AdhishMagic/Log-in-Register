# Fireside Login

Warm, modern login/register/profile starter built with PHP (no framework), MySQL, Redis, and MongoDB — featuring an accessible, responsive UI with a cozy “Fireside” theme.

## Highlights

- Clean separation: static frontend (HTML/CSS/JS) + simple PHP backend endpoints
- Auth flow: register → login → token stored in Redis → profile CRUD in MongoDB
- Friendly UI: warm gradient, glass cards, responsive navbar, spinners, validation, ARIA hints
- Zero framework: easy to read, extend, and deploy for learning or small projects

## Tech stack

- Frontend: HTML5, Bootstrap 5, jQuery, custom CSS
- Backend: PHP 8+ (built-in server ok)
- Databases: MySQL (users), Redis (session tokens), MongoDB (profiles)
- PHP extensions: pdo_mysql, redis, mongodb

## Project structure

```
backend/
	login.php          # POST /backend/login.php — issue a session token
	register.php       # POST /backend/register.php — create a new user
	profile.php        # GET/POST /backend/profile.php — read/update profile
	utils.php          # JSON helpers, token, validation
	db/
		config.php       # central configuration (env overrides supported)
		db.php           # PDO MySQL, Redis, MongoDB clients + helpers
		schema.sql       # MySQL schema (users table)
		diagnostics.php  # quick connectivity check (JSON)
frontend/
	login.html, register.html, profile.html
	css/style.css
	js/login.js, js/register.js, js/profile.js
README.md
```

## Requirements

- PHP 8.1+ with extensions: pdo_mysql, redis, mongodb
- MySQL 8+ (or MariaDB 10.5+)
- Redis 6+
- MongoDB 6+

## Configuration

All settings live in `backend/db/config.php` and can be overridden via environment variables.

- MySQL
	- MYSQL_HOST (default 127.0.0.1)
	- MYSQL_PORT (default 3306)
	- MYSQL_DB   (default guvi_app)
	- MYSQL_USER (default root)
	- MYSQL_PASSWORD (default empty)
- Redis
	- REDIS_HOST (default 127.0.0.1)
	- REDIS_PORT (default 6379)
	- REDIS_DB   (default 0)
	- REDIS_PASSWORD (optional)
	- SESSION_TTL (default 604800 seconds = 7 days)
- MongoDB
	- MONGO_URI (default mongodb://127.0.0.1:27017)
	- MONGO_DB (default guvi_app)
	- MONGO_COLLECTION (default profiles)

## Quick start (Windows PowerShell)

1) Ensure services are running (MySQL, Redis, MongoDB) and PHP extensions installed.

2) Create database and table (use any MySQL client), or run the SQL below:

```
CREATE DATABASE IF NOT EXISTS guvi_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE guvi_app;
CREATE TABLE IF NOT EXISTS users (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	username VARCHAR(32) NOT NULL UNIQUE,
	email VARCHAR(255) NOT NULL UNIQUE,
	password_hash VARCHAR(255) NOT NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

3) (Recommended) Use a dedicated MySQL app user instead of root:

```
CREATE USER 'guvi'@'127.0.0.1' IDENTIFIED WITH mysql_native_password BY 'StrongPass123!';
GRANT ALL ON guvi_app.* TO 'guvi'@'127.0.0.1';
FLUSH PRIVILEGES;
```

4) Start the PHP server from the project root:

```powershell
# Optional: set env overrides for this session
$env:MYSQL_USER = "guvi"; $env:MYSQL_PASSWORD = "StrongPass123!"
$env:MYSQL_DB = "guvi_app"; $env:MONGO_URI = "mongodb://127.0.0.1:27017"

php -S 127.0.0.1:8000 -t .
```

5) Open the app:

- Register: http://127.0.0.1:8000/frontend/register.html
- Login:    http://127.0.0.1:8000/frontend/login.html
- Profile:  http://127.0.0.1:8000/frontend/profile.html

6) Optional: check connectivity status (JSON):

- http://127.0.0.1:8000/backend/db/diagnostics.php

## API overview

- POST `/backend/register.php`
	- form fields: username, email, password
	- 200: `{ success: true, message: "Registered" }`
	- errors: 400/409 (validation/conflict)

- POST `/backend/login.php`
	- form fields: username, password
	- 200: `{ success: true, token, user: { username, email } }`
	- token is stored in Redis: `session:<token>` with TTL (sliding on use)

- GET `/backend/profile.php`
	- headers: `X-Session-Token: <token>` (or `?token=`)
	- 200: `{ success: true, user: { username, email }, profile: {...} }`

- POST `/backend/profile.php`
	- headers: `X-Session-Token: <token>`
	- body (JSON): `{ profile: { age, dob, contact, address } }`
	- 200: `{ success: true, message: "Updated" }`

## Troubleshooting

- MySQL “Access denied” using root on Linux/WSL
	- Create an app user (`guvi` example above) and set `MYSQL_USER`/`MYSQL_PASSWORD`
	- Or switch root to `mysql_native_password`

- MySQL “Unknown database”
	- Create the DB and run `backend/db/schema.sql`
	- Or point `MYSQL_DB` to an existing database

- Redis/Mongo “connection refused”
	- Ensure services are running and listening on 127.0.0.1
	- Check firewall/ports; verify via `redis-cli ping` / `mongosh --eval 'db.runCommand({ ping: 1 })'`

## Notes and limits

- Educational starter, not production-hardened (no HTTPS, CSRF, rate-limits, auditing)
- Tokens are bearer-style and kept in Redis; protect them and use HTTPS in real deployments
- CORS is not configured; the static frontend and backend are served from the same origin by default

## License

MIT (or your preferred license). Add a LICENSE file if distributing.
