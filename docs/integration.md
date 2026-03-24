---
name: wp-module-onboarding-data
title: Integration
description: How the module registers and integrates.
updated: 2025-03-18
---

# Integration

## How the module registers

The module is non-toggleable and registers with the Newfold Module Loader. It provides the onboarding data interface used by wp-module-onboarding and other modules (e.g. next-steps, ecommerce). Other code accesses onboarding state and site info through this module’s API.

## Dependencies

See [dependencies.md](dependencies.md).
