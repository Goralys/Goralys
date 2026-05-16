# Goralys Backend Architecture

This document describes the layered architecture of the Goralys backend, how the code is organized, and how components interact at runtime. It is intended as a concise reference for developers working on the backend implementation.

## Overview

The backend is organized into distinct layers to separate responsibilities and improve maintainability:

- Platform (infrastructure): `backend/src/Platform`
- Core (domain): `backend/src/Core`
- App (application controllers and HTTP concerns): `backend/src/App`
- Shared (cross-cutting types and exceptions): `backend/src/Shared`
- Kernel (bootstrapping and runtime wiring): `backend/src/Kernel`

Layers are designed to keep dependencies directional — higher layers may depend on lower ones, and Shared contains common types used across layers.

## Layer descriptions

1. Platform — Infrastructure (`backend/src/Platform`)

    Provides low-level, environment and specific services used by the rest of the system.

    Platform content:
    - DB (`backend/src/Platform/DB`): connection handling and query helpers.
    - Loader (`backend/src/Platform/Loader`): environment and configuration loading (wrapper around `DotEnv` by vlucas).
    - Logger (`backend/src/Platform/Logger`): centralized logging APIs and configuration.

    Platform responsibilities:
    - Provide database connectivity and execution helpers.
    - Load and validate environment/configuration values.
    - Expose logging and other infrastructure services to upper layers.

2. Core — Domain (`backend/src/Core`)

    Encapsulates domain entities, business rules, and use-cases. Organize code by domain (for example `User`, `Subject`).

    Core content:
    - Data transfer objects and enums.
    - Services implementing business use-cases.
    - Repository interfaces and domain-facing repository implementations.

    Core responsibilities:
    - Implement business rules and orchestrate domain workflows.
    - Depend on repository abstractions to persist/retrieve data; repository implementations delegate to Platform.

3. App — Application (`backend/src/App`)

    Hosts HTTP/web concerns, controllers, request/response mapping, session handling, and presentation helpers.

    App content:
    - Controllers.
    - Security helpers (CSRF utilities).
    - UI helpers (toast system).

    App responsibilities:
    - Map transport-layer inputs (HTTP, forms) to Core DTOs and services.
    - Manage session state and user-facing utilities (toasts, messages).
    - Return HTTP responses and handle input validation.

4. Shared — Cross-cutting (`backend/src/Shared`)

    Contains reusable, domain-agnostic components used by multiple layers.

    Shared content:
    - Exceptions: `backend/src/Shared/Exception` (typed exceptions for DB, User, etc.)

5. Kernel — Bootstrapping and runtime (`backend/src/Kernel`)

    Initializes and wires runtime services and global application policies (error handling, logging).

    Kernel content:
    - `GoralysKernel`: constructs and exposes services such as the logger, env loader, DB container, and toast controller; configures exception/error handlers and provides helpers for request handling.
    - `bootstrap.php`: small helpers to create and start the kernel.

    Kernel responsibilities:
    - Provide a single place to initialize platform services and to expose them to App and Core.
    - Enforce application-wide behaviors (logging, global error handling, configuration loading).

## Dependency rules (guidelines)

- App → may depend on Core, Kernel, Platform, and Shared.
- Core → may depend on Platform and Shared.
- Platform → may depend on Shared only.
- Kernel → may depend on Platform, Core and Shared (may also depend on some app utilities like toast).
- Shared → no upstream dependencies.

Maintaining these boundaries keeps the system modular and easier to test.

## Typical request / action flow

1. The Kernel boots.
2. The endpoint receives the HTTP request and validates inputs (including CSRF checks where applicable).
3. The endpoint delegates the business logic to a Core controller (may invoke Core DTOs to transfer the data if needed).
4. The controller maps request data to Core DTOs and invokes Core services (use-cases).
5. Core services execute business logic and use repository interfaces to persist or fetch data.
6. Repository implementations delegate to Platform services (DB, logger) to execute queries.
7. Core returns results/DTOs to the controller. The controller formats and returns an HTTP response and emits toasts or messages if needed.
8. Errors are logged via Platform logger and handled either by Kernel-level handlers or controller-level try/catch logic.

## Conventions

- Namespaces mirror directory structure: `Goralys\\Platform\\...`, `Goralys\\Core\\...`, `Goralys\\App\\...`, `Goralys\\Shared\\...`, `Goralys\\Kernel\\...`.
- DTOs live under `Data/` directories.
- Services implement business use-cases and depend on repository interfaces.
- Repositories handle data access; implementations that depend on Platform live near their domain when interfaces exist.
- Enums are placed under `Data/Enums` for domain-specific types or under `Platform`/`Shared` for cross-cutting enums (not-recommended).

## Environment and configuration

- Environment variables are loaded via `EnvService` from the project root (the directory containing `.env`).
- Store credentials and secrets in `.env`; never hardcode them in source control.

## File layout (snapshot)

- `backend/src/Platform/DB` — DB facade, interfaces, and services
- `backend/src/Platform/Loader` — environment loader
- `backend/src/Platform/Logger` — logger API and enums
- `backend/src/Core/User` — user domain (data, repos, services)
- `backend/src/Core/Subject` — subject domain (data, repos, services)
- `backend/src/App/*` — controllers, security, utilities (e.g., Toast)
- `backend/src/Shared/*` — shared exceptions and utilities
- `backend/src/Kernel/*` — kernel and bootstrap utilities
- `backend/src/App/Router` - router for the API and the middlwares (`backend/src/App/HTTP/Middleware`)
