# Latte project architecture

This profile creates classic server-rendered Nette applications. PHP presenters and controls own request handling and HTML rendering through Latte. Browser code enhances that HTML and must preserve a usable server-rendered baseline wherever the feature allows it.

Dependency direction:

```text
consuming application
  -> websonette/latte-application
    -> websonette/web-application
```

The adapter owns Nette/Latte integration, generic browser lifecycle, Naja rehydration, and the Latte-specific Vite registry. The consuming application owns routes, presenters, concrete controls, templates, application components, entrypoints, styles, design system, domain behavior, and environment configuration.

Do not route JSON API conventions or SPA framework behavior through this adapter. A project may expose JSON endpoints when needed, but their contract belongs to the application or a dedicated API layer.
