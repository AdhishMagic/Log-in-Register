# Fireside Login

Restructured to:
- frontend/: public HTML/CSS/JS
- backend/: PHP endpoints
- backend/db/: central config and clients in `db.php` (PDO MySQL, Redis, MongoDB)

Frontend pages:
- /guvi-internship/frontend/login.html
- /guvi-internship/frontend/register.html
- /guvi-internship/frontend/profile.html

Endpoints:
- /guvi-internship/backend/register.php
- /guvi-internship/backend/login.php
- /guvi-internship/backend/profile.php

Requires MySQL, Redis, MongoDB (configure in backend/db/config.php or via env).
Endpoints now `require backend/db/db.php` directly (older thin wrappers `mysql.php`, `redis.php`, `mongo.php` are no longer used).

Notes:
- The app name and UI have been refreshed to a warm, welcoming theme called “Fireside Login.”
- The `docs/` folder contains optional setup notes; `.github/` contains Copilot guidance for contributors. Neither is required at runtime.
