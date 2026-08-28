import type { LatteApplication } from './application.js';
import { elementsIncludingRoot } from './dom.js';
import type { ComponentRegistry } from './types.js';

const COMPONENT_SELECTOR = '[data-websonette-component]';

export class ComponentRuntime {
    readonly #mounted = new WeakMap<HTMLElement, Set<string>>();

    public constructor(
        private readonly application: LatteApplication,
        private readonly registry: ComponentRegistry,
    ) {
    }

    public async hydrate(root: ParentNode): Promise<void> {
        for (const element of elementsIncludingRoot(root, COMPONENT_SELECTOR)) {
            const name = element.dataset.websonetteComponent?.trim() ?? '';
            if (name === '') {
                continue;
            }

            const loader = this.registry[name];
            if (loader === undefined) {
                continue;
            }

            const names = this.#mounted.get(element) ?? new Set<string>();
            const firstMount = !names.has(name);
            const module = await loader();

            if (typeof module.default === 'function') {
                await module.default(element, {
                    application: this.application,
                    firstMount,
                    name,
                });
            }

            names.add(name);
            this.#mounted.set(element, names);
        }
    }
}
