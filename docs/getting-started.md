# Getting started

## Prerequisites

- **PHP** 7.3+.
- **Composer.** The module has multiple Newfold and third-party runtime dependencies; see docs/dependencies.md.

## Install

```bash
composer install
```

## Run tests

```bash
composer run test
composer run test-coverage
```

## Lint

```bash
composer run lint
composer run fix
```

## Using in a host plugin

This module is typically pulled in as a dependency of wp-module-onboarding or other modules. See [integration.md](integration.md).
