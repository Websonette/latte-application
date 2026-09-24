# Deployment profile

- Build Composer and frontend dependencies reproducibly in CI and deploy only required runtime files.
- Point the web server at the application's `web/` directory whenever hosting permits it.
- Publish Vite's manifest and fingerprinted assets with the PHP release so templates and assets cannot drift between versions.
- Warm required caches and validate Nette configuration before switching traffic.
- Keep writable `temp/` and `log/` paths outside immutable release content or provision them explicitly.
- If shared hosting exposes a broader document root, deny direct access to source, configuration, dependencies, `.env`, and VCS paths and test the rules.
