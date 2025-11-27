# Gmail SMTP Configuration Guide

## Problem
OTP emails nahi aa rahe kyunki mail configuration sahi nahi hai.

## Solution - Gmail SMTP Setup

### Step 1: Gmail App Password Generate Karein

1. **Google Account mein jao**: https://myaccount.google.com/
2. **Security** section mein jao
3. **2-Step Verification** enable karein (agar nahi hai to)
4. **App Passwords** par click karein: https://myaccount.google.com/apppasswords
5. **Select app**: "Mail" choose karein
6. **Select device**: "Other (Custom name)" choose karein aur "Laravel App" type karein
7. **Generate** button click karein
8. **16-digit password** copy karein (yeh use karna hai, regular password nahi)

### Step 2: .env File Update Karein

`.env` file mein yeh values update karein:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME="your-email@gmail.com"
MAIL_PASSWORD="your-16-digit-app-password"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@gmail.com"
MAIL_FROM_NAME="Video Call App"
```

### Step 3: Config Cache Clear Karein

```bash
php artisan config:clear
php artisan cache:clear
```

### Step 4: Test Email

Test karne ke liye:

```bash
php artisan tinker
```

Phir yeh command run karein:

```php
Mail::raw('Test email', function($m) { 
    $m->to('your-test-email@gmail.com')->subject('Test'); 
});
```

### Quick Setup Script

Ya phir script use karein:

```bash
./setup-gmail.sh
```

## Important Notes

⚠️ **Regular Gmail password use mat karein** - App Password hi use karein
⚠️ **2-Step Verification zaroori hai** - App Password generate karne ke liye
⚠️ **App Password 16 characters ka hoga** - Spaces ignore karein

## Troubleshooting

### Error: "Authentication failed"
- Check karein ki App Password sahi hai
- 2-Step Verification enabled hai ya nahi

### Error: "Connection timeout"
- Firewall check karein
- Port 587 blocked to nahi hai

### Emails spam mein ja rahe hain
- Gmail account verify karein
- SPF/DKIM records setup karein (production ke liye)

## Alternative: Mailtrap (Development)

Development ke liye Mailtrap use kar sakte hain:

1. https://mailtrap.io/ par account banayein
2. SMTP settings copy karein
3. `.env` mein update karein

Mailtrap emails actually send nahi karta, sirf testing ke liye perfect hai.

