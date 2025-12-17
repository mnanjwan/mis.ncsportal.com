#!/bin/bash

# NCS Employee Portal - Application Launcher
# This script starts both backend and frontend servers

echo "🚀 Starting NCS Employee Portal..."
echo ""

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Get the project directory
PROJECT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
BACKEND_DIR="$PROJECT_DIR"
FRONTEND_DIR="$PROJECT_DIR/ncs-employee-portal"

# Check if we're in the right directory
if [ ! -f "$BACKEND_DIR/artisan" ]; then
    echo "❌ Error: artisan file not found. Please run this script from the project root."
    exit 1
fi

# Function to check if port is in use
check_port() {
    lsof -Pi :$1 -sTCP:LISTEN -t >/dev/null
}

# Check and start backend
echo -e "${BLUE}📦 Starting Backend Server...${NC}"
if check_port 8000; then
    echo -e "${YELLOW}⚠️  Port 8000 is already in use. Backend may already be running.${NC}"
else
    cd "$BACKEND_DIR"
    php artisan serve --host=0.0.0.0 --port=8000 > backend.log 2>&1 &
    BACKEND_PID=$!
    echo -e "${GREEN}✅ Backend started (PID: $BACKEND_PID)${NC}"
    echo "   Backend URL: http://localhost:8000"
    sleep 2
fi

# Check and start frontend
echo -e "${BLUE}🌐 Starting Frontend Server...${NC}"
if check_port 8080; then
    echo -e "${YELLOW}⚠️  Port 8080 is already in use. Frontend may already be running.${NC}"
else
    cd "$FRONTEND_DIR"
    if command -v python3 &> /dev/null; then
        python3 -m http.server 8080 > frontend.log 2>&1 &
        FRONTEND_PID=$!
        echo -e "${GREEN}✅ Frontend started (PID: $FRONTEND_PID)${NC}"
    elif command -v php &> /dev/null; then
        php -S localhost:8080 > frontend.log 2>&1 &
        FRONTEND_PID=$!
        echo -e "${GREEN}✅ Frontend started (PID: $FRONTEND_PID)${NC}"
    else
        echo -e "${YELLOW}⚠️  Python3 or PHP not found. Please start frontend manually.${NC}"
        FRONTEND_PID=""
    fi
    echo "   Frontend URL: http://localhost:8080"
fi

echo ""
echo -e "${GREEN}🎉 Application is starting!${NC}"
echo ""
echo "📋 Access Points:"
echo "   Frontend: http://localhost:8080/authentication/login.html"
echo "   Backend API: http://localhost:8000/api/v1"
echo ""
echo "🔐 Test Credentials:"
echo "   HRD: hrd@ncs.gov.ng / password123"
echo "   Staff Officer: staff.officer@ncs.gov.ng / password123"
echo ""
echo "📝 Logs:"
echo "   Backend: $BACKEND_DIR/backend.log"
echo "   Frontend: $FRONTEND_DIR/frontend.log"
echo ""
echo "🛑 To stop servers:"
echo "   kill $BACKEND_PID $FRONTEND_PID"
echo ""
echo "Press Ctrl+C to stop..."

# Wait for user interrupt
trap "echo ''; echo 'Stopping servers...'; kill $BACKEND_PID $FRONTEND_PID 2>/dev/null; exit" INT
wait

