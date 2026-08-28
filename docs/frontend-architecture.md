# Frontend architecture for Latte applications

This document is the current boundary. It intentionally describes only the server-rendered Latte application; a future SPA application may use a different runtime and adapter.

## Repository ownership

| Repository | Package | Responsibility |
| --- | --- | --- |
| `websonette/web-application` | Composer `websonette/web-application` | UI-independent PHP state and contracts usable by HTML or JSON applications. It has no browser or Vite code. |
| `websonette/latte-application` | Composer `websonette/latte-application` and npm `@websonette/latte-application` | Nette/Latte integration, browser lifecycle for server-rendered HTML, Naja rehydration, and the Vite registry plugin. |
| consuming application (for example Grunt) | application Composer and npm dependencies | Concrete controls, business components, layouts, global styles, entrypoints, and application-specific handlers. |
| `websonette/frontend`, `websonette/ui` | none required by this architecture | Reserved experiments. They are not dependencies of `latte-application` and should receive no duplicated code. Decide later whether to archive or repurpose them. |

One Git repository may be published to two registries. Composer reads `composer.json`; npm reads `package.json`. A separate `vite` repository is not needed because this plugin implements conventions belonging specifically to a Latte/Naja application.

## What is a component here

A concrete component is application code, for example `OrderEditor`, `RaceCategoryPanel`, or `GruntRace-FaqList`. Its PHP control, Latte template, JavaScript handler, and CSS live in the consuming application.

Reusable form initialization, AJAX form handling, modal lifecycle, AJAX modal coordination, flash-message presentation, and similar mechanisms are not concrete business components. They belong in `latte-application` as opt-in browser features. They will be built on the `LatteFeature` lifecycle and may have their own optional peer dependencies. They must not be copied from Grunt; Grunt is only a behavioral reference for a clean TypeScript implementation.

A future React/Vue SPA will normally use its framework's own components, forms, routing, and modal lifecycle. It therefore should not import these Latte/Naja features. No speculative SPA adapter is part of this package now.

## Runtime flow

1. A Latte control renders `data-websonette-component="ComponentName"`.
2. The consuming application's Vite configuration runs `websonetteLatte()`.
3. At build/dev time the plugin scans the configured application folders and generates `virtual:websonette/latte-registry`.
4. The application entrypoint passes the generated component and page registries to `createLatteApplication()`.
5. Initial HTML is hydrated after DOM ready.
6. After Naja replaces a snippet, only that snippet is hydrated again.
7. When an AJAX navigation payload contains `websonettePageMeta`, the runtime updates the namespaced `<body>` data attributes and runs module, presenter, and action handlers in this order.

No wrapper is written for each JavaScript class or function. A wrapper exists only at an integration boundary: the application entrypoint, a Vite plugin, or an optional lifecycle feature. A component handler is simply the default export in the conventional application folder.

## Consuming application layout

With Vite rooted at the application's `web/` directory, use this structure:

```text
web/
  assets/
    components/
      ComponentName/
        assets/
          js/index.ts
          scss/index.scss
    modules/
      public/
        assets/js/index.ts
        assets/scss/index.scss
        presenters/
          Homepage/
            assets/js/index.ts
            assets/scss/index.scss
            actions/
              default/
                assets/js/index.ts
                assets/scss/index.scss
    entry/application.ts
  vite.config.ts
```

JavaScript and style files are independently optional. A style-only directory is valid. Duplicate component or page keys across configured roots are rejected instead of being selected implicitly.

## Vite configuration

```ts
import nette from '@nette/vite-plugin';
import { defineConfig } from 'vite';
import { websonetteLatte } from '@websonette/latte-application/vite';

export default defineConfig({
    plugins: [
        nette({ entry: ['assets/entry/application.ts'] }),
        websonetteLatte({
            components: ['assets/components'],
            pages: ['assets/modules'],
        }),
    ],
});
```

The paths are application paths and may be changed through plugin options. The core package never needs to know the Grunt repository path.

Add the virtual module declarations to the consuming `tsconfig.json`:

```json
{
  "compilerOptions": {
    "types": ["@websonette/latte-application/vite/client"]
  }
}
```

## Application entrypoint

```ts
import naja from 'naja';
import { createLatteApplication } from '@websonette/latte-application';
import { createNajaFeature } from '@websonette/latte-application/naja';
import {
    components,
    pages,
} from 'virtual:websonette/latte-registry';

const application = createLatteApplication({
    components,
    pages,
    features: [
        createNajaFeature(naja),
    ],
});

await application.start();
```

Application-wide styles and application-specific feature modules remain ordinary imports in this entrypoint.

A component handler needs no Vite wrapper:

```ts
import type { ComponentInitializer } from '@websonette/latte-application';

const initialize: ComponentInitializer = (element, { firstMount }) => {
    if (firstMount) {
        // Bind behavior that should be installed once for this DOM element.
    }
};

export default initialize;
```

## Server page metadata

The package cannot derive the application module key because domain/project routing is application-specific. The consuming presenter creates the generic value object and publishes it:

```php
use Websonette\LatteApplication\Frontend\PageMeta;

protected function beforeRender(): void
{
    parent::beforeRender();

    $this->setWebsonettePageMeta(new PageMeta(
        module: $this->resolveApplicationModuleKey(),
        presenter: $this->resolveFrontendPresenterKey(),
        action: $this->getAction(),
    ));
}
```

The layout renders the namespaced attributes:

```latte
<body n:attr="
    data-websonette-module => $websonettePageMeta->module,
    data-websonette-presenter => $websonettePageMeta->presenter,
    data-websonette-action => $websonettePageMeta->action
">
```

Only an AJAX operation that represents page navigation should publish the payload as well:

```php
$this->setWebsonettePageMeta($pageMeta, publishToPayload: true);
```

This prevents page-level handlers from rerunning after unrelated AJAX form or component requests.

## Local installation and publication

Until npm publication, a consuming workspace can install this checkout using a workspace dependency or a file dependency. Do not copy `dist/` or source files into the application.

Before the first npm release:

1. Replace package version `0.0.0` with the release version matching the Git tag.
2. Run `pnpm test` and `pnpm pack --dry-run`.
3. Create/sign in to the npm account that owns the `@websonette` organization.
4. Publish with `pnpm publish --access public`.
5. Register the same Git repository on Packagist for Composer releases.

Publishing is intentionally not automated or performed from a development checkout before registry ownership and release policy are decided.

## Implementation order

The foundation now contains the runtime, component/page registries, Naja adapter, page metadata contract, tests, and production build. Continue in this order:

1. Integrate the package locally into the development application and replace its `core/componentLoader.js`, `core/pageLoader.js`, and page-meta Naja extension.
2. Add reusable Latte-only features one at a time: Nette Forms initialization, AJAX form conventions, flashes, modal lifecycle, then AJAX modal coordination.
3. For every migrated feature, write TypeScript tests from required behavior and remove the corresponding application-global implementation only after application verification.
4. Keep concrete application components and all design/business code in the application.
5. Publish Composer/npm releases only after the local consumer works end to end.