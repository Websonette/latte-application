import {
    existsSync,
    readdirSync,
} from 'node:fs';
import {
    isAbsolute,
    relative,
    resolve,
    sep,
} from 'node:path';
import type {
    AssetConvention,
    ResolvedWebsonetteLattePluginOptions,
    WebsonetteLattePluginOptions,
} from './types.js';

interface RegistryEntry {
    readonly key: string;
    readonly script?: string;
    readonly style?: string;
}

interface Registries {
    readonly components: ReadonlyMap<string, RegistryEntry>;
    readonly pages: ReadonlyMap<string, RegistryEntry>;
}

const defaultComponentAssets: AssetConvention = {
    scripts: [
        'assets/js/index.ts',
        'assets/js/index.js',
        'assets/js/index.mjs',
    ],
    styles: [
        'assets/scss/index.scss',
        'assets/css/index.css',
    ],
};

const defaultPageAssets: AssetConvention = {
    scripts: defaultComponentAssets.scripts,
    styles: defaultComponentAssets.styles,
};

export function resolvePluginOptions(
    options: WebsonetteLattePluginOptions = {},
): ResolvedWebsonetteLattePluginOptions {
    return {
        components: options.components ?? ['components'],
        pages: options.pages ?? ['modules'],
        componentAssets: {
            scripts: options.componentAssets?.scripts ?? defaultComponentAssets.scripts,
            styles: options.componentAssets?.styles ?? defaultComponentAssets.styles,
        },
        pageAssets: {
            scripts: options.pageAssets?.scripts ?? defaultPageAssets.scripts,
            styles: options.pageAssets?.styles ?? defaultPageAssets.styles,
        },
    };
}

export function resolveSourceRoots(
    viteRoot: string,
    options: ResolvedWebsonetteLattePluginOptions,
): string[] {
    return [...options.components, ...options.pages].map((root) => (
        isAbsolute(root) ? root : resolve(viteRoot, root)
    ));
}

function directories(directory: string): string[] {
    if (!existsSync(directory)) {
        return [];
    }

    return readdirSync(directory, { withFileTypes: true })
        .filter((entry) => entry.isDirectory())
        .map((entry) => entry.name)
        .sort((left, right) => left.localeCompare(right));
}

function firstExisting(directory: string, candidates: readonly string[]): string | undefined {
    for (const candidate of candidates) {
        const file = resolve(directory, candidate);
        if (existsSync(file)) {
            return file;
        }
    }

    return undefined;
}

function createEntry(
    key: string,
    directory: string,
    convention: AssetConvention,
): RegistryEntry | undefined {
    const script = firstExisting(directory, convention.scripts);
    const style = firstExisting(directory, convention.styles);

    if (script === undefined && style === undefined) {
        return undefined;
    }

    return {
        key,
        ...(script === undefined ? {} : { script }),
        ...(style === undefined ? {} : { style }),
    };
}

function addEntry(
    registry: Map<string, RegistryEntry>,
    entry: RegistryEntry | undefined,
    source: string,
): void {
    if (entry === undefined) {
        return;
    }

    const existing = registry.get(entry.key);
    if (existing !== undefined) {
        throw new Error(
            `Duplicate Websonette registry key "${entry.key}" in ${source}; `
            + `already provided by ${existing.script ?? existing.style ?? 'unknown source'}.`,
        );
    }

    registry.set(entry.key, entry);
}

export function discoverRegistries(
    viteRoot: string,
    options: ResolvedWebsonetteLattePluginOptions,
): Registries {
    const components = new Map<string, RegistryEntry>();
    const pages = new Map<string, RegistryEntry>();

    for (const configuredRoot of options.components) {
        const componentRoot = isAbsolute(configuredRoot)
            ? configuredRoot
            : resolve(viteRoot, configuredRoot);

        for (const component of directories(componentRoot)) {
            const directory = resolve(componentRoot, component);
            addEntry(
                components,
                createEntry(component, directory, options.componentAssets),
                directory,
            );
        }
    }

    for (const configuredRoot of options.pages) {
        const pageRoot = isAbsolute(configuredRoot)
            ? configuredRoot
            : resolve(viteRoot, configuredRoot);

        for (const moduleName of directories(pageRoot)) {
            const moduleDirectory = resolve(pageRoot, moduleName);
            addEntry(
                pages,
                createEntry(moduleName, moduleDirectory, options.pageAssets),
                moduleDirectory,
            );

            const presenterRoot = resolve(moduleDirectory, 'presenters');
            for (const presenter of directories(presenterRoot)) {
                const presenterDirectory = resolve(presenterRoot, presenter);
                const presenterKey = `${moduleName}/${presenter}`;
                addEntry(
                    pages,
                    createEntry(presenterKey, presenterDirectory, options.pageAssets),
                    presenterDirectory,
                );

                const actionRoot = resolve(presenterDirectory, 'actions');
                for (const action of directories(actionRoot)) {
                    const actionDirectory = resolve(actionRoot, action);
                    addEntry(
                        pages,
                        createEntry(
                            `${presenterKey}/${action}`,
                            actionDirectory,
                            options.pageAssets,
                        ),
                        actionDirectory,
                    );
                }
            }
        }
    }

    return { components, pages };
}

function importSpecifier(file: string, viteRoot: string): string {
    const relativeFile = relative(viteRoot, file);
    const normalizedRelative = relativeFile.split(sep).join('/');

    if (!normalizedRelative.startsWith('../') && normalizedRelative !== '..') {
        return `/${normalizedRelative}`;
    }

    return `/@fs/${file.split(sep).join('/')}`;
}

function loaderSource(entry: RegistryEntry, viteRoot: string): string {
    const script = entry.script === undefined
        ? undefined
        : JSON.stringify(importSpecifier(entry.script, viteRoot));
    const style = entry.style === undefined
        ? undefined
        : JSON.stringify(importSpecifier(entry.style, viteRoot));

    if (script !== undefined && style !== undefined) {
        return `async () => { const [module] = await Promise.all([import(${script}), import(${style})]); return module; }`;
    }

    if (script !== undefined) {
        return `() => import(${script})`;
    }

    return `async () => { await import(${style}); return {}; }`;
}

function registrySource(registry: ReadonlyMap<string, RegistryEntry>, viteRoot: string): string {
    const properties = [...registry]
        .map(([key, entry]) => `    ${JSON.stringify(key)}: ${loaderSource(entry, viteRoot)}`)
        .join(',\n');

    return `Object.freeze({\n${properties}\n})`;
}

export function generateRegistrySource(
    viteRoot: string,
    options: ResolvedWebsonetteLattePluginOptions,
): string {
    const registries = discoverRegistries(viteRoot, options);

    return [
        `export const components = ${registrySource(registries.components, viteRoot)};`,
        `export const pages = ${registrySource(registries.pages, viteRoot)};`,
    ].join('\n\n');
}

export function isInsideSourceRoot(file: string, roots: readonly string[]): boolean {
    return roots.some((root) => {
        const relativeFile = relative(root, file);
        return relativeFile !== ''
            && relativeFile !== '..'
            && !relativeFile.startsWith(`..${sep}`)
            && !isAbsolute(relativeFile);
    });
}
