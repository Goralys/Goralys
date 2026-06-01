#!/usr/bin/env bash

set -euo pipefail

show_banner() {
    cat "scripts/banner.txt"
    echo
}

show_banner

echo "=================================================="
echo "=====             Goralys setup              ====="
echo "=================================================="

echo "[1/6] Checking for pnpm..."
if ! command -v pnpm >/dev/null 2>&1; then
    echo "[ERROR] Fatal: pnpm not found in PATH."
    echo ">> Please install pnpm or add it to your system PATH."
    exit 1
fi

echo "[OK] pnpm found."

echo "[2/6] Checking for Composer..."
if ! command -v composer >/dev/null 2>&1; then
    echo "[ERROR] Fatal: Composer not found in PATH."
    echo ">> Please install Composer or add it to your system PATH."
    exit 1
fi

echo "[OK] composer found."

echo "[3/6] Installing dependencies ..."
composer install --working-dir=backend || {
    echo "[ERROR] Composer install failed."
    exit 1
}

pnpm install || {
    echo "[ERROR] pnpm install failed."
    exit 1
}

echo "[OK] Successfully installed dependencies."
echo

echo "[4/6] Creating .env file ..."

if [ -f "./backend/.env" ]; then
    echo "An existing .env file was found, do you want to overwrite it ?"
    read -r -p "Overwrite ? (Y/n) : " OVERWRITE
    if [[ "${OVERWRITE:-Y}" != "Y" ]]; then
        echo "Keeping existing .env"
        goto_after_env=true
    fi
fi

if [ "${goto_after_env:-false}" != "true" ]; then
cat > ./backend/.env <<'EOF'
DATABASE_HOST="localhost"
DATABASE_ID="your db id (user)"
DATABASE_PASSWORD="your db password"
DATABASE_NAME="your db name"
FOLDER="/"
PHP_SESSION_LIFETIME=3600
PHP_SESSION_LIFETIME_MULTIPLIER=1.25
GORALYS_ENVIRONMENT="dev"
EOF

cat > ./.env.local << 'EOF'
NEXT_PUBLIC_API_DOMAIN="your api domain"
EOF

    echo ".env ready."
    echo
fi

echo "[5/6] Creating directories ..."
echo "Creating Logs directory ..."
mkdir -p ./backend/Logs
echo "Creating Assets directory ..."
mkdir -p ./backend/Assets
mkdir -p ./backend/Assets/Template
mkdir -p ./backend/Assets/Template/Exports
mkdir -p ./backend/Assets/StudentsDrafts
echo "[OK] Directories are ready."
echo

echo "[6/6] Running checks"

read -r -p "Would you like the setup to run checks (eslint + phpcs)? (Y/n) : " RUN_CHECKS
if [[ "${RUN_CHECKS:-Y}" != "Y" ]]; then
    goto_done=true
fi

if [ "${goto_done:-false}" != "true" ]; then
    echo
    echo "Running eslint + phpcs checks..."

    pnpm run lint || {
        echo "[ERROR] ESLint failed."
        echo "Fix issues and re-run setup or run: pnpm run lint"
        exit 1
    }

    if ! pnpm run phpcs; then
        echo
        echo "PHPCS found coding standard violations."
        read -r -p "Run phpcbf to auto-fix what it can ? (Y/n) : " RUN_FIX

        if [[ "${RUN_FIX:-Y}" == "Y" ]]; then
            pnpm run phpcbf || {
                echo "[ERROR] PHPCBF failed."
                exit 1
            }

            echo "Re-running phpcs to verify..."
            pnpm run phpcs || {
                echo "[ERROR] Some PHPCS issues remain after PHPCBF."
                echo "You will need to fix the remaining violations manually."
                exit 1
            }

            echo "PHPCS clean after PHPCBF."
        else
            echo "Skipped PHPCBF. You can run it later with: pnpm run phpcbf"
            exit 1
        fi
    else
        echo "PHPCS clean."
    fi
fi

echo
echo "=================================================="
echo "=====             Setup Complete             ====="
echo "=================================================="
echo "You can now edit your .env file and start coding."
