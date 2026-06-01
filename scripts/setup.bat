@echo off
setlocal EnableExtensions EnableDelayedExpansion

type scripts\banner.txt

echo ==================================================
echo =====             Goralys setup              =====
echo ==================================================

echo [1/6 ]Checking for pnpm...
where pnpm >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Fatal: pnpm not found in PATH.
    echo >> Please install pnpm or add it to your system PATH.
    pause
    exit /b 1
)

echo [OK] pnpm found.

echo [2/6] Checking for Composer...
where composer >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Fatal: Composer not found in PATH.
    echo >> Please install Composer or add it to your system PATH.
    pause
    exit /b 1
)

echo [OK] composer found.

echo [3/6] Installing dependencies ...
call composer install --working-dir=backend
if errorlevel 1 (
    echo [ERROR] Composer install failed.
    pause
    exit /b 1
)

call pnpm install
if errorlevel 1 (
    echo [ERROR] pnpm install failed.
    pause
    exit /b 1
)

echo [OK] Successfully installed dependencies.
echo.

echo [4/6] Creating .env file ...

if exist ".\backend\.env" (
    echo An existing .env file was found, do you want to overwrite it ? This will delete all previous configuration.
    set /p OVERWRITE="Overwrite ? (Y/n) : "
    if /I not "!OVERWRITE!"=="Y" (
        echo Keeping existing .env
        goto :after_env
    )
)

(
echo DATABASE_HOST="localhost"
echo DATABASE_ID="your db id (user)"
echo DATABASE_PASSWORD="your db password"
echo DATABASE_NAME="your db name"
echo FOLDER="/"
echo PHP_SESSION_LIFETIME=3600
echo PHP_SESSION_LIFETIME_MULTIPLIER=1.25
echo GORALYS_ENVIRONMENT="dev"
) > ./backend/.env

(
echo NEXT_PUBLIC_API_DOMAIN="your api domain"
) > ./.env.local

echo .env ready.
echo.

:after_env

echo [5/6] Creating directories ...
echo Creating Logs directory ...
if not exist ".\backend\Logs" mkdir ".\backend\Logs" >nul 2>&1
echo Creating Assets directory ...
if not exist ".\backend\Assets" mkdir ".\backend\Assets" >nul 2>&1
if not exist ".\backend\Assets\Template" mkdir ".\backend\Assets\Template" >nul 2>&1
if not exist ".\backend\Assets\Template\Exports" mkdir ".\backend\Assets\Template\Exports" >nul 2>&1
if not exist ".\backend\Assets\StudentsDrafts" mkdir ".\backend\Assets\StudentsDrafts" >nul 2>&1
echo [OK] Directories are ready.
echo.

echo [6/6] Running checks
echo Would you like the setup to run checks (eslint + phpcs)?
set /p RUN_CHECKS="Run checks ? (Y/n) : "
if /I not "!RUN_CHECKS!"=="Y" (
    goto :done
)

echo.
echo Running eslint + phpcs checks...
call pnpm run lint
if errorlevel 1 (
    echo [ERROR] ESLint failed. Fix issues and re-run setup or run: pnpm run lint
    pause
    exit /b 1
)

call pnpm run phpcs
if errorlevel 1 (
    echo.
    echo PHPCS found coding standard violations.
    set /p RUN_FIX="Run phpcbf to auto-fix what it can ? (Y/n) : "
    if /I "!RUN_FIX!"=="Y" (
        call pnpm run phpcbf

        echo Re-running phpcs to verify...
        call pnpm run phpcs
        if errorlevel 1 (
            echo [ERROR] Some PHPCS issues remain after PHPCBF.
            echo You will need to fix the remaining violations manually.
            pause
            exit /b 1
        ) else (
            echo PHPCS clean after PHPCBF.
        )
    ) else (
        echo Skipped PHPCBF. You can run it later with: pnpm run phpcbf
        pause
        exit /b 1
    )
) else (
    echo PHPCS clean.
)

:done
echo.
echo ==================================================
echo =====             Setup Complete             =====
echo ==================================================
echo You can now edit your .env file and start coding.
pause
exit /b 0