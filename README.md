# Websonette Latte Application

Reusable foundations for classic server-rendered Nette applications using Latte. This package is intended for HTML web applications, not JSON APIs.

> The package is currently being prepared and does not have a stable public API yet.

## Package layout

- **src/** contains PHP code shipped to applications through Composer.
- **frontend/** contains the browser runtime shipped through npm.
- **vite/** contains the Latte-specific Vite registry plugin.
- **tests/** and **tests-js/** contain PHP and TypeScript tests.
- **docs/frontend-architecture.md** defines repository boundaries and the consuming-application integration.
- **docs/project-profile/** and **scaffold/profile.json** define the adapter-owned inputs used by Websonette project orchestration.
- **examples/dev-app/** is a small consuming Nette application.
- **docker/** and **compose.yaml** provide PHP-FPM and Nginx for local development.
- **vendor/**, **node_modules/**, and **dist/** are generated and are never committed.

Application-specific code must not be added to **src/** or **frontend/**. The development application deliberately has its own **composer.json** and consumes the released package from Packagist like any real project.

## Current foundations

- **Websonette\LatteApplication\Bootstrap** creates and exposes Nette's configurator.
- **Websonette\LatteApplication\Presenter\WebBasePresenter** is the common presenter extension point.
- **Websonette\LatteApplication\FlashMessage** defines stable template-facing flash types.
- npm **@websonette/latte-application** provides the server-rendered browser lifecycle.
- npm subpath **@websonette/latte-application/naja** rehydrates replaced snippets and applies AJAX page metadata.
- npm subpath **@websonette/latte-application/vite** discovers application component and page handlers.

The same Git repository is intentionally both a Composer package and an npm package. The Vite integration belongs here because its conventions are specific to Latte-rendered HTML and Naja; it is not a universal SPA frontend.

The package intentionally leaves project-specific debug mode, Tracy setup, environment variables, configuration files, timezone, concrete controls, business components, and visual design in the consuming application. See [Frontend architecture](docs/frontend-architecture.md) for exact ownership and integration.

The project profile is descriptive tooling, not another runtime layer. The adapter declares its own Nette/Latte/Naja/Vite, layout, deployment, testing, and security decisions; the `create-project` skill in `websonette/web-application` only orchestrates those references.

## Breadcrumbs

`Websonette\WebApplication\Breadcrumbs\Breadcrumbs` is a request-scoped, UI-agnostic collection of breadcrumb items. Each item is identified by its generated URL. The Latte renderer lives separately in `Websonette\LatteApplication\UI\Breadcrumbs\BreadcrumbsControl`. `WebBasePresenter` receives the service and a `BreadcrumbsControlFactory`, so child presenters can change the trail and layouts can render `{control breadcrumbs}`.

Add items in the presenter, typically during `startup()`:

~~~php
protected function startup(): void
{
    parent::startup();

    $this->breadcrumbs->add(
        title: 'Homepage',
        url: $this->link('Home:default'),
        icon: 'home',
        description: 'Úvodní stránka',
    );

    $this->breadcrumbs->add(
        title: 'Products',
        url: $this->link('Product:default'),
        icon: 'box',
        description: 'Product overview',
    );
}
~~~

Change a title by URL:

~~~php
$this->breadcrumbs->changeTitle(
    url: $this->link('Product:default'),
    title: 'Our products',
);
~~~

Change an icon or description by URL:

~~~php
$this->breadcrumbs->changeIcon(
    url: $this->link('Product:default'),
    icon: 'package',
);

$this->breadcrumbs->changeDescription(
    url: $this->link('Product:default'),
    description: 'Current product offer',
);
~~~

Remove one item by URL:

~~~php
$this->breadcrumbs->remove(
    url: $this->link('Product:default'),
);
~~~

Render the HTML component in the layout or page template:

~~~latte
{control breadcrumbs}
~~~

Clear the whole trail:

~~~php
$this->breadcrumbs->clear();
~~~

The Breadcrumbs service is provided by `websonette/web-application` and can also feed a JSON API. `toArray()` returns a list of serializable items and never exposes internal keys:

~~~php
return new JsonResponse([
    'breadcrumbs' => $this->breadcrumbs->toArray(),
]);
~~~


## Local environment configuration

The development Docker environment works without an **.env** file by using the defaults declared in **compose.yaml**. To override ports or credentials locally, copy the optional template and edit the copy:

~~~bash
cp .env.example .env
~~~

Each project can select its own host ports and credentials in **.env**:

~~~dotenv
APP_DEBUG=1
APP_TIMEZONE=Europe/Prague

HTTP_PORT=8087
~~~

**HTTP_PORT** is the host port and should differ between concurrently running projects. After changing ports or container environment values, recreate the services:

~~~bash
docker compose up -d --force-recreate
~~~

Inspect the resolved Compose configuration at any time:

~~~bash
docker compose config
docker compose config --environment
~~~

Never commit **.env**, **.env.local**, or production credentials. This library does not create or manage application environment files. Environment generation belongs to a future root installation project, whose Composer scripts run under the application's control.

## Development with Docker

If this checkout is opened from Windows through `\\wsl$` or a mapped drive such as `U:`, run the following commands in the Ubuntu/WSL shell from the native path (for this workspace: `/var/www/websonette/packages/latte-application`). Docker Desktop cannot bind-mount the mapped Windows network drive reliably.

Build the PHP image and install both sets of dependencies:

~~~bash
docker compose build php
docker compose run --rm -w /workspace php composer install
docker compose run --rm php composer install
~~~

The first Composer command installs development tools for the library. The second installs the released **websonette/latte-application** and its transitive dependencies from Packagist.

Start the application:

~~~bash
docker compose up -d
~~~

Open <http://localhost:8080>.

Run all package checks:

~~~bash
docker compose run --rm -w /workspace php composer test
~~~

Run only one layer:

~~~bash
docker compose run --rm -w /workspace php composer tests
docker compose run --rm -w /workspace php composer analyse
~~~

Run one PHPUnit test:

~~~bash
docker compose run --rm -w /workspace php vendor/bin/phpunit tests/BootstrapTest.php
~~~

Inspect logs and stop the environment:

~~~bash
docker compose logs -f
docker compose down
~~~


## How the released package is loaded

The development application deliberately uses the same public Composer dependency as a real consuming project:

~~~json
{
  "require": {
    "websonette/latte-application": "^0.1"
  }
}
~~~

Composer resolves `websonette/web-application` transitively through `latte-application`. No sibling directories, path repositories, or copied package sources are required.
## Translations

The package uses contributte/translation directly. Register both extensions:

~~~neon
extensions:
    translation: Contributte\Translation\DI\TranslationExtension
    websonette.latte-application: Websonette\LatteApplication\DI\LatteApplicationExtension
~~~

LatteApplicationExtension implements Contributte's TranslationProviderInterface
and provides the package directory resources/translations. Translation files use
Contributte's domain.locale.format convention, for example websonette.cs.neon.
The public key keeps the domain prefix:

~~~php
$translator->translate('websonette.latte-application.flash.success');
~~~

~~~latte
{_'websonette.latte-application.flash.success'}
~~~

Contributte interprets websonette as the domain and
latte-application.flash.success as the catalogue key. Applications can override
package messages by adding their own translation directory:

~~~neon
translation:
    dirs:
        - %rootDir%/app/translations
~~~

For example, app/translations/websonette.cs.neon can contain:

~~~neon
latte-application:
    flash:
        success: Hotovo
~~~

Pluralization, parameters, locale resolution, custom loaders, and alternative
translation sources are configured through Contributte/Symfony Translation.
## Intended scope

The package may provide reusable foundations for:

- a thin base presenter for HTML applications;
- typed and extended flash messages;
- breadcrumb and page metadata coordination;
- Latte-oriented presenter and template integration;
- the browser lifecycle and Naja rehydration for server-rendered HTML;
- opt-in generic behaviors such as Nette Forms, AJAX forms, flashes, and modal coordination;
- the Vite registry that connects application-owned component/page folders to that lifecycle;
- common application-level contracts, value objects, and small DI integrations.

These browser behaviors are mechanisms, not real business components. API presenters, JSON response conventions, project-specific domain rules, concrete controls, application handlers, layouts, styling, and frontend design systems remain outside this package. React/Vue SPA behavior is also outside this package.

## Installation

The Composer package is installed from Packagist. The npm package is not published yet and can be consumed through a workspace or file dependency during its remaining development.

Composer and the future npm package are installed independently:

~~~bash
composer require websonette/latte-application:^0.1
pnpm add @websonette/latte-application
~~~

Run the npm checks in this repository with:

~~~bash
pnpm test
~~~

Release and npm-organization steps are documented in [Frontend architecture](docs/frontend-architecture.md#local-installation-and-publication).

The package follows the organization-wide [contribution guidelines](https://github.com/Websonette/.github/blob/main/CONTRIBUTING.md) and [CI contract](https://github.com/Websonette/.github/blob/main/docs/CI.md).

## License

Websonette Latte Application is licensed under the MIT License.
