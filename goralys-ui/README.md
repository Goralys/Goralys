## Goralys UI

---

This package contains the ui library for the [Goralys](https://github.com/SAMSAM-55/Goralys) applications – web and
mobile.

This package bundles the reusable ui components for the web frontend and the mobile app to avoid code redundancy and
duplication across platforms.

### Content Overview

- `hooks/` – React hooks
- `lib/` – Small logic used for rendering (e.g, text area auto resize)
- `types/` – Shared TypeScript types
- `ui/` – All ui components available in Goralys.

The business logic is **not** part of this package.

### Setup

```bash
pnpm add @goralys/ui
```

### Development

This package is part of the Goralys monorepo and is managed via pnpm workspaces

```bash
# From the project root
cd goralys-ui
pnpm install
pnpm build
```

### License

This package is distributed under the LGPL-2.1-or-later license, see [LICENSE](LICENSE) for more information.

### Contact

sami.saubion@gmail.com – main developer and maintainer of the project