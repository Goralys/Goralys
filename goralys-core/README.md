## Goralys Core

---

This package contains the shared business logic for the [Goralys](https://github.com/SAMSAM-55/Goralys) applications –
web and mobile.

This package bundles the reusable code for the web frontend and the mobile app to avoid code redundancy and duplication
across platforms.

### Content Overview

- `hooks/` – Reusable React hooks (independant from the DOM)
- `lib/` – Fetch logic, event listeners, etc.
- `types/` – Shared TypeScript types

The UI components are **not** part of this package.

### Setup

```bash
pnpm add @goralys/core
```

### Development

This package is part of the Goralys monorepo and is managed via pnpm workspaces

```bash
# From the project root
cd goralys-core
pnpm install
pnpm build
pnpm dev
```

### License

This package is distributed under the LGPL-2.1-or-later license, see [LICENSE](LICENSE) for more information.

### Contact

sami.saubion@gmail.com – main developer and maintainer of the project