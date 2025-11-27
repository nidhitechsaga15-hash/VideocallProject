# MySQL Database Setup Guide

## Current Status
Abhi SQLite use ho raha hai. MySQL ke liye configure karna hai.

## Quick Setup

### Option 1: Script Use Karein

```bash
./setup-mysql.sh
```

### Option 2: Manual Setup

#### Step 1: .env File Update Karein

`.env` file mein yeh changes karein:

```env
# SQLite ko comment karo
# DB_CONNECTION=sqlite

# MySQL ko uncomment karo aur values update karo
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=videocall_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

#### Step 2: MySQL Database Create Karein

```bash
mysql -u root -p
```

Phir MySQL mein:

```sql
CREATE DATABASE videocall_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

#### Step 3: Migrations Run Karein

```bash
php artisan config:clear
php artisan migrate
```

#### Step 4: Test Karein

```bash
php artisan tinker
```

Phir:
```php
DB::connection()->getPdo();
// Agar connection successful hai to "PDO Object" dikhega
```

## Common Issues

### ❌ "Access denied for user"
→ Username/password sahi hai? MySQL user ko database access hai?

### ❌ "Unknown database"
→ Database create kiya? Name sahi hai?

### ❌ "Connection refused"
→ MySQL service running hai?
```bash
sudo systemctl status mysql
# Ya
sudo service mysql status
```

## MySQL Service Start Karein (Agar nahi chal raha)

```bash
# Ubuntu/Debian
sudo systemctl start mysql
sudo systemctl enable mysql

# CentOS/RHEL
sudo systemctl start mysqld
sudo systemctl enable mysqld
```

## Database Backup

```bash
# Backup
mysqldump -u root -p videocall_db > backup.sql

# Restore
mysql -u root -p videocall_db < backup.sql
```

## Production Tips

1. **Strong Password**: Production mein strong password use karein
2. **Remote Access**: Agar remote access chahiye to MySQL bind-address check karein
3. **Backup**: Regular backups setup karein
4. **Indexes**: Large tables ke liye indexes add karein

