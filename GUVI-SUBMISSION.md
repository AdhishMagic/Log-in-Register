# 📦 GUVI Project Submission Guide

## ✅ Files Included in This Submission

### Core Application Files
```
index.php                    # Application router
start-server.bat            # Windows development server
start-wsl.sh               # Linux development server

public/                    # Frontend pages
├── index.html            # Landing page
├── login.html            # Login page
├── register.html         # Registration page
└── profile.html          # Profile page

css/
└── style.css             # Custom yellow-green theme

js/                       # Client-side logic
├── login.js             # Login functionality
├── register.js          # Registration functionality
└── profile.js           # Profile management + auto-age

php/                      # Backend API
├── login.php            # Login endpoint
├── register.php         # Registration endpoint
├── profile.php          # Profile CRUD endpoint
├── utils.php            # Helper functions
└── db/                  # Database layer
    ├── config.php       # Configuration (env-based)
    ├── db.php           # Database connections
    ├── schema.sql       # MySQL schema
    ├── mongo-init.sh    # MongoDB setup
    ├── diagnostics.php  # Connection tester
    └── wsl-check.sh     # Service health check
```

### Deployment Files
```
deploy-ec2.sh             # AWS EC2 automated deployment script
.env.example              # Environment variables template
DEPLOYMENT-GUIDE.md       # Complete deployment instructions
DEPLOYMENT-READINESS.md   # Quick deployment summary
README.md                 # Project documentation
GUVI-SUBMISSION.md        # This file
```

---

## 🎯 GUVI Requirements Compliance

| Requirement | Status | Implementation |
|------------|--------|----------------|
| **Separation of Concerns** | ✅ | PHP backend in `php/`, HTML/CSS/JS frontend in `public/`, `css/`, `js/` |
| **jQuery AJAX** | ✅ | All API calls in `js/*.js` use jQuery `.ajax()`, no fetch API |
| **Bootstrap Framework** | ✅ | Bootstrap 5.3.3 used throughout, responsive design |
| **Prepared Statements** | ✅ | PDO with prepared statements in `php/db/db.php`, prevents SQL injection |
| **localStorage** | ✅ | Session tokens stored in localStorage in all JS files |
| **Redis** | ✅ | Session management with 7-day TTL in `php/db/db.php` |
| **MySQL** | ✅ | User authentication, relational data storage |
| **MongoDB** | ✅ | User profiles with document storage and indexes |
| **Password Hashing** | ✅ | PHP `password_hash()` and `password_verify()` in `php/register.php`, `php/login.php` |
| **Responsive Design** | ✅ | Bootstrap grid system, mobile-first approach |

---

## 🚀 How to Run This Project

### Local Development (Windows)

**Prerequisites:**
- PHP 8.x with extensions: pdo_mysql, redis, mongodb
- MySQL 8.0+ running on WSL or Windows
- Redis 6.0+ running on WSL or Windows
- MongoDB 6.0+ running on WSL or Windows

**Steps:**
1. Extract project files to a folder
2. Start MySQL, Redis, MongoDB services
3. Import database: `mysql -u root -p < php/db/schema.sql`
4. Initialize MongoDB: `bash php/db/mongo-init.sh`
5. Run: `start-server.bat`
6. Access: `http://localhost:8000`

### Local Development (Linux/Mac)

**Steps:**
1. Extract project files
2. Ensure services running: `sudo systemctl start mysql redis-server mongod`
3. Import database: `mysql -u root -p < php/db/schema.sql`
4. Initialize MongoDB: `bash php/db/mongo-init.sh`
5. Run: `bash start-wsl.sh`
6. Access: `http://localhost:8000`

---

## ☁️ AWS EC2 Deployment Instructions

### Quick Deploy (3 Commands)

```bash
# 1. Upload to EC2
scp -i "your-key.pem" -r * ubuntu@YOUR_EC2_IP:~/guvi-app/

# 2. SSH to EC2
ssh -i "your-key.pem" ubuntu@YOUR_EC2_IP

# 3. Run deployment script
cd ~/guvi-app && chmod +x deploy-ec2.sh && sudo ./deploy-ec2.sh
```

**Deployment time: ~10 minutes**

### Detailed Deployment Steps

**See README.md** for complete step-by-step instructions including:
- EC2 instance setup
- Security group configuration
- File upload methods (SCP, WinSCP, Git)
- Password changes
- SSL/HTTPS setup
- Troubleshooting guide

---

## 🔐 Security Features

1. **Password Security**
   - BCrypt hashing via `password_hash(PASSWORD_BCRYPT)`
   - Automatic salt generation
   - Never stores plain-text passwords

2. **SQL Injection Prevention**
   - All queries use PDO prepared statements
   - Parameters bound separately from SQL
   - No string concatenation in queries

3. **Session Security**
   - Redis-based sessions with TTL (auto-expire)
   - Cryptographically secure tokens (32 bytes random)
   - Session validation on every protected request

4. **Input Validation**
   - Server-side validation in all PHP endpoints
   - Client-side validation with HTML5 + Bootstrap
   - Email format validation
   - Password strength requirements

---

## 🎨 Key Features

### 1. Auto-Age Calculation
- Enter DOB → age auto-calculated in real-time
- Considers if birthday passed this year
- Stored in MongoDB, displayed instantly

**Implementation:** `js/profile.js` - `calculateAge()` function

### 2. Multi-Database Architecture
- **MySQL**: Relational user data (id, username, email, password_hash)
- **Redis**: Fast session storage with TTL (7 days)
- **MongoDB**: Flexible profile documents (first_name, last_name, dob, age, phone, address)

**Why 3 databases?**
- MySQL: ACID transactions for critical auth data
- Redis: In-memory speed for sessions
- MongoDB: Schema-less flexibility for profile fields

