import { ComponentRuntime } from './components.js';
import { PageRuntime, normalizePageMeta, readPageMeta } from './pages.js';
import type { Awaitable, LatteApplicationOptions, LatteFeature, PageMeta } from './types.js';

function domReady(documentNode: Document): Promise<void> {
    if (documentNode.readyState !== 'loading') {
        return Promise.resolve();
    }

    return new Promise((resolve) => {
        documentNode.addEventListener('DOMContentLoaded', () => resolve(), { once: true });
    });
}

export class LatteApplication {
    readonly #features: readonly LatteFeature[];
    readonly #components: ComponentRuntime;
    readonly #pages: PageRuntime;
    readonly #onError: (error: unknown) => void;
    #started = false;

    public constructor(options: LatteApplicationOptions = {}) {
        this.#features = options.features ?? [];
        this.#onError = options.onError ?? ((error) => console.error(error));
        this.#components = new ComponentRuntime(this, options.components ?? {});
        this.#pages = new PageRuntime(this, options.pages ?? {});
    }

    public async start(documentNode: Document = document): Promise<void> {
        if (this.#started) {
            return;
        }

        this.#started = true;

        for (const feature of this.#features) {
            await feature.initialize?.(this);
        }

        await domReady(documentNode);
        await this.hydrate(documentNode);
        await this.navigate(readPageMeta(documentNode), documentNode);
    }

    public async hydrate(root: ParentNode): Promise<void> {
        for (const feature of this.#features) {
            await feature.hydrate?.(root, this);
        }

        await this.#components.hydrate(root);
    }

    public async navigate(meta: PageMeta, root: ParentNode = document): Promise<void> {
        const normalized = normalizePageMeta(meta);

        await this.#pages.navigate(normalized, root);

        for (const feature of this.#features) {
            await feature.navigate?.(normalized, root, this);
        }
    }

    public async destroy(): Promise<void> {
        for (const feature of [...this.#features].reverse()) {
            await feature.destroy?.(this);
        }

        this.#started = false;
    }

    public run(task: Awaitable<void>): void {
        void Promise.resolve(task).catch(this.#onError);
    }
}

export function createLatteApplication(options: LatteApplicationOptions = {}): LatteApplication {
    return new LatteApplication(options);
}
