#!/bin/bash

echo "=========================================="
echo "Gmail SMTP Configuration Setup"
echo "=========================================="
echo ""
read -p "Enter your Gmail address: " GMAIL_EMAIL
read -p "Enter your Gmail App Password (not regular password): " GMAIL_PASSWORD
read -p "Enter your name/company name for sender: " SENDER_NAME

# Update .env file
sed -i "s/MAIL_MAILER=log/MAIL_MAILER=smtp/" .env
sed -i "s|MAIL_HOST=127.0.0.1|MAIL_HOST=smtp.gmail.com|" .env
sed -i "s/MAIL_PORT=2525/MAIL_PORT=587/" .env
sed -i "s/MAIL_USERNAME=null/MAIL_USERNAME=\"$GMAIL_EMAIL\"/" .env
sed -i "s|MAIL_PASSWORD=null|MAIL_PASSWORD=\"$GMAIL_PASSWORD\"|" .env
sed -i "s|MAIL_FROM_ADDRESS=\"hello@example.com\"|MAIL_FROM_ADDRESS=\"$GMAIL_EMAIL\"|" .env
sed -i "s|MAIL_FROM_NAME=\"\${APP_NAME}\"|MAIL_FROM_NAME=\"$SENDER_NAME\"|" .env

# Add MAIL_ENCRYPTION if not exists
if ! grep -q "MAIL_ENCRYPTION" .env; then
    sed -i "/MAIL_PASSWORD/a MAIL_ENCRYPTION=tls" .env
else
    sed -i "s/MAIL_ENCRYPTION=.*/MAIL_ENCRYPTION=tls/" .env
fi

echo ""
echo "✅ Gmail configuration updated successfully!"
echo ""
echo "Next steps:"
echo "1. Make sure you have enabled 'Less secure app access' OR"
echo "2. Use Gmail App Password (Recommended)"
echo "   - Go to: https://myaccount.google.com/apppasswords"
echo "   - Generate an app password for 'Mail'"
echo "   - Use that password in MAIL_PASSWORD"
echo ""
echo "3. Clear config cache: php artisan config:clear"
echo "4. Test email: php artisan tinker"
echo "   Then run: Mail::raw('Test', function(\$m) { \$m->to('your@email.com')->subject('Test'); });"

