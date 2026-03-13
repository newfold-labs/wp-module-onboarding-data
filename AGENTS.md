# Agent guidance – wp-module-onboarding-data

This file gives AI agents a quick orientation to the repo. For full detail, see the **docs/** directory.

## What this project is

- **wp-module-onboarding-data** – A non-toggleable module providing a standardized interface for onboarding data. Used by wp-module-onboarding and other modules. Depends on wp-module-installer, wp-module-patterns, wp-module-ai, wp-module-data, wp-module-performance, wp-module-install-checker, wp-module-survey, mustache, wp-forge/wp-upgrade-handler. Maintained by Newfold Labs.

- **Stack:** PHP 7.3+. See docs/dependencies.md.

- **Architecture:** Registers with the loader; provides data API for onboarding. See docs/integration.md.

## Key paths

| Purpose | Location |
|---------|----------|
| Includes | `includes/` |
| Tests | `tests/` |

## Essential commands

```bash
composer install
composer run lint
composer run fix
composer run test
```

## Documentation

- **Full documentation** is in **docs/**. Start with **docs/index.md**.
- **CLAUDE.md** is a symlink to this file (AGENTS.md).

---

## Keeping documentation current

When you change code, features, or workflows, update the docs. When adding or changing dependencies, update **docs/dependencies.md**. When cutting a release, update **docs/changelog.md**.
