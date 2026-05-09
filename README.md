# Goralys

Goralys is a lightweight web app to manage "Grand Oral" topics for students and teachers at a high school.

## Features

- Student/teacher/admin roles with automatic role detection at registration ([`AuthController::register`](backend/src/App/User/Controllers/AuthController.php)).
- Two-topic student workflow: draft, submit, and read-only once submitted ([`useSubjects`](app/hooks/useSubjects.ts), [`SubjectsController`](backend/src/App/Subjects/Controllers/SubjectsController.php)).
- Session-backed user data caching for fast frontend rendering ([`AuthController::login`](backend/src/App/User/Controllers/AuthController.php) manages session data).
- CSRF protection using a short-lived session token: [`CSRFService`](backend/src/App/Security/CSRF/Services/CSRFService.php) + [`fetchCsrfClient`](app/lib/fetch/fetch.client.ts).
- Toast notification system used by both PHP and Next.js ([`ToastController::showToast`](backend/src/App/Utils/Toast/Controllers/ToastController.php) and [`toast-provider.tsx`](app/ui/toast/toast-provider.tsx)).

## Quick start (development)

Prerequisites:

- PHP 8.5+ with mysqli
- Composer
- pnpm package manager

Steps:

1. Run setup script:
    ```bash
    .\scripts\setup.bat
    ```
    Or if you use Linux:
    ```bash
    ./scripts/setup.sh
    ```
2. Configure environment:
    - For development, modify the values inside .env (created using setup.bat)
3. Database:
    - Create the database and tables using the schema at [backend/data_structure.sql](backend/data_structure.sql).
4. Run dev server:
    - Run Next and PHP's built-in server for the API. By default, the next rewrite port for the API is 80:
        ```bash
        pnpm run dev
        php -S localhost:80
        ```
5. Access the app:
    - Visit `http://localhost/goralys/` (or `http://localhost:8000` if using built-in server).

## Testing

You can use phpunit to run the unit tests for the backend in `backend/tests`.
To run the tests, use the following command after installing the project dependencies with composer:

```bash
.\backend\vendor\bin\phpunit --configuration backend\phpunit.xml
```

### Topic import

To test the topic import system, you can use the test file under the `assets/` folder ([test.zip](assets/test.zip)).
This can also help you understand the required format for Goralys topics import. If your data does not follow this exact
format, the system will not be able to import it successfully.

## Security notes

- CSRF:
    - Token validated by [`CSRFService::validate`](backend/src/App/Security/CSRF/Services/CSRFService.php).
- Passwords:
    - Passwords are hashed using PHP's `password_hash` ([`RegisterService::register`](backend/src/Core/User/Services/RegisterService.php)) and verified with `password_verify` ([`LoginService::login`](backend/src/Core/User/Services/LoginService.php)).
- Sensitive config:
    - You _must_ use `.env` to configure your project.

_Note: the `develop` branch serves as a pre-production playground, so some commits may include experimental or buggy code — I try to minimize this as much as possible._

## Key code pointers

- Main Kernel (Initialization & Routing): [`GoralysKernel`](backend/src/Kernel/GoralysKernel.php)
- Authentication & Sessions: [`AuthController`](backend/src/App/User/Controllers/AuthController.php)
- Subjects Management: [`SubjectsController`](backend/src/App/Subjects/Controllers/SubjectsController.php)
- Database schema: [backend/data_structure.sql](backend/data_structure.sql)
- Frontend Subject logic: [`useSubjects` hook](app/hooks/useSubjects.ts)
- Toast notification: [`ToastController`](backend/src/App/Utils/Toast/Controllers/ToastController.php) and [`toast-provider.tsx`](app/ui/toast/toast-provider.tsx)
- CSRF Service: [`CSRFService`](backend/src/App/Security/CSRF/Services/CSRFService.php)

## Project structure

### Frontend (Next.js)

- `app/`: Contains the application pages and logic.
- `app/subject/`: Student, Teacher, and Admin dashboards.
- `app/hooks/`: React hooks for data fetching and state management.
- `app/ui/`: Reusable UI components.

### Backend (PHP)

- `backend/API/`: API endpoints, acting as entry points for the kernel.
- `backend/src/Kernel/`: The core of the backend, handles initialization and request management.
- `backend/src/App/`: Controllers and application-level services.
- `backend/src/Core/`: Business logic and core domain services.
- `backend/src/Platform/`: Low-level platform services (DB, Logger, Loader).
- `backend/tests/`: Unit and integration tests.

## License and contributing information

This project was originally licensed under the MIT license, as of version 2.1.1, this project is now licensed under the
GNU Affero General Public License v3.0 (see: [`LICENSE`](LICENSE)). Third-party licenses can be found in [`THIRD-LICENSE-PARTY`](THIRD-PARTY-LICENSE).
All contributions are welcome as long as they respect the terms inside [`Contributing`](CONTRIBUTING.md).

## Notes

Any pull request containing sensitive information inside `.env` will have no chance to be merged.
