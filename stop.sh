#!/bin/bash

PORT=8001

echo "Stopping Sakumi processes on port $PORT..."

# Check if port is in use
if lsof -i :$PORT > /dev/null; then
    PID=$(lsof -t -i:$PORT)
    echo "Killing process $PID..."
    kill -9 $PID
    echo "Sakumi stopped."
else
    echo "No process found on port $PORT."
fi
