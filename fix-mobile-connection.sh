#!/bin/bash

# Mobile Connection Fix Script
# Ye script server ko mobile se accessible banata hai

echo "=========================================="
echo "📱 MOBILE CONNECTION FIX"
echo "=========================================="
echo ""

# Get Network IP
NETWORK_IP=$(hostname -I | awk '{print $1}')
PORT=8000

echo "🔍 Current Status:"
echo "   Network IP: $NETWORK_IP"
echo "   Port: $PORT"
echo ""

# Check if server is running
if pgrep -f "php artisan serve" > /dev/null; then
    echo "⚠️  Existing server processes found. Stopping them..."
    pkill -f "php artisan serve"
    sleep 2
    echo "✅ Old server processes stopped"
    echo ""
fi

# Check current server binding
CURRENT_BIND=$(ss -tuln | grep ":8000" | awk '{print $4}')
if [ ! -z "$CURRENT_BIND" ]; then
    echo "📊 Current binding: $CURRENT_BIND"
    if [[ "$CURRENT_BIND" == *"127.0.0.1"* ]]; then
        echo "❌ Server is bound to localhost (127.0.0.1)"
        echo "   This is why mobile can't connect!"
    fi
    echo ""
fi

echo "🚀 Starting server on network (0.0.0.0)..."
echo ""
echo "📲 Mobile se access karne ke liye:"
echo "   http://$NETWORK_IP:$PORT"
echo ""
echo "⚠️  Important:"
echo "   1. Laptop aur Phone same WiFi par hone chahiye"
echo "   2. Agar connection nahi ho, to firewall check karein:"
echo "      sudo ufw allow 8000/tcp"
echo ""
echo "=========================================="
echo ""

# Start server on all network interfaces (0.0.0.0)
cd /var/www/html/VideocallProject
php artisan serve --host=0.0.0.0 --port=$PORT



