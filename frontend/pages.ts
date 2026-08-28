import type { LatteApplication } from './application.js';
import { documentFromRoot } from './dom.js';
import type { PageMeta, PageRegistry } from './types.js';

function normalize(value: string | undefined): string | undefined {
    const normalized = value?.trim();
    return normalized === undefined || normalized === '' ? undefined : normalized;
}

export function normalizePageMeta(meta: PageMeta): PageMeta {
    const moduleName = normalize(meta.module);
    const presenter = normalize(meta.presenter);
    const action = normalize(meta.action);

    return {
        ...(moduleName === undefined ? {} : { module: moduleName }),
        ...(presenter === undefined ? {} : { presenter }),
        ...(action === undefined ? {} : { action }),
    };
}

export function readPageMeta(root: ParentNode = document): PageMeta {
    const body = documentFromRoot(root).body;
    const moduleName = body.dataset.websonetteModule;
    const presenter = body.dataset.websonettePresenter;
    const action = body.dataset.websonetteAction;

    return normalizePageMeta({
        ...(moduleName === undefined ? {} : { module: moduleName }),
        ...(presenter === undefined ? {} : { presenter }),
        ...(action === undefined ? {} : { action }),
    });
}

export function writePageMeta(meta: PageMeta, documentNode: Document = document): PageMeta {
    const normalized = normalizePageMeta(meta);
    const values = {
        websonetteModule: normalized.module,
        websonettePresenter: normalized.presenter,
        websonetteAction: normalized.action,
    };

    for (const [key, value] of Object.entries(values)) {
        if (value === undefined) {
            delete documentNode.body.dataset[key];
        } else {
            documentNode.body.dataset[key] = value;
        }
    }

    return normalized;
}
export function pageKeys(meta: PageMeta): string[] {
    const normalized = normalizePageMeta(meta);
    const keys: string[] = [];

    if (normalized.module !== undefined) {
        keys.push(normalized.module);
    }

    if (normalized.module !== undefined && normalized.presenter !== undefined) {
        keys.push(`${normalized.module}/${normalized.presenter}`);
    }

    if (
        normalized.module !== undefined
        && normalized.presenter !== undefined
        && normalized.action !== undefined
    ) {
        keys.push(`${normalized.module}/${normalized.presenter}/${normalized.action}`);
    }

    return keys;
}

export class PageRuntime {
    public constructor(
        private readonly application: LatteApplication,
        private readonly registry: PageRegistry,
    ) {
    }

    public async navigate(meta: PageMeta, root: ParentNode): Promise<void> {
        const normalized = normalizePageMeta(meta);

        for (const key of pageKeys(normalized)) {
            const loader = this.registry[key];
            if (loader === undefined) {
                continue;
            }

            const module = await loader();
            if (typeof module.default === 'function') {
                await module.default({
                    ...normalized,
                    application: this.application,
                    key,
                    root,
                });
            }
        }
    }
}
