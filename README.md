# Websonette Latte Application

Shared foundations for classic server-rendered Nette applications using Latte. This package is intended for HTML web applications, not JSON APIs.

> The package is currently being prepared and does not have a stable public API yet.

## Intended scope

The package may provide reusable foundations for:

- a thin base presenter for HTML applications;
- typed and extended flash messages;
- breadcrumb and page metadata coordination;
- Latte-oriented presenter and template integration;
- common application-level contracts and value objects;
- small DI integrations needed by these features.

## Explicitly outside the package

- API presenters, JSON response conventions, and API authentication;
- project-specific domain or authorization rules;
- mandatory localization dependencies;
- concrete UI widgets, grids, modals, or frontend design systems.

Localization, security, and larger UI capabilities should remain optional packages. The core package must stay usable by a small web that does not need those features.

## Installation

The package is not published yet. After the first stable release it will be installable through Composer:

```bash
composer require websonette/latte-application
```

## Development

Run all checks exposed by the package:

```bash
composer test
```

The package follows the organization-wide [contribution guidelines](https://github.com/Websonette/.github/blob/main/CONTRIBUTING.md) and [CI contract](https://github.com/Websonette/.github/blob/main/docs/CI.md).

## License

Websonette Latte Application is licensed under the MIT License.

