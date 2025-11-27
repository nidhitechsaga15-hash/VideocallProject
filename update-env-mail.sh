#!/bin/bash
# Quick Gmail SMTP update script

echo "Gmail SMTP Configuration"
echo "======================="
echo ""
read -p "Your Gmail Email: " email
read -p "Gmail App Password (16 digits): " password
read -p "Sender Name [Video Call App]: " name
name=${name:-Video Call App}

# Backup
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)

# Update mail settings
sed -i "s/MAIL_MAILER=.*/MAIL_MAILER=smtp/" .env
sed -i "s|MAIL_HOST=.*|MAIL_HOST=smtp.gmail.com|" .env
sed -i "s/MAIL_PORT=.*/MAIL_PORT=587/" .env
sed -i "s|MAIL_USERNAME=.*|MAIL_USERNAME=\"$email\"|" .env
sed -i "s|MAIL_PASSWORD=.*|MAIL_PASSWORD=\"$password\"|" .env
sed -i "s|MAIL_FROM_ADDRESS=.*|MAIL_FROM_ADDRESS=\"$email\"|" .env
sed -i "s|MAIL_FROM_NAME=.*|MAIL_FROM_NAME=\"$name\"|" .env

# Add MAIL_ENCRYPTION if missing
if ! grep -q "^MAIL_ENCRYPTION" .env; then
    sed -i "/MAIL_PASSWORD/a MAIL_ENCRYPTION=tls" .env
else
    sed -i "s/MAIL_ENCRYPTION=.*/MAIL_ENCRYPTION=tls/" .env
fi

echo ""
echo "✅ Configuration updated!"
echo "Run: php artisan config:clear"
