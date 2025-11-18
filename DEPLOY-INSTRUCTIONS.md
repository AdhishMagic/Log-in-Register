# 🚀 Quick Deployment Instructions for GUVI Evaluators

## ⚡ Super Quick Deploy (Copy-Paste These 3 Commands)

```bash
# 1. After uploading files to EC2, SSH into it:
ssh -i "your-key.pem" ubuntu@YOUR_EC2_IP

# 2. Navigate to project and make script executable:
cd ~/guvi-app && chmod +x deploy-ec2.sh

# 3. Run automated deployment (installs everything):
sudo ./deploy-ec2.sh
```

**Done! App will be live at `http://YOUR_EC2_IP` in 10 minutes.**

---

## 📋 What You Need Before Starting

### AWS Requirements:
- ✅ AWS Account (free tier works fine)
- ✅ EC2 instance running Ubuntu 22.04 LTS
- ✅ Instance type: t2.micro (free) or t2.small (recommended)
- ✅ Security group with ports: 22 (SSH), 80 (HTTP), 443 (HTTPS)
- ✅ SSH key pair (.pem file downloaded)

### Your Computer Needs:
- ✅ SSH client (built-in on Windows 10+/Linux/Mac)
- ✅ Or PuTTY for Windows (if you prefer GUI)
- ✅ Or WinSCP/FileZilla for file upload (optional, easier)

---

## 📦 Step 1: Upload Project to EC2

### Method A: Using SCP (Command Line)

**Windows PowerShell:**
```powershell
cd "d:\Last Try\Log-in-Register"
scp -i "C:\path\to\your-key.pem" -r * ubuntu@YOUR_EC2_IP:~/guvi-app/
```

**Linux/Mac:**
```bash
cd /path/to/project
scp -i ~/path/to/your-key.pem -r * ubuntu@YOUR_EC2_IP:~/guvi-app/
```

### Method B: Using WinSCP (Windows GUI - Easier!)

1. Download WinSCP: https://winscp.net/
2. New Site:
   - File Protocol: **SFTP**
   - Host: **YOUR_EC2_IP**
   - Port: **22**
   - Username: **ubuntu**
3. Advanced > SSH > Authentication:
   - Private key: Select your **.ppk** file (convert .pem with PuTTYgen if needed)
4. Login and drag-drop entire project folder to `/home/ubuntu/guvi-app/`

### Method C: Using Git (If you pushed to GitHub)

```bash
# On EC2 instance
git clone https://github.com/YOUR_USERNAME/YOUR_REPO.git ~/guvi-app
cd ~/guvi-app
```

---

## 🔧 Step 2: Connect to EC2 and Deploy

### Connect via SSH:

**Windows:**
```powershell
ssh -i "C:\path\to\your-key.pem" ubuntu@YOUR_EC2_IP
```

**Linux/Mac:**
```bash
ssh -i ~/path/to/your-key.pem ubuntu@YOUR_EC2_IP
```

### Run Deployment:

```bash
cd ~/guvi-app
chmod +x deploy-ec2.sh
sudo ./deploy-ec2.sh
```

**The script will:**
- Install Apache, PHP 8.x, MySQL, Redis, MongoDB (takes ~5 minutes)
- Configure all services automatically
- Create database and load schema
- Set up environment variables
- Start the application

**Wait for:** "✅ Installation Complete!" message

---

## ✅ Step 3: Verify Everything Works

### Check Services:
```bash
# All should show "active (running)"
sudo systemctl status apache2
sudo systemctl status mysql
sudo systemctl status redis-server
sudo systemctl status mongod
```

### Test in Browser:

1. **Get your EC2 IP:**
   ```bash
   curl http://169.254.169.254/latest/meta-data/public-ipv4
   ```

2. **Visit these URLs:**
   - Home: `http://YOUR_EC2_IP/`
   - Diagnostics: `http://YOUR_EC2_IP/php/db/diagnostics.php` (should show all ✓)
   - Register: `http://YOUR_EC2_IP/public/register.html`
   - Login: `http://YOUR_EC2_IP/public/login.html`

---

## 🔒 Step 4: Change Default Password (Important!)

```bash
# Edit environment file
sudo nano /var/www/guvi-app/.env

# Change this line:
MYSQL_PASSWORD=Guvi@2024@Secure!
# To your strong password:
MYSQL_PASSWORD=YourStrongPassword123!

# Save: Ctrl+O, Enter, Ctrl+X
```

```bash
# Update MySQL password
sudo mysql -e "ALTER USER 'guvi'@'localhost' IDENTIFIED WITH mysql_native_password BY 'YourStrongPassword123!'; FLUSH PRIVILEGES;"

# Update Apache environment
sudo sed -i 's/Guvi@2024@Secure!/YourStrongPassword123!/g' /etc/apache2/envvars

# Restart Apache
sudo systemctl restart apache2
```

