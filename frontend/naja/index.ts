import type { LatteApplication } from '../application.js';
import { writePageMeta } from '../pages.js';
import type { LatteFeature, PageMeta } from '../types.js';

interface NajaEventDetail {
    readonly payload?: {
        readonly websonettePageMeta?: PageMeta;
    };
    readonly snippet?: ParentNode;
}

interface NajaLike extends EventTarget {
    readonly snippetHandler: EventTarget;
    initialize(): void;
}

export interface NajaFeatureOptions {
    readonly initialize?: boolean;
}

export function createNajaFeature(
    naja: NajaLike,
    options: NajaFeatureOptions = {},
): LatteFeature {
    const initialize = options.initialize ?? true;
    let application: LatteApplication | undefined;
    let queue = Promise.resolve();

    const schedule = (task: (current: LatteApplication) => Promise<void>): void => {
        const current = application;
        if (current === undefined) {
            return;
        }

        queue = queue.catch(() => undefined).then(() => task(current));
        current.run(queue);
    };

    const afterUpdate = (event: Event): void => {
        const snippet = (event as CustomEvent<NajaEventDetail>).detail?.snippet;
        if (snippet !== undefined) {
            schedule((current) => current.hydrate(snippet));
        }
    };

    const success = (event: Event): void => {
        const meta = (event as CustomEvent<NajaEventDetail>).detail?.payload?.websonettePageMeta;
        if (meta !== undefined) {
            const normalized = writePageMeta(meta, document);
            schedule((current) => current.navigate(normalized, document));
        }
    };

    return {
        initialize(currentApplication) {
            application = currentApplication;
            queue = Promise.resolve();
            naja.snippetHandler.addEventListener('afterUpdate', afterUpdate);
            naja.addEventListener('success', success);

            if (initialize) {
                naja.initialize();
            }
        },
        destroy() {
            naja.snippetHandler.removeEventListener('afterUpdate', afterUpdate);
            naja.removeEventListener('success', success);
            application = undefined;
        },
    };
}

export type { NajaLike };