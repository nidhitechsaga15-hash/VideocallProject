#!/bin/bash

# HTTPS Setup Script for Video Call App
# This script helps setup HTTPS for local development

echo "=========================================="
echo "🔒 HTTPS SETUP FOR VIDEO CALL APP"
echo "=========================================="
echo ""

# Check if mkcert is installed
if ! command -v mkcert &> /dev/null; then
    echo "❌ mkcert is not installed."
    echo ""
    echo "📦 Install mkcert:"
    echo "   Ubuntu/Debian: sudo apt install mkcert"
    echo "   Or visit: https://github.com/FiloSottile/mkcert"
    echo ""
    echo "💡 Alternative: Use localhost instead of IP"
    echo "   On laptop: http://localhost:8000"
    echo "   On phone: Use same WiFi, but access via laptop's localhost"
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
echo "🔐 Generating certificate for localhost and network IP..."
NETWORK_IP=$(hostname -I | awk '{print $1}')

mkcert localhost 127.0.0.1 ::1 $NETWORK_IP

echo ""
echo "✅ Certificate generated!"
echo ""
echo "📋 Next Steps:"
echo "1. Update .env file:"
echo "   APP_URL=https://localhost:8000"
echo ""
echo "2. Start server with HTTPS:"
echo "   php artisan serve --host=0.0.0.0 --port=8000"
echo ""
echo "3. Access from phone:"
echo "   https://$NETWORK_IP:8000"
echo ""
echo "⚠️  Note: You'll need to accept the certificate warning"
echo "   on your phone (it's safe for local development)"
echo ""

