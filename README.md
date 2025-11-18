# 🎓 GUVI Login/Register Application

A complete user authentication and profile management system built with PHP, MySQL, Redis, and MongoDB.

## ✨ Features

- **User Registration** with validation and password hashing
- **User Login** with session management
- **User Profile** with avatar, badges, and real-time updates
- **Auto-age calculation** from date of birth
- **Responsive Design** with Bootstrap 5 and custom yellow-green theme
- **Multi-database Architecture**:
  - MySQL: User authentication (relational data)
  - Redis: Session storage (7-day TTL)
  - MongoDB: User profiles (document storage)

## 🛠️ Technology Stack

### Backend
- PHP 8.x
- MySQL 8.0+ (user authentication)
- Redis 6.0+ (session management)
- MongoDB 6.0+ (profile storage)

### Frontend
- HTML5, CSS3, JavaScript
- Bootstrap 5.3.3
- jQuery 3.7.1 (AJAX)
- Custom yellow-green theme (#F0E491, #BBC863, #658C58, #31694E)

### Security
- Password hashing with PHP `password_hash()`
- Prepared statements (SQL injection protection)
- Session management with Redis TTL

## 📁 Project Structure

```
├── index.php                 # Router for PHP built-in server
├── start-server.bat          # Windows development launcher
├── start-wsl.sh              # Linux development launcher
├── deploy-ec2.sh             # AWS EC2 deployment script
├── .env.example              # Environment variables template
│
├── public/                   # Frontend pages
│   ├── index.html           # Landing page
│   ├── login.html           # Login page
│   ├── register.html        # Registration page
│   └── profile.html         # User profile page
│
├── css/
│   └── style.css            # Custom styles (yellow-green theme)
│
├── js/                      # Frontend logic
│   ├── login.js            # Login functionality
│   ├── register.js         # Registration functionality
│   └── profile.js          # Profile + auto-age calculation
│
├── php/                     # Backend API
│   ├── login.php           # Login endpoint
│   ├── register.php        # Registration endpoint
│   ├── profile.php         # Profile CRUD endpoint
│   ├── utils.php           # Utility functions
│   │
│   └── db/                 # Database layer
│       ├── config.php      # Environment-based configuration
│       ├── db.php          # Database connection functions
│       ├── schema.sql      # MySQL schema
│       ├── mongo-init.sh   # MongoDB initialization
│       ├── diagnostics.php # Database connection test
│       └── wsl-check.sh    # Service verification script
│
└── assets/                  # Static assets (if any)
```

## 🚀 Quick Start (Development)

### Prerequisites
- PHP 8.x with extensions: pdo_mysql, redis, mongodb
- MySQL 8.0+
- Redis 6.0+
- MongoDB 6.0+

### Windows (WSL Setup)
1. Start WSL databases (MySQL, Redis, MongoDB running on WSL)
2. Run: `start-server.bat`
3. Access: `http://localhost:8000`

### Linux
1. Ensure all services are running
2. Run: `bash start-wsl.sh`
3. Access: `http://localhost:8000`

### Database Setup
```bash
# Initialize MySQL
mysql -u root -p < php/db/schema.sql

# Initialize MongoDB
bash php/db/mongo-init.sh

# Verify services
bash php/db/wsl-check.sh
```

## ☁️ AWS EC2 Deployment (Complete Guide)

### 📋 Step-by-Step Instructions

#### **Step 1: Launch EC2 Instance**

1. **Login to AWS Console**
   - Go to https://aws.amazon.com/console/
   - Navigate to EC2 Dashboard

2. **Launch Instance**
   - Click "Launch Instance"
   - **Name**: `guvi-app-server`
   - **OS**: Ubuntu 22.04 LTS (free tier eligible)
   - **Instance Type**: t2.small (recommended) or t2.micro (free tier)
   - **Key Pair**: Create new or select existing (download .pem file)

3. **Configure Security Group**
   - Click "Edit" under Network Settings
   - Add these inbound rules:
   ```
   Type: SSH        | Port: 22   | Source: My IP (your IP address)
   Type: HTTP       | Port: 80   | Source: Anywhere (0.0.0.0/0)
   Type: HTTPS      | Port: 443  | Source: Anywhere (0.0.0.0/0)
   ```

4. **Storage**: 20 GB (default is fine)

5. **Launch Instance** and wait for it to start (2-3 minutes)

6. **Note your Public IP**: Find it in EC2 Dashboard under "Public IPv4 address"

---

#### **Step 2: Connect to EC2**

**Windows (PowerShell):**
```powershell
# Change permission of your key file
icacls "C:\path\to\your-key.pem" /inheritance:r
icacls "C:\path\to\your-key.pem" /grant:r "$($env:USERNAME):(R)"

# Connect via SSH
ssh -i "C:\path\to\your-key.pem" ubuntu@YOUR_EC2_PUBLIC_IP
```

**Alternative: Use PuTTY (Windows)**
1. Download PuTTY and PuTTYgen
2. Convert .pem to .ppk using PuTTYgen
3. In PuTTY: Host = ubuntu@YOUR_EC2_IP, Port = 22
4. Under SSH > Auth, load your .ppk file

---

#### **Step 3: Upload Project Files**

**Option A: Using SCP (Recommended)**

```powershell
# From your local machine (Windows PowerShell)
# Navigate to project folder
cd "d:\Last Try\Log-in-Register"

# Upload entire project
scp -i "C:\path\to\your-key.pem" -r * ubuntu@YOUR_EC2_IP:~/guvi-app/
```

**Option B: Using WinSCP (Easier for Windows)**
1. Download WinSCP: https://winscp.net/
2. File Protocol: SFTP
3. Host: YOUR_EC2_PUBLIC_IP
4. Username: ubuntu
5. Advanced > SSH > Authentication > Private key: Select your .ppk file
6. Login and drag-drop all project files to `/home/ubuntu/guvi-app/`

**Option C: Using Git**
```bash
# On your local machine, push to GitHub first
cd "d:\Last Try\Log-in-Register"
git add .
git commit -m "Ready for deployment"
git push origin main

# Then on EC2 instance
git clone https://github.com/YOUR_USERNAME/YOUR_REPO.git ~/guvi-app
cd ~/guvi-app
```

---

#### **Step 4: Run Deployment Script**

```bash
# On EC2 instance (after uploading files)
cd ~/guvi-app
chmod +x deploy-ec2.sh
sudo ./deploy-ec2.sh
```

**What this script does:**
- ✅ Updates Ubuntu packages
- ✅ Installs Apache, PHP 8.x, MySQL, Redis, MongoDB
- ✅ Installs PHP extensions (pdo_mysql, redis, mongodb)
- ✅ Creates MySQL database `guvi_app` and user `guvi`
- ✅ Loads database schema from `php/db/schema.sql`
- ✅ Initializes MongoDB with indexes
- ✅ Configures Apache virtual host
- ✅ Copies files to `/var/www/guvi-app`
- ✅ Sets up environment variables
- ✅ Starts all services

**Installation takes 5-10 minutes.**

---

#### **Step 5: Verify Deployment**

```bash
# Check if all services are running
sudo systemctl status apache2
sudo systemctl status mysql
sudo systemctl status redis-server
sudo systemctl status mongod

# Get your public IP (if you forgot)
curl http://169.254.169.254/latest/meta-data/public-ipv4
```

**Test in Browser:**
- **Home**: `http://YOUR_EC2_IP/`
- **Diagnostics**: `http://YOUR_EC2_IP/php/db/diagnostics.php` (should show all ✓)
- **Register**: `http://YOUR_EC2_IP/public/register.html`
- **Login**: `http://YOUR_EC2_IP/public/login.html`

---

#### **Step 6: Change Default Password (CRITICAL!)**

```bash
# On EC2 instance
sudo nano /var/www/guvi-app/.env

# Change this line:
MYSQL_PASSWORD=CHANGE_THIS_PASSWORD
# To something strong like:
MYSQL_PASSWORD=MyStr0ng!P@ssw0rd2024

# Save (Ctrl+O, Enter, Ctrl+X)

# Update MySQL user password
sudo mysql
```

```sql
ALTER USER 'guvi'@'localhost' IDENTIFIED WITH mysql_native_password BY 'MyStr0ng!P@ssw0rd2024';
FLUSH PRIVILEGES;
EXIT;
```

```bash
# Update Apache environment
sudo nano /etc/apache2/envvars

# Find line:
export MYSQL_PASSWORD=Guvi@2024@Secure!
# Change to:
export MYSQL_PASSWORD=MyStr0ng!P@ssw0rd2024

# Restart Apache
sudo systemctl restart apache2
```

---

#### **Step 7: Setup SSL/HTTPS (Recommended)**

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-apache

# Get free SSL certificate (if you have a domain)
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com

# For IP-only setup, skip this step (use HTTP for now)
```

---

### 🔐 Security Checklist

After deployment, verify these:
- [ ] Changed MySQL password from default
- [ ] AWS Security Group only allows ports 22, 80, 443
- [ ] Removed database ports (3306, 6379, 27017) from public access
- [ ] Apache is running and accessible
- [ ] All database connections working (check diagnostics page)

---

### 🐛 Troubleshooting

**Problem: "Connection refused" when accessing EC2 IP**
```bash
# Check Apache status
sudo systemctl status apache2

# If not running, start it
sudo systemctl start apache2

# Check firewall
sudo ufw status
sudo ufw allow 'Apache Full'
```

**Problem: Database connection errors**
```bash
# Check MySQL
sudo systemctl status mysql
mysql -u guvi -p guvi_app  # Test login

# Check Redis
redis-cli ping  # Should return PONG

# Check MongoDB
mongosh --eval "db.adminCommand('ping')"  # Should return ok: 1
```

**Problem: 403 Forbidden error**
```bash
# Fix permissions
sudo chown -R www-data:www-data /var/www/guvi-app
sudo chmod -R 755 /var/www/guvi-app
```

**Problem: PHP extensions not loaded**
```bash
# Check loaded extensions
php -m | grep -E "redis|mongodb|mysql"

# Reinstall if missing
sudo apt install --reinstall php-redis php-mongodb php-mysql
sudo systemctl restart apache2
```

---

## 📊 Database Schema

### MySQL (guvi_app.users)
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Redis (Sessions)
```
Key: session:{token}
Value: {user_id, username, email}
TTL: 604800 seconds (7 days)
```

### MongoDB (guvi_app.profiles)
```javascript
{
    user_id: Number,        // Unique index
    first_name: String,
    last_name: String,
    dob: String,
    age: Number,            // Auto-calculated
    phone: String,
    address: String,
    updated_at: Date        // Index
}
```

## 🎨 Features Highlights

### Auto-Age Calculation
- Enter date of birth → age automatically calculated
- Updates in real-time (considers if birthday occurred this year)
- Stored in MongoDB profiles collection

### Profile Display
- Avatar with gradient background
- User badges (Member Since, Last Updated)
- Two-column responsive layout
- Real-time updates without page reload

### Session Management
- Redis-based sessions with 7-day TTL
- Automatic session validation
- localStorage for client-side persistence

## 📝 API Endpoints

### POST /php/register.php
Register new user
```javascript
{
    username: string,
    email: string,
    password: string
}
```

### POST /php/login.php
Authenticate user
```javascript
{
    username: string,
    password: string
}
```

### GET /php/profile.php
Get user profile (requires session token)

### POST /php/profile.php
Create/update profile (requires session token)
```javascript
{
    first_name: string,
    last_name: string,
    dob: string,      // YYYY-MM-DD
    phone: string,
    address: string
}
```

## 📦 GUVI Requirements ✅

- ✅ **Separation of Concerns**: PHP backend, HTML/CSS/JS frontend
- ✅ **jQuery AJAX**: All API calls use jQuery (no fetch API)
- ✅ **Bootstrap**: v5.3.3 with responsive design
- ✅ **Prepared Statements**: SQL injection prevention
- ✅ **localStorage**: Client-side session storage
- ✅ **Redis**: Session management with TTL
- ✅ **MySQL**: User authentication (relational)
- ✅ **MongoDB**: User profiles (document store)
- ✅ **Password Security**: PHP password_hash()

## 💰 AWS Cost Estimate

### Free Tier (First 12 Months):
- **t2.micro** EC2: FREE (750 hours/month)
- 20GB Storage: FREE
- 15GB Transfer: FREE
- **Total**: $0/month

### After Free Tier:
- **t2.small** EC2: ~$17/month
- 20GB Storage: ~$2/month
- **Total**: ~$20/month

## 📄 License

This project is created for GUVI internship assessment.

## 👨‍💻 Author

**GUVI Internship Project**  
Multi-database authentication system with responsive UI

---

**Deployment Time**: 30-45 minutes  
**Status**: ✅ Production Ready  
**For Help**: See DEPLOYMENT-GUIDE.md for detailed instructions
