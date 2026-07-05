#!/usr/bin/env bash

# This script is used to preserve the app state between deployments.
# You should always run it before updating your version on your server.

set -euo pipefail

BACKUP_DIR=~/goralys_backup_backend

if [ -d "$BACKUP_DIR" ]; then
    read -r -p "Backup exists at $BACKUP_DIR. Overwrite? (y/N): " confirm

    case "$confirm" in
        [yY]|[yY][eE][sS])
            rm -rf "$BACKUP_DIR"
            ;;
        *)
            echo "Backup cancelled."
            exit 1
            ;;
    esac
fi

mkdir -p "$BACKUP_DIR"

cp -r backend/Assets "$BACKUP_DIR/Assets"
cp -r backend/Users "$BACKUP_DIR/Users"
cp backend/.env "$BACKUP_DIR/.env"

echo "Successfully created backup at $BACKUP_DIR"