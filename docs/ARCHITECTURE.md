# SDK Architecture

## Purpose

The Foundwell SDK is the supported boundary between Foundwell applications and the Foundwell Platform.

## Rules

1. Product code calls SDK capabilities, not raw Platform endpoints.
2. The SDK contains transport and integration concerns, not product business logic.
3. Public interfaces remain stable within a major version.
4. Platform failures must be represented through typed exceptions.
5. Composer is optional so products can deploy in restricted hosting environments.

## Initial Public Surface

- `Foundwell\\Client`
- `Foundwell\\Config`
- `Foundwell\\Contracts\\*`
- `Foundwell\\Exceptions\\*`

Service implementations will be introduced in later releases after the API contract is reviewed.
