# Latte conventions

- Keep templates presentation-focused. Prepare domain decisions in application services or presenters rather than embedding them in Latte expressions.
- Use components for reusable interaction with real server-side lifecycle or state. Do not wrap every partial in a control.
- Rely on Latte escaping and use explicit no-escape output only for reviewed trusted HTML.
- Render stable `data-websonette-*` attributes for browser integration instead of coupling JavaScript to visual classes or fragile DOM depth.
- Keep concrete layouts, macros/extensions, controls, and design components in the consuming application unless they are proven adapter mechanisms.
- Make full-page and snippet rendering produce compatible component markup.
