# 🚀 Quick Fix - Gmail OTP Email Issue

## Problem
OTP emails nahi aa rahe kyunki mail configuration `log` mode mein hai.

## Solution (3 Steps)

### Step 1: Gmail App Password Generate Karein

1. Google Account kholo: https://myaccount.google.com/
2. **Security** → **2-Step Verification** (enable karo agar nahi hai)
3. **App Passwords** par click: https://myaccount.google.com/apppasswords
4. **Select app**: "Mail"
5. **Select device**: "Other" → "Laravel"
6. **Generate** → 16-digit password copy karo

### Step 2: .env File Update Karein

`.env` file mein yeh lines update karo:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME="your-email@gmail.com"
MAIL_PASSWORD="xxxx xxxx xxxx xxxx"  # 16-digit app password (spaces ignore)
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@gmail.com"
MAIL_FROM_NAME="Video Call App"
```

**Important**: 
- `MAIL_PASSWORD` mein **App Password** use karo, regular password nahi
- Spaces ignore karo (xxxx xxxx xxxx xxxx = xxxxxxxxxxxxxxxx)

### Step 3: Config Clear Karein

```bash
php artisan config:clear
php artisan cache:clear
```

### Step 4: Test Karein

```bash
php artisan tinker
```

Phir:
```php
Mail::raw('Test', function($m) { 
    $m->to('your-email@gmail.com')->subject('Test Email'); 
});
```

Agar email aa gaya to sab sahi hai! ✅

---

## Alternative: Quick Script

```bash
./update-env-mail.sh
```

Ya manually `.env` file edit karo.

---

## Troubleshooting

### ❌ "Authentication failed"
→ App Password sahi hai? Regular password use to nahi kar rahe?

### ❌ "Connection timeout"  
→ Port 587 blocked to nahi? Firewall check karo.

### ❌ Emails spam mein
→ Normal hai development mein. Production mein SPF/DKIM setup karo.

---

## Development Alternative: Mailtrap

Agar Gmail setup mushkil ho to Mailtrap use karo (testing ke liye perfect):

1. https://mailtrap.io/ → Free account
2. SMTP settings copy karo
3. `.env` mein update karo

Mailtrap emails actually send nahi karta, testing ke liye best hai.

