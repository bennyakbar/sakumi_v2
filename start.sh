#!/bin/bash

# Ensure we are in the correct directory
if [ ! -f "artisan" ]; then
    echo "Error: artisan not found. Please run this script from the sakumi root directory."
    exit 1
fi

PORT=8001
HOST=127.0.0.1

echo "Targeting Sakumi on http://$HOST:$PORT"

# Check if port is in use
if lsof -i :$PORT > /dev/null; then
    echo "Warning: Port $PORT is already in use."
    PID=$(lsof -t -i:$PORT)
    echo "Killing process $PID..."
    kill -9 $PID
fi

# Start npm run dev in background if not running
if ! pgrep -f "npm run dev" > /dev/null; then
    echo "Starting npm run dev..."
    npm run dev > /dev/null 2>&1 &
fi

# Start artisan serve
echo "Starting Sakumi Server..."
echo "Access URL: http://$HOST:$PORT"
php artisan serve --host=$HOST --port=$PORT
