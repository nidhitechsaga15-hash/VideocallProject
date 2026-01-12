#!/bin/bash

# Phone Connection Fix Script
# Ye script phone se IP connection fix karta hai

echo "=========================================="
echo "📱 PHONE CONNECTION FIX"
echo "=========================================="
echo ""

# Get Network IP
NETWORK_IP=$(hostname -I | awk '{print $1}')
PORT=8000

echo "🔍 Current Network IP: $NETWORK_IP"
echo ""

# Check if server is running on localhost only
echo "📊 Checking server status..."
if ss -tuln | grep -q "127.0.0.1:8000"; then
    echo "❌ Server localhost par chal raha hai (127.0.0.1:8000)"
    echo "   Phone se connect nahi hoga!"
    echo ""
    echo "✅ Solution: Server ko 0.0.0.0 par start karein"
    echo ""
    echo "📝 Steps:"
    echo "   1. Current server stop karein (Ctrl+C)"
    echo "   2. Ye command run karein:"
    echo "      php artisan serve --host=0.0.0.0 --port=8000"
    echo ""
    echo "   Ya ye script use karein:"
    echo "      ./start-network-server.sh"
    echo ""
elif ss -tuln | grep -q "0.0.0.0:8000"; then
    echo "✅ Server network par accessible hai (0.0.0.0:8000)"
    echo ""
    echo "📱 Phone se access karne ke liye:"
    echo "   http://$NETWORK_IP:$PORT"
    echo ""
else
    echo "⚠️  Server running nahi hai"
    echo ""
    echo "📝 Server start karein:"
    echo "   php artisan serve --host=0.0.0.0 --port=8000"
    echo ""
fi

# Check firewall
echo ""
echo "🔥 Firewall Check:"
if command -v ufw &> /dev/null; then
    FIREWALL_STATUS=$(sudo ufw status | grep -i "Status: active")
    if [ ! -z "$FIREWALL_STATUS" ]; then
        PORT_ALLOWED=$(sudo ufw status | grep "8000/tcp")
        if [ -z "$PORT_ALLOWED" ]; then
            echo "⚠️  Firewall active hai, port 8000 allow nahi hai"
            echo ""
            echo "✅ Port allow karne ke liye:"
            echo "   sudo ufw allow 8000/tcp"
            echo ""
        else
            echo "✅ Port 8000 firewall mein allowed hai"
        fi
    else
        echo "✅ Firewall inactive hai"
    fi
else
    echo "ℹ️  UFW installed nahi hai"
fi

echo ""
echo "=========================================="
echo ""
echo "📋 Quick Fix Commands:"
echo ""
echo "1. Firewall allow (agar needed ho):"
echo "   sudo ufw allow 8000/tcp"
echo ""
echo "2. Server network par start karein:"
echo "   php artisan serve --host=0.0.0.0 --port=8000"
echo ""
echo "3. Phone browser mein open karein:"
echo "   http://$NETWORK_IP:$PORT"
echo ""
echo "⚠️  Important:"
echo "   - Laptop aur Phone same WiFi par hone chahiye"
echo "   - http:// prefix zaroori hai"
echo "   - Port :8000 zaroori hai"
echo ""

