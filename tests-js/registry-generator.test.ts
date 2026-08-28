// @vitest-environment node

import { mkdirSync, mkdtempSync, rmSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { dirname, join } from 'node:path';
import { afterEach, describe, expect, it } from 'vitest';
import {
    discoverRegistries,
    generateRegistrySource,
    resolvePluginOptions,
} from '../vite/registry-generator.js';

const temporaryDirectories: string[] = [];

function temporaryRoot(): string {
    const directory = mkdtempSync(join(tmpdir(), 'websonette-latte-vite-'));
    temporaryDirectories.push(directory);
    return directory;
}

function createFile(root: string, file: string): void {
    const target = join(root, file);
    mkdirSync(dirname(target), { recursive: true });
    writeFileSync(target, 'export default () => undefined;');
}

afterEach(() => {
    for (const directory of temporaryDirectories.splice(0)) {
        rmSync(directory, { recursive: true, force: true });
    }
});

describe('registry generator', () => {
    it('discovers component and page conventions used by Latte applications', () => {
        const root = temporaryRoot();
        createFile(root, 'components/Gallery/assets/js/index.ts');
        createFile(root, 'components/Gallery/assets/scss/index.scss');
        createFile(root, 'modules/public/assets/js/index.ts');
        createFile(root, 'modules/public/presenters/Homepage/assets/js/index.ts');
        createFile(root, 'modules/public/presenters/Homepage/actions/default/assets/js/index.ts');

        const registries = discoverRegistries(root, resolvePluginOptions());

        expect([...registries.components.keys()]).toEqual(['Gallery']);
        expect([...registries.pages.keys()]).toEqual([
            'public',
            'public/Homepage',
            'public/Homepage/default',
        ]);

        const source = generateRegistrySource(root, resolvePluginOptions());
        expect(source).toContain('"Gallery"');
        expect(source).toContain('import("/components/Gallery/assets/js/index.ts")');
        expect(source).toContain('import("/components/Gallery/assets/scss/index.scss")');
    });

    it('rejects duplicate keys contributed by multiple roots', () => {
        const root = temporaryRoot();
        createFile(root, 'components-a/Gallery/assets/js/index.ts');
        createFile(root, 'components-b/Gallery/assets/js/index.ts');

        expect(() => discoverRegistries(root, resolvePluginOptions({
            components: ['components-a', 'components-b'],
        }))).toThrow('Duplicate Websonette registry key "Gallery"');
    });

    it('allows style-only components', () => {
        const root = temporaryRoot();
        createFile(root, 'components/Theme/assets/scss/index.scss');

        const source = generateRegistrySource(root, resolvePluginOptions());

        expect(source).toContain('"Theme"');
        expect(source).toContain('return {};');
    });
});
