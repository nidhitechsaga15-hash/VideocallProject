#!/bin/bash

# Quick HTTPS Setup Script for Video Call App
# Ye script HTTPS setup karta hai mobile camera access ke liye

echo "=========================================="
echo "🔒 QUICK HTTPS SETUP"
echo "=========================================="
echo ""

# Get Network IP
NETWORK_IP=$(hostname -I | awk '{print $1}')
PORT=8000

echo "📱 Network IP: $NETWORK_IP"
echo ""

# Check if mkcert is installed
if ! command -v mkcert &> /dev/null; then
    echo "❌ mkcert installed nahi hai"
    echo ""
    echo "📦 Install karein:"
    echo "   sudo apt update"
    echo "   sudo apt install mkcert"
    echo ""
    exit 1
fi

echo "✅ mkcert found"
echo ""

# Create certs directory
mkdir -p storage/certs
cd storage/certs

# Install local CA
echo "📝 Installing local CA..."
mkcert -install

# Generate certificate
echo ""
echo "🔐 Generating certificate..."
mkcert localhost 127.0.0.1 ::1 $NETWORK_IP

echo ""
echo "✅ Certificate generated!"
echo ""

# Find certificate files
CERT_FILE=$(ls -t *.pem 2>/dev/null | grep -v key | head -1)
KEY_FILE=$(ls -t *-key.pem 2>/dev/null | head -1)

if [ -z "$CERT_FILE" ] || [ -z "$KEY_FILE" ]; then
    echo "❌ Certificate files nahi mil rahe"
    exit 1
fi

echo "📋 Certificate files:"
echo "   Cert: $CERT_FILE"
echo "   Key: $KEY_FILE"
echo ""

cd ../..

echo "=========================================="
echo "✅ HTTPS SETUP COMPLETE!"
echo "=========================================="
echo ""
echo "📝 Next Steps:"
echo ""
echo "1. Server HTTPS par start karein:"
echo "   php artisan serve --host=0.0.0.0 --port=$PORT \\"
echo "     --tls-cert=storage/certs/$CERT_FILE \\"
echo "     --tls-key=storage/certs/$KEY_FILE"
echo ""
echo "2. Phone browser mein open karein:"
echo "   https://$NETWORK_IP:$PORT"
echo ""
echo "3. Certificate warning accept karein (safe hai local dev ke liye)"
echo ""
echo "⚠️  Note:"
echo "   - Server stop karein (Ctrl+C) agar pehle se chal raha ho"
echo "   - Phir HTTPS server start karein"
echo ""

