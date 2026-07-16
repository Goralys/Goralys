@echo off
setlocal EnableExtensions EnableDelayedExpansion

type scripts\banner.txt

rem Detect a PHP 8.5+ binary. Adjust the fallback path below if your
rem local PHP installs live somewhere else (e.g. C:\php85\php.exe).
set "PHP_BIN=php"
where php85 >nul 2>&1
if not errorlevel 1 (
    set "PHP_BIN=php85"
) else if exist "C:\php85\php.exe" (
    set "PHP_BIN=C:\php85\php.exe"
)

echo ==================================================
echo =====             Goralys setup              =====
echo ==================================================

echo [1/6] Checking for pnpm...
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

for /f "delims=" %%C in ('where composer') do set "COMPOSER_BIN=%%C" & goto :composer_found
:composer_found

echo [OK] composer found (using !PHP_BIN!).

echo [3/6] Installing dependencies ...
call "!PHP_BIN!" "!COMPOSER_BIN!" install --working-dir=backend
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
echo
echo PHP_SESSION_LIFETIME=3600
echo PHP_SESSION_LIFETIME_MULTIPLIER=1.25
echo GORALYS_ENVIRONMENT="prod"
echo
echo ALLOWED_DOMAINS="http://localhost:3000"
echo MASTER_DOMAIN="exemple.com"
echo COOKIES_DOMAIN=".exemple.com"
echo
echo MAIL_HOST="smtp.exemple.com"
echo MAIL_PORT=587
echo MAIL_USERNAME="your mail username (address)"
echo MAIL_PASSWORD="your mail password"
echo MAIL_ADMIN_ADDRESS="admin@exemple.com, jhon.doe@mail.com"
) > ./backend/.env

(
echo NEXT_PUBLIC_API_DOMAIN="your api domain"
echo NEXT_PUBLIC_API_TOKEN="veryrand0mbytes"
) > ./.env.local

echo .env ready.
echo.

:after_env

echo [5/6] Creating directories ...
echo Creating Logs directory ...
if not exist ".\backend\Logs" mkdir ".\backend\Logs" >nul 2>&1
echo Creating RateLimiter directory ...
if not exist ".\backend\RateLimiter" mkdir ".\backend\RateLimiter" >nul 2>&1
echo Creating Assets directory ...
if not exist ".\backend\Assets" mkdir ".\backend\Assets" >nul 2>&1
if not exist ".\backend\Assets\Template" mkdir ".\backend\Assets\Template" >nul 2>&1
if not exist ".\backend\Assets\Mails" mkdir ".\backend\Assets\Mails" >nul 2>&1
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