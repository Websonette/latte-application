# Testing profile

- Unit-test non-trivial domain and application behavior independently of presenters.
- Integration-test DI configuration, routing, presenter responses, controls, and template-facing contracts that are important to the application.
- Test browser lifecycle features with DOM-focused tests. Cover initial hydration and Naja snippet replacement when both are supported.
- Add an end-to-end smoke test for critical full-page and AJAX navigation paths when regressions would be costly.
- The merge gate should include Composer validation, PHP tests, static analysis, TypeScript type checking, browser-runtime tests, and the production Vite build when those layers exist.
- Prefer contract assertions over snapshots of large HTML documents.