---

## 🌐 Optional: Add Custom Domain

If you have a domain (like myapp.com):

### 1. Point Domain to EC2:
In your domain registrar (GoDaddy, Namecheap, etc.):
```
A Record: @ → YOUR_EC2_IP
A Record: www → YOUR_EC2_IP
```

### 2. Update Apache:
```bash
sudo nano /etc/apache2/sites-available/guvi-app.conf

# Add these lines inside <VirtualHost *:80>:
ServerName yourdomain.com
ServerAlias www.yourdomain.com

# Save and restart
sudo systemctl restart apache2
```

### 3. Get Free SSL (HTTPS):
```bash
sudo apt install -y certbot python3-certbot-apache
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com

# Follow prompts (provide email, agree to terms)
# Certificate auto-renews every 90 days
```

---

## 🐛 Troubleshooting

### Problem: "Connection refused" when accessing EC2 IP

**Solution:**
```bash
# Check AWS Security Group allows port 80
# In AWS Console → EC2 → Security Groups → Inbound Rules
# Should have: HTTP (80) from 0.0.0.0/0

# Check Apache is running
sudo systemctl status apache2
sudo systemctl start apache2  # If not running
```

### Problem: Database connection errors

**Solution:**
```bash
# Test MySQL
mysql -u guvi -p'Guvi@2024@Secure!' guvi_app

# Test Redis
redis-cli ping  # Should return PONG

# Test MongoDB
mongosh --eval "db.adminCommand('ping')"  # Should return ok: 1

# Restart all services
sudo systemctl restart mysql redis-server mongod apache2
```

### Problem: 403 Forbidden error

**Solution:**
```bash
sudo chown -R www-data:www-data /var/www/guvi-app
sudo chmod -R 755 /var/www/guvi-app
sudo systemctl restart apache2
```

### Problem: PHP extensions not loaded

**Solution:**
```bash
php -m | grep -E "redis|mongodb|mysql"  # Check what's loaded

# Reinstall extensions
sudo apt install --reinstall php-redis php-mongodb php-mysql
sudo systemctl restart apache2
```

---

## 📊 What Gets Installed

| Software | Version | Purpose |
|----------|---------|---------|
| Apache | 2.4+ | Web server |
| PHP | 8.1+ | Backend language |
| MySQL | 8.0+ | User authentication database |
| Redis | 6.0+ | Session storage |
| MongoDB | 6.0+ | Profile data storage |
| PHP Extensions | pdo_mysql, redis, mongodb | Database connections |

**Total disk space:** ~800 MB  
**Installation time:** 5-10 minutes  
**Memory usage:** ~400 MB (idle)

---

## 💰 Cost Estimate

### Free Tier (First 12 months):
- t2.micro EC2: **FREE** (750 hours/month)
- 20GB Storage: **FREE**
- 1GB Data Transfer: **FREE**
- **Total: $0/month**

### After Free Tier:
- t2.small EC2: **~$17/month**
- 20GB Storage: **~$2/month**
- **Total: ~$20/month**

---

## ✅ Deployment Checklist

- [ ] EC2 instance running (Ubuntu 22.04)
- [ ] Security group configured (ports 22, 80, 443)
- [ ] SSH key downloaded (.pem file)
- [ ] Project files uploaded to `/home/ubuntu/guvi-app/`
- [ ] `deploy-ec2.sh` executed successfully
- [ ] All services running (Apache, MySQL, Redis, MongoDB)
- [ ] App accessible at `http://YOUR_EC2_IP`
- [ ] Diagnostics page shows all ✓
- [ ] Default password changed
- [ ] Tested: Register → Login → Profile

---

## 📚 Additional Documentation

For more details, see:
- **README.md** - Complete project documentation
- **DEPLOYMENT-GUIDE.md** - Detailed deployment steps with screenshots
- **DEPLOYMENT-READINESS.md** - Architecture overview and scaling
- **GUVI-SUBMISSION.md** - Requirements compliance and testing guide

---

## 🎯 Expected Results

After successful deployment:
1. ✅ Home page loads with yellow-green theme
2. ✅ Can register new user (password gets hashed)
3. ✅ Can login (session stored in Redis)
4. ✅ Profile page shows/edits user data (stored in MongoDB)
5. ✅ Age auto-calculates from DOB
6. ✅ Data persists across page reloads
7. ✅ Responsive on mobile devices

---

**Need help?** Check diagnostics page: `http://YOUR_EC2_IP/php/db/diagnostics.php`

**Total deployment time: 15-20 minutes** ⏱️
