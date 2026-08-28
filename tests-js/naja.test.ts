import { describe, expect, it, vi } from 'vitest';
import { createLatteApplication } from '../frontend/application.js';
import { createNajaFeature } from '../frontend/naja/index.js';

class FakeNaja extends EventTarget {
    public readonly snippetHandler = new EventTarget();
    public readonly initialize = vi.fn();
}

describe('createNajaFeature', () => {
    it('rehydrates updated snippets and loads page metadata from a successful response', async () => {
        document.body.dataset.websonettePresenter = 'Old';
        const naja = new FakeNaja();
        const component = vi.fn();
        const page = vi.fn();
        const errors: unknown[] = [];
        const application = createLatteApplication({
            components: {
                Example: async () => ({ default: component }),
            },
            pages: {
                public: async () => ({ default: page }),
            },
            features: [createNajaFeature(naja)],
            onError: (error) => errors.push(error),
        });
        await application.start(document);

        const snippet = document.createElement('div');
        snippet.dataset.websonetteComponent = 'Example';
        naja.snippetHandler.dispatchEvent(new CustomEvent('afterUpdate', {
            detail: { snippet },
        }));
        naja.dispatchEvent(new CustomEvent('success', {
            detail: {
                payload: {
                    websonettePageMeta: { module: 'public' },
                },
            },
        }));
        await vi.waitFor(() => {
            expect(component).toHaveBeenCalledOnce();
            expect(page).toHaveBeenCalledOnce();
        });

        expect(naja.initialize).toHaveBeenCalledOnce();
        expect(document.body.dataset.websonetteModule).toBe('public');
        expect(document.body.dataset.websonettePresenter).toBeUndefined();
        expect(errors).toEqual([]);
    });
});
