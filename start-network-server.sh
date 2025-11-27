#!/bin/bash

# Network Server Start Script
# Ye script server ko network accessible banata hai

echo "=========================================="
echo "🌐 NETWORK SERVER START"
echo "=========================================="

# Get Network IP
NETWORK_IP=$(hostname -I | awk '{print $1}')
PORT=8000

echo ""
echo "📱 Network IP: $NETWORK_IP"
echo "🔌 Port: $PORT"
echo ""
echo "📲 Phone se access karne ke liye:"
echo "   http://$NETWORK_IP:$PORT"
echo ""
echo "⚠️  Important:"
echo "   1. Laptop aur Phone same WiFi par hone chahiye"
echo "   2. Firewall allow karein (agar needed ho)"
echo "   3. Server start ho raha hai..."
echo ""
echo "=========================================="
echo ""

# Start server on all network interfaces (0.0.0.0)
php artisan serve --host=0.0.0.0 --port=$PORT

