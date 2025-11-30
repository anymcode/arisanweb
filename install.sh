#!/bin/bash

# Arisan System - Quick Installation Script
# This script will help you set up the database

echo "================================================"
echo "  Sistem Arisan Digital - Database Setup"
echo "================================================"
echo ""

# Check if MySQL is running
if ! pgrep -x "mysqld" > /dev/null; then
    echo "❌ MySQL is not running!"
    echo "Please start XAMPP/LAMPP first:"
    echo "  sudo /opt/lampp/lampp start"
    exit 1
fi

echo "✅ MySQL is running"
echo ""

# Get the script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
SQL_FILE="$SCRIPT_DIR/database/schema.sql"

# Check if SQL file exists
if [ ! -f "$SQL_FILE" ]; then
    echo "❌ SQL file not found: $SQL_FILE"
    exit 1
fi

echo "📁 SQL file found: $SQL_FILE"
echo ""

# Import database
echo "📥 Importing database..."
echo "Please enter your MySQL root password (press Enter if no password):"

mysql -u root -p < "$SQL_FILE"

if [ $? -eq 0 ]; then
    echo ""
    echo "================================================"
    echo "  ✅ Installation Successful!"
    echo "================================================"
    echo ""
    echo "Database 'arisan_db' has been created with sample data."
    echo ""
    echo "Default Admin Credentials:"
    echo "  Username: admin"
    echo "  Password: admin123"
    echo ""
    echo "Access the application at:"
    echo "  http://localhost/arisan"
    echo ""
    echo "================================================"
else
    echo ""
    echo "❌ Installation failed!"
    echo "Please check your MySQL credentials and try again."
    exit 1
fi
