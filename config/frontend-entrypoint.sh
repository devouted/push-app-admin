#!/bin/sh
set -e

echo "Installing/updating npm dependencies..."
npm install

echo "Starting development server..."
exec npm run dev -- --host 0.0.0.0
