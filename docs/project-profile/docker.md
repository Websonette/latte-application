# Docker profile

- Keep Docker optional and application-owned. Reuse the repository's development image only as a reference.
- Bind-mount source in development for immediate PHP, Latte, and frontend feedback; do not copy local credentials into images.
- Install the consuming application's dependencies in its own working directory. It must consume released adapter packages rather than sibling source paths in production-like verification.
- Give each project configurable host ports so several applications can run concurrently.
- Use production images and web-server configuration appropriate to the target deployment rather than shipping the development Compose file unchanged.
