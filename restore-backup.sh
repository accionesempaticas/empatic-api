#!/bin/bash

# Database restoration script
echo "🔄 Database Restoration Tool"
echo "============================="

BACKUP_DIR="storage/app/backups"
PERSISTENT_BACKUP_DIR="/app/persistent-backups"
DB_PATH="database/database.sqlite"

# Check if backup directories exist
LOCAL_BACKUPS_EXIST=false
PERSISTENT_BACKUPS_EXIST=false

if [ -d "$BACKUP_DIR" ]; then
    LOCAL_BACKUPS_EXIST=true
fi

if [ -d "$PERSISTENT_BACKUP_DIR" ]; then
    PERSISTENT_BACKUPS_EXIST=true
fi

if [ "$LOCAL_BACKUPS_EXIST" = false ] && [ "$PERSISTENT_BACKUPS_EXIST" = false ]; then
    echo "❌ No backup directories found"
    exit 1
fi

# List available backups
echo ""
echo "📋 Available backups:"

# Collect backups from both locations
BACKUPS=()

if [ "$LOCAL_BACKUPS_EXIST" = true ]; then
    echo "Local backups:"
    LOCAL_BACKUPS=($(ls -1t $BACKUP_DIR/*.sqlite 2>/dev/null))
    for backup in "${LOCAL_BACKUPS[@]}"; do
        BACKUPS+=("$backup")
    done
    if [ ${#LOCAL_BACKUPS[@]} -eq 0 ]; then
        echo "   No local backups found"
    fi
fi

if [ "$PERSISTENT_BACKUPS_EXIST" = true ]; then
    echo "Persistent backups:"
    PERSISTENT_BACKUPS=($(ls -1t $PERSISTENT_BACKUP_DIR/*.sqlite 2>/dev/null))
    for backup in "${PERSISTENT_BACKUPS[@]}"; do
        BACKUPS+=("$backup")
    done
    if [ ${#PERSISTENT_BACKUPS[@]} -eq 0 ]; then
        echo "   No persistent backups found"
    fi
fi

if [ ${#BACKUPS[@]} -eq 0 ]; then
    echo "❌ No backup files found"
    exit 1
fi

# Sort all backups by modification time (newest first)
IFS=$'\n' BACKUPS=($(sort -t$'\n' -k1,1 -r <(for backup in "${BACKUPS[@]}"; do echo "$(stat -f "%m" "$backup"):$backup"; done) | cut -d: -f2-))

# Display backups with numbers
for i in "${!BACKUPS[@]}"; do
    BACKUP_FILE="${BACKUPS[$i]}"
    BASENAME=$(basename "$BACKUP_FILE")
    SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    MODIFIED=$(date -r "$BACKUP_FILE" "+%Y-%m-%d %H:%M:%S")
    echo "   $((i+1)). $BASENAME (${SIZE}, ${MODIFIED})"
done

echo ""
echo "💡 Usage examples:"
echo "   ./restore-backup.sh 1              # Restore latest backup"
echo "   ./restore-backup.sh backup_file.sqlite  # Restore specific backup"
echo ""

# Get user selection
if [ $# -eq 0 ]; then
    read -p "Select backup number (1-${#BACKUPS[@]}) or 'q' to quit: " SELECTION

    if [ "$SELECTION" = "q" ]; then
        echo "Cancelled."
        exit 0
    fi

    if ! [[ "$SELECTION" =~ ^[0-9]+$ ]] || [ "$SELECTION" -lt 1 ] || [ "$SELECTION" -gt ${#BACKUPS[@]} ]; then
        echo "❌ Invalid selection"
        exit 1
    fi

    SELECTED_BACKUP="${BACKUPS[$((SELECTION-1))]}"
else
    # Command line argument provided
    if [[ "$1" =~ ^[0-9]+$ ]]; then
        # Number provided
        if [ "$1" -lt 1 ] || [ "$1" -gt ${#BACKUPS[@]} ]; then
            echo "❌ Invalid backup number: $1"
            exit 1
        fi
        SELECTED_BACKUP="${BACKUPS[$((1-1))]}"
    else
        # Filename provided
        SELECTED_BACKUP="$BACKUP_DIR/$1"
        if [ ! -f "$SELECTED_BACKUP" ]; then
            echo "❌ Backup file not found: $SELECTED_BACKUP"
            exit 1
        fi
    fi
fi

echo ""
echo "🎯 Selected backup: $(basename "$SELECTED_BACKUP")"

# Confirm restoration
read -p "⚠️  This will REPLACE your current database. Continue? (y/N): " CONFIRM

if [[ ! "$CONFIRM" =~ ^[Yy]$ ]]; then
    echo "Cancelled."
    exit 0
fi

# Create backup of current database before restoration
if [ -f "$DB_PATH" ]; then
    TIMESTAMP=$(date +"%Y-%m-%d_%H-%M-%S")
    SAFETY_BACKUP="$BACKUP_DIR/pre_restore_backup_${TIMESTAMP}.sqlite"

    echo "💾 Creating safety backup of current database..."
    if cp "$DB_PATH" "$SAFETY_BACKUP"; then
        echo "✅ Safety backup created: $(basename "$SAFETY_BACKUP")"
    else
        echo "❌ Failed to create safety backup!"
        exit 1
    fi
fi

# Restore the selected backup
echo "🔄 Restoring database..."
if cp "$SELECTED_BACKUP" "$DB_PATH"; then
    echo "✅ Database restored successfully!"
    echo "   📁 From: $(basename "$SELECTED_BACKUP")"
    echo "   📍 To: $DB_PATH"
    echo ""
    echo "🔧 You may want to run migrations to ensure database is up to date:"
    echo "   php artisan migrate --force"
else
    echo "❌ Failed to restore database!"
    exit 1
fi