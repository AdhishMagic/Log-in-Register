# 🚀 AWS EC2 Deployment Guide

## Prerequisites

### AWS EC2 Instance Requirements
- **OS**: Ubuntu 22.04 LTS or Ubuntu 20.04 LTS
- **Instance Type**: t2.micro (minimum) or t2.small (recommended)
- **Storage**: 20GB minimum
- **Security Group**: Configure inbound rules:
  ```
  SSH (22)         - Your IP
  HTTP (80)        - 0.0.0.0/0
  HTTPS (443)      - 0.0.0.0/0
  ```

### Software Requirements
The deployment script will install:
- Apache 2.4+
- PHP 8.x with extensions: mysql, redis, mongodb, curl, xml, mbstring
- MySQL 8.0+
- Redis 6.0+
- MongoDB 6.0+

---

## 📦 Deployment Steps

### Step 1: Launch EC2 Instance

1. Log in to AWS Console → EC2 → Launch Instance
2. Select **Ubuntu 22.04 LTS** AMI
3. Choose **t2.small** instance type
4. Configure Security Group:
   - SSH (22) from your IP
   - HTTP (80) from anywhere
   - HTTPS (443) from anywhere
5. Create/select key pair for SSH access
6. Launch instance

### Step 2: Connect to EC2 Instance

```bash
# Windows (PowerShell)
ssh -i "your-key.pem" ubuntu@YOUR_EC2_PUBLIC_IP

# Or use PuTTY with your .ppk key
```

### Step 3: Upload Application Files

**Option A: Using SCP (from your Windows machine)**
```powershell
# Navigate to your project directory
cd "d:\Last Try\Log-in-Register"

# Upload entire project
scp -i "your-key.pem" -r * ubuntu@YOUR_EC2_PUBLIC_IP:~/guvi-app/
```

**Option B: Using Git**
```bash
# On EC2 instance
git clone https://github.com/yourusername/guvi-app.git ~/guvi-app
cd ~/guvi-app
```

**Option C: Using SFTP (FileZilla/WinSCP)**
1. Connect to EC2 using SFTP protocol
2. Upload all project files to `/home/ubuntu/guvi-app/`

### Step 4: Run Deployment Script

```bash
# On EC2 instance
cd ~/guvi-app
chmod +x deploy-ec2.sh
sudo ./deploy-ec2.sh
```

The script will:
- ✅ Install all required software (Apache, PHP, MySQL, Redis, MongoDB)
- ✅ Configure Apache virtual host
- ✅ Create database and user
- ✅ Initialize MySQL schema
- ✅ Setup MongoDB collections and indexes
- ✅ Configure environment variables
- ✅ Enable services and restart Apache

**Installation takes approximately 5-10 minutes.**

### Step 5: Verify Installation

```bash
# Get your public IP
curl http://169.254.169.254/latest/meta-data/public-ipv4

# Check service status
sudo systemctl status apache2
sudo systemctl status mysql
sudo systemctl status redis-server
sudo systemctl status mongod
```

Visit in browser:
- **App**: `http://YOUR_EC2_PUBLIC_IP/`
- **Diagnostics**: `http://YOUR_EC2_PUBLIC_IP/php/db/diagnostics.php`

---

## 🔒 Post-Deployment Security

### 1. Change Default Passwords

```bash
# Edit environment file
sudo nano /var/www/guvi-app/.env

# Change these values:
MYSQL_PASSWORD=YOUR_STRONG_PASSWORD_HERE
```

Update MySQL password:
```bash
sudo mysql
ALTER USER 'guvi'@'localhost' IDENTIFIED WITH mysql_native_password BY 'YOUR_STRONG_PASSWORD_HERE';
FLUSH PRIVILEGES;
EXIT;
```

Update Apache environment:
```bash
sudo nano /etc/apache2/envvars
# Update MYSQL_PASSWORD line
sudo systemctl restart apache2
```

### 2. Setup SSL/HTTPS with Let's Encrypt

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-apache

# Get SSL certificate (replace with your domain)
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com

# Certbot will automatically:
# - Obtain certificate
# - Configure Apache for HTTPS
# - Setup auto-renewal
```

### 3. Configure MySQL Security

```bash
sudo mysql_secure_installation

# Answer prompts:
# - Set root password: YES
# - Remove anonymous users: YES
# - Disallow root login remotely: YES
# - Remove test database: YES
# - Reload privilege tables: YES
```

### 4. Restrict Database Access

```bash
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf

# Add/modify:
bind-address = 127.0.0.1

sudo systemctl restart mysql
```

### 5. Secure Redis

```bash
sudo nano /etc/redis/redis.conf

# Add password authentication:
requirepass YOUR_REDIS_PASSWORD

# Bind to localhost only:
bind 127.0.0.1

sudo systemctl restart redis-server
```

Update your app config:
```bash
sudo nano /var/www/guvi-app/.env
# Add: REDIS_PASSWORD=YOUR_REDIS_PASSWORD

