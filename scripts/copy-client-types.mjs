import { copyFile, mkdir } from 'node:fs/promises';

const destination = new URL('../dist/vite/', import.meta.url);
await mkdir(destination, { recursive: true });
await copyFile(
    new URL('../vite/client.d.ts', import.meta.url),
    new URL('client.d.ts', destination),
);