# Consuming application layout

Prefer a conventional Nette layout and keep the public document root narrow:

```text
app/
  Bootstrap.php
  Presentation/
  Router/
  UI/
config/
  common.neon
  services.neon
resources/
  translations/
temp/
log/
vendor/
web/
  index.php
  assets/
    components/
    modules/
    entry/application.ts
  vite.config.ts
```

Names may follow an existing project convention. Preserve clear ownership instead of mechanically renaming a working application.

- `app/Presentation/` owns presenters and page templates.
- `app/UI/` owns application-specific controls and their templates.
- `web/assets/components/` owns browser handlers and styles for concrete controls.
- `web/assets/modules/` owns module/presenter/action browser handlers.
- `web/assets/entry/` owns application composition and global imports.
- `websonette/latte-application` supplies mechanisms; never copy its source into the project.

Generated Vite output, Composer dependencies, logs, temp data, and local environment files are not source files.