### 3. Responsive UI with Custom Theme
- **Colors**: Yellow-green nature theme (#F0E491, #BBC863, #658C58, #31694E)
- **Layout**: Bootstrap grid, mobile-first
- **Components**: Gradient navbar, avatar circles, badges
- **Clean**: No hamburger menu, no breadcrumbs

### 4. Real-Time Profile Updates
- Save profile → instant display update (no page reload)
- AJAX requests with jQuery
- localStorage persistence

---

## 📊 Database Schemas

### MySQL: `guvi_app.users`
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Redis: Sessions
```
Key: session:{random_token}
Value: JSON {user_id, username, email}
TTL: 604800 seconds (7 days)
```

### MongoDB: `guvi_app.profiles`
```javascript
{
    user_id: 1,                    // Unique index
    first_name: "John",
    last_name: "Doe",
    dob: "1995-06-15",
    age: 29,                       // Auto-calculated
    phone: "+1234567890",
    address: "123 Main St",
    updated_at: ISODate("2024-11-18T...")  // Index
}
```

---

## 🧪 Testing the Application

### 1. Test Database Connections
Visit: `http://localhost:8000/php/db/diagnostics.php`

Should show:
```
✓ MySQL connection successful
✓ Redis connection successful
✓ MongoDB connection successful
```

### 2. Test Registration
1. Go to `/public/register.html`
2. Create account: username, email, password
3. Check MySQL: `SELECT * FROM users;`
4. Should see new user with hashed password

### 3. Test Login
1. Go to `/public/login.html`
2. Login with registered credentials
3. Check Redis: `redis-cli KEYS session:*`
4. Should see session key with your token

### 4. Test Profile
1. After login, go to `/public/profile.html`
2. Fill profile form with DOB
3. Age should auto-calculate
4. Save and verify data persists on reload
5. Check MongoDB: `db.profiles.findOne({user_id: 1})`

---

## 📁 What Was Removed from Project

Cleaned up unnecessary development files for GUVI submission:
- ❌ `README-NEW.md` (duplicate documentation)
- ❌ `SETUP-SUMMARY.md` (development notes)
- ❌ `WSL-DATABASE-SETUP.md` (local setup only)
- ❌ `composer.json` (not using Composer)

**Result:** Clean, production-ready codebase ready for evaluation.

---

## 💡 Code Highlights for Review

### Best Practices Demonstrated

**1. Environment-Based Configuration** (`php/db/config.php`)
```php
'host' => getenv('MYSQL_HOST') ?: '127.0.0.1',
'user' => getenv('MYSQL_USER') ?: 'root',
'pass' => getenv('MYSQL_PASSWORD') ?: '',
```
✅ No hardcoded credentials, deployment-friendly

**2. Prepared Statements** (`php/register.php`)
```php
$stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
$stmt->execute([$username, $email, $hash]);
```
✅ SQL injection prevention

**3. Password Hashing** (`php/register.php`)
```php
$hash = password_hash($password, PASSWORD_BCRYPT);
```
✅ Industry-standard BCrypt with automatic salting

**4. Session Management** (`php/db/db.php`)
```php
$redis->setex("session:$token", $ttl, json_encode($data));
```
✅ Auto-expiring sessions with Redis

**5. jQuery AJAX** (`js/login.js`)
```javascript
$.ajax({
    url: '/php/login.php',
    method: 'POST',
    data: { username, password },
    success: function(response) { ... }
});
```
✅ No fetch API, pure jQuery as required

---

## 📝 Evaluation Notes

### For GUVI Evaluators

**To quickly verify the project:**

1. **Check separation of concerns:**
   - Backend: `php/` folder (pure PHP, no HTML)
   - Frontend: `public/` folder (pure HTML)
   - Styles: `css/` folder
   - Scripts: `js/` folder

2. **Check jQuery usage:**
   - Search `js/` files for `$.ajax()` ✅
   - Search for `fetch(` - should find NONE ✅

3. **Check database integration:**
   - MySQL: `php/db/schema.sql` and usage in `php/register.php`, `php/login.php`
   - Redis: Session functions in `php/db/db.php`
   - MongoDB: Profile functions in `php/profile.php`

4. **Check security:**
   - `php/register.php` line ~25: `password_hash()` ✅
   - `php/login.php` line ~30: `password_verify()` ✅
   - All DB queries use prepared statements ✅

5. **Test deployment:**
   - Run `deploy-ec2.sh` on clean Ubuntu EC2
   - Should work without manual configuration
   - All services auto-installed and configured

---

## 📞 Contact & Support

**Project Type:** GUVI Internship Assessment  
**Topic:** Full-Stack Web Development  
**Technologies:** PHP, MySQL, Redis, MongoDB, Bootstrap, jQuery  
**Deployment:** AWS EC2 Ready  

For deployment questions, see:
- **README.md** - Complete project documentation
- **DEPLOYMENT-GUIDE.md** - Detailed deployment steps
- **DEPLOYMENT-READINESS.md** - Quick deployment summary

---

## ✅ Pre-Submission Checklist

Before submitting to GUVI:
- [x] All code files present and organized
- [x] README.md with clear instructions
- [x] Database schemas included (schema.sql, mongo-init.sh)
- [x] Deployment scripts tested and working
- [x] All GUVI requirements met (jQuery, Bootstrap, Redis, MySQL, MongoDB)
- [x] Security best practices implemented
- [x] Code comments where necessary
- [x] Responsive design verified
- [x] No hardcoded credentials in code
- [x] .env.example for configuration

---

**Project Status:** ✅ Ready for GUVI Submission  
**Deployment Status:** ✅ AWS EC2 Production-Ready  
**Documentation:** ✅ Complete
