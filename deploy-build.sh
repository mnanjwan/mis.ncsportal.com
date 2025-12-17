#!/bin/bash

# Script to build assets locally and deploy to production
# Usage: ./deploy-build.sh [production_server_path]

set -e

echo "🔨 Building assets..."
npm run build

if [ ! -f "public/build/manifest.json" ]; then
    echo "❌ Error: manifest.json not found after build"
    exit 1
fi

echo "✅ Build completed successfully"
echo ""
echo "📦 To deploy to production, run one of these commands:"
echo ""
echo "Option A - Copy build directory to server:"
echo "  scp -r public/build user@srv877042:/home/mis.ncsportal.com/public_html/public/"
echo ""
echo "Option B - Use rsync (recommended):"
echo "  rsync -avz --delete public/build/ user@srv877042:/home/mis.ncsportal.com/public_html/public/build/"
echo ""
echo "Replace 'user' with your actual SSH username"
