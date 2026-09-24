# Naja and Vite conventions

- Compose `createLatteApplication()` in one application-owned entrypoint.
- Add `createNajaFeature()` only when the project uses Naja. Naja is not a substitute for explicit server behavior or API contracts.
- Hydration handlers must tolerate repeated calls. Use `firstMount` for one-time element setup and clean up resources owned by optional features.
- Publish page metadata in an AJAX payload only for navigation that changes page identity; ordinary component requests must not rerun page handlers.
- Configure `websonetteLatte()` with application paths. The adapter must not know the consuming repository path.
- Keep JavaScript and styles independently optional. Reject duplicate registry keys rather than relying on discovery order.
- Vite entrypoints, aliases, build destination, and CSS tooling belong to the consuming application. Do not add Tailwind, a component library, or another bundler as an adapter default.

The detailed registry and lifecycle contract remains in [`../frontend-architecture.md`](../frontend-architecture.md).
