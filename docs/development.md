---
name: wp-module-onboarding-data
title: Development
description: Lint, test, and workflow.
updated: 2025-03-18
---

# Development

## Linting

- **PHP:** `composer run lint`, `composer run fix`. Uses repo phpcs config.

## Testing

- **Codeception wpunit:** `composer run test`, `composer run test-coverage`.

## Workflow

1. Make changes in `includes/`.
2. Run `composer run lint` and `composer run test` before committing.
3. When changing dependencies, update [dependencies.md](dependencies.md). When cutting a release, update **docs/changelog.md**.
