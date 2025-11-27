#!/bin/bash

echo "=========================================="
echo "MySQL Database Configuration Setup"
echo "=========================================="
echo ""

read -p "MySQL Host [127.0.0.1]: " db_host
db_host=${db_host:-127.0.0.1}

read -p "MySQL Port [3306]: " db_port
db_port=${db_port:-3306}

read -p "Database Name: " db_name
read -p "MySQL Username: " db_user
read -s -p "MySQL Password: " db_pass
echo ""

# Backup .env
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)

# Update .env file
sed -i "s/DB_CONNECTION=.*/DB_CONNECTION=mysql/" .env
sed -i "s/# DB_HOST=.*/DB_HOST=$db_host/" .env
sed -i "s/# DB_PORT=.*/DB_PORT=$db_port/" .env
sed -i "s/# DB_DATABASE=.*/DB_DATABASE=$db_name/" .env
sed -i "s/# DB_USERNAME=.*/DB_USERNAME=$db_user/" .env
sed -i "s/# DB_PASSWORD=.*/DB_PASSWORD=$db_pass/" .env

# Uncomment MySQL settings
sed -i "s/^# DB_HOST/DB_HOST/" .env
sed -i "s/^# DB_PORT/DB_PORT/" .env
sed -i "s/^# DB_DATABASE/DB_DATABASE/" .env
sed -i "s/^# DB_USERNAME/DB_USERNAME/" .env
sed -i "s/^# DB_PASSWORD/DB_PASSWORD/" .env

# Comment out SQLite
sed -i "s/^DB_CONNECTION=sqlite/# DB_CONNECTION=sqlite/" .env

echo ""
echo "✅ MySQL configuration updated!"
echo ""
echo "Next steps:"
echo "1. Create database in MySQL:"
echo "   mysql -u $db_user -p"
echo "   CREATE DATABASE $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
echo "   EXIT;"
echo ""
echo "2. Run migrations:"
echo "   php artisan migrate"
echo ""
echo "3. Clear config cache:"
echo "   php artisan config:clear"
