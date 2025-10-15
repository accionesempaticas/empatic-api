#!/bin/bash

# Manual backup script for emergency backups
echo "🚨 Starting emergency database backup..."

# Get current timestamp
TIMESTAMP=$(date +"%Y-%m-%d_%H-%M-%S")
BACKUP_DIR="storage/app/backups"
PERSISTENT_BACKUP_DIR="/app/persistent-backups"
BACKUP_FILE="emergency_backup_${TIMESTAMP}.sqlite"

# Ensure backup directories exist
mkdir -p $BACKUP_DIR
mkdir -p $PERSISTENT_BACKUP_DIR

# Check if database exists
if [ ! -f "database/database.sqlite" ]; then
    echo "❌ Database file not found!"
    exit 1
fi

# Create backup in both locations
if cp database/database.sqlite "$BACKUP_DIR/$BACKUP_FILE"; then
    echo "✅ Emergency backup created successfully in local storage:"
    echo "   📁 File: $BACKUP_FILE"
    echo "   📊 Size: $(du -h "$BACKUP_DIR/$BACKUP_FILE" | cut -f1)"
    echo "   📍 Location: $BACKUP_DIR/$BACKUP_FILE"

    # Also create backup in persistent volume (Railway)
    if [ -d "$PERSISTENT_BACKUP_DIR" ]; then
        cp database/database.sqlite "$PERSISTENT_BACKUP_DIR/$BACKUP_FILE"
        echo "✅ Emergency backup also created in persistent volume:"
        echo "   📍 Persistent Location: $PERSISTENT_BACKUP_DIR/$BACKUP_FILE"
    fi

    # List all backups
    echo ""
    echo "📋 All available backups:"
    echo "Local backups:"
    ls -lah $BACKUP_DIR/*.sqlite 2>/dev/null || echo "   No local backups found"

    if [ -d "$PERSISTENT_BACKUP_DIR" ]; then
        echo "Persistent backups:"
        ls -lah $PERSISTENT_BACKUP_DIR/*.sqlite 2>/dev/null || echo "   No persistent backups found"
    fi

else
    echo "❌ Failed to create emergency backup!"
    exit 1
fi

echo "✅ Emergency backup completed!"