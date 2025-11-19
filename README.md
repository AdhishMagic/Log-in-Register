# GUVI Internship User Authentication System

**Deployment:** [Live Demo](http://13.53.176.199)

This project is a complete user authentication system built for the GUVI Internship requirements. It provides a registration page, login page, and profile page with full CRUD support for user details.

---

## Core Flow

1. **Register:**  
   Users sign up with basic information. Data is securely stored in MySQL using prepared statements.

2. **Login:**  
   Authentication uses AJAX—no traditional form submission.  
   - Session is stored in browser `localStorage`.
   - Backend session info is kept in Redis.

3. **Profile Page:**  
   Once logged in, users are redirected to a profile page to view and update details:
   - Name
   - Age
   - Date of Birth
   - Contact info
   - Other personal details

---

## Important Requirements Implemented

- **Separation of Concerns:**  
  HTML, CSS, JS, and PHP code are all in separate files.
- **AJAX for Backend Communication:**  
  All backend communication uses jQuery AJAX (no form submits).
- **Bootstrap Responsive UI:**  
  All UI uses Bootstrap for seamless responsiveness.
- **Secure MySQL Queries:**  
  Only prepared statements are used (no raw queries).
- **Sessions:**
  - Backend: Redis
  - Frontend: localStorage
- **MongoDB Support:**  
  Optionally used for extended profile metadata, per GUVI specs.
- **Organized Folder Structure:**  
  Codebase is neatly arranged by responsibility.

---

## Tech Stack

- **Frontend:**  
  HTML, CSS, Bootstrap, JavaScript, jQuery AJAX

- **Backend:**  
  PHP

- **Databases:**  
  MySQL, Redis, MongoDB

- **Session Handling:**  
  Redis (backend) + Browser localStorage (frontend)

---

## Expected Directory Structure

```
/assets
    /css
    /js
    /images

/backend
    register.php
    login.php
    update_profile.php
    fetch_profile.php
    redis_config.php
    db_config.php

/views
    register.html
    login.html
    profile.html
```

---

## Features

- User registration with validation
- Login authentication via prepared statements
- Session handling using Redis + localStorage
- Update profile details through AJAX calls
- Responsive UI powered by Bootstrap
- Well-structured, easily navigable codebase

---

**Deployment:** [http://13.53.176.199](http://13.53.176.199)
