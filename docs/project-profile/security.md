# Security profile

- Use Nette and Latte protections for CSRF, escaping, session cookies, and response handling; do not replace them with custom primitives.
- Authorize every server action independently of rendered links, controls, or Naja behavior.
- Treat snippet payloads as server responses with the same output-encoding and data-minimization requirements as full pages.
- Do not expose debug mode, Tracy, stack traces, environment data, or Vite development servers in production.
- Keep upload destinations outside executable/public source paths and validate type, size, and ownership.
- Review any use of raw HTML, dynamic template names, redirects, and return URLs at their trust boundary.