sudo nano /etc/apache2/envvars
# Add: export REDIS_PASSWORD=YOUR_REDIS_PASSWORD
```

Update `php/db/db.php`:
```php
// Add authentication to redis_client() function
if (!empty(getenv('REDIS_PASSWORD'))) {
    $redis->auth(getenv('REDIS_PASSWORD'));
}
```

### 6. Update AWS Security Group

In AWS Console → EC2 → Security Groups:
- **Keep**: HTTP (80) from 0.0.0.0/0, HTTPS (443) from 0.0.0.0/0
- **Remove**: MySQL (3306), Redis (6379), MongoDB (27017) from public access
- **Keep**: SSH (22) restricted to your IP only

---

## 📊 Monitoring & Maintenance

### Check Application Logs

```bash
# Apache error logs
sudo tail -f /var/log/apache2/guvi-app-error.log

# Apache access logs
sudo tail -f /var/log/apache2/guvi-app-access.log

# MySQL logs
sudo tail -f /var/log/mysql/error.log
```

### Database Backups

```bash
# Create backup script
sudo nano /usr/local/bin/backup-guvi-db.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/home/ubuntu/backups"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

# MySQL backup
mysqldump -u guvi -p'Guvi@2024@Secure!' guvi_app > $BACKUP_DIR/mysql_$DATE.sql

# MongoDB backup
mongodump --db=guvi_app --out=$BACKUP_DIR/mongo_$DATE

# Compress backups
tar -czf $BACKUP_DIR/backup_$DATE.tar.gz $BACKUP_DIR/mysql_$DATE.sql $BACKUP_DIR/mongo_$DATE
rm -rf $BACKUP_DIR/mysql_$DATE.sql $BACKUP_DIR/mongo_$DATE

# Keep only last 7 days
find $BACKUP_DIR -type f -mtime +7 -delete

echo "Backup completed: backup_$DATE.tar.gz"
```

```bash
# Make executable
sudo chmod +x /usr/local/bin/backup-guvi-db.sh

# Add to crontab (daily at 2 AM)
sudo crontab -e
# Add: 0 2 * * * /usr/local/bin/backup-guvi-db.sh
```

### Performance Monitoring

```bash
# Check resource usage
htop

# Monitor disk space
df -h

# Check MySQL performance
sudo mysqladmin -u root -p status

# Redis info
redis-cli info
```

---

## 🔧 Troubleshooting

### Apache not starting
```bash
# Check configuration
sudo apache2ctl configtest

# View logs
sudo journalctl -u apache2 -n 50
```

### Database connection errors
```bash
# Test MySQL connection
mysql -u guvi -p'Guvi@2024@Secure!' guvi_app

# Test Redis
redis-cli ping

# Test MongoDB
mongosh --eval "db.adminCommand('ping')"
```

### PHP extensions not loaded
```bash
# Check loaded extensions
php -m | grep -E "redis|mongodb|mysql"

# Reinstall if missing
sudo apt install --reinstall php-redis php-mongodb php-mysql
sudo systemctl restart apache2
```

### Permission issues
```bash
# Fix ownership
sudo chown -R www-data:www-data /var/www/guvi-app

# Fix permissions
sudo chmod -R 755 /var/www/guvi-app
```

---

## 🌐 Custom Domain Setup

### 1. Point Domain to EC2
In your domain registrar (GoDaddy, Namecheap, etc.):
```
A Record: @ → YOUR_EC2_PUBLIC_IP
A Record: www → YOUR_EC2_PUBLIC_IP
```

### 2. Update Apache Config
```bash
sudo nano /etc/apache2/sites-available/guvi-app.conf

# Add to <VirtualHost *:80>:
ServerName yourdomain.com
ServerAlias www.yourdomain.com

sudo systemctl restart apache2
```

### 3. Get SSL Certificate
```bash
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com
```

---

## ✅ Production Checklist

- [ ] EC2 instance launched with Ubuntu 22.04
- [ ] Security group configured (SSH, HTTP, HTTPS only)
- [ ] Application files uploaded to `/var/www/guvi-app`
- [ ] Deployment script executed successfully
- [ ] All services running (Apache, MySQL, Redis, MongoDB)
- [ ] Application accessible via public IP
- [ ] Diagnostics page shows all connections OK
- [ ] Default passwords changed
- [ ] SSL/HTTPS configured
- [ ] MySQL security hardened
- [ ] Redis password protected
- [ ] Database backups scheduled
- [ ] AWS security group locked down (no DB ports public)
- [ ] Domain configured (if applicable)
- [ ] Monitoring setup

---

## 📈 Scaling Considerations

### For Production Traffic:
1. **Upgrade to t3.medium or t3.large** for better CPU performance
2. **Use AWS RDS** for MySQL (managed, auto-backup, high availability)
3. **Use AWS ElastiCache** for Redis (managed, scalable)
4. **Use AWS DocumentDB** or MongoDB Atlas for MongoDB (managed)
5. **Use Elastic Load Balancer** for multiple application servers
6. **Use AWS CloudFront** CDN for static assets
7. **Enable Auto Scaling** for traffic spikes

### Current Setup:
- ✅ Works for development and small-scale production
- ✅ Supports 100-500 concurrent users on t2.small
- ⚠️ All databases on same instance (single point of failure)
- ⚠️ No automatic failover or redundancy

---

## 📞 Support

For issues during deployment:
1. Check logs: `/var/log/apache2/guvi-app-error.log`
2. Run diagnostics: `http://YOUR_IP/php/db/diagnostics-detailed.php`
3. Verify services: `sudo systemctl status apache2 mysql redis-server mongod`

**Estimated deployment time**: 15-20 minutes (including SSL setup)
