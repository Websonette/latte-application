import { beforeEach, describe, expect, it, vi } from 'vitest';
import { createLatteApplication } from '../frontend/application.js';
import type { LatteFeature } from '../frontend/types.js';

describe('LatteApplication', () => {
    beforeEach(() => {
        document.body.innerHTML = '';
        document.body.removeAttribute('data-websonette-module');
        document.body.removeAttribute('data-websonette-presenter');
        document.body.removeAttribute('data-websonette-action');
    });

    it('loads a component by its server-rendered name and reports repeated hydration', async () => {
        document.body.innerHTML = '<section data-websonette-component="Example"></section>';
        const initialize = vi.fn();
        const loader = vi.fn(async () => ({ default: initialize }));
        const application = createLatteApplication({
            components: { Example: loader },
        });

        await application.hydrate(document);
        await application.hydrate(document);

        expect(loader).toHaveBeenCalledTimes(2);
        expect(initialize).toHaveBeenCalledTimes(2);
        expect(initialize.mock.calls[0]?.[1]).toMatchObject({
            firstMount: true,
            name: 'Example',
        });
        expect(initialize.mock.calls[1]?.[1]).toMatchObject({
            firstMount: false,
            name: 'Example',
        });
    });

    it('includes the hydration root itself and ignores components without a handler', async () => {
        const root = document.createElement('div');
        root.dataset.websonetteComponent = 'Known';
        root.innerHTML = '<span data-websonette-component="Missing"></span>';
        document.body.append(root);

        const initialize = vi.fn();
        const application = createLatteApplication({
            components: {
                Known: async () => ({ default: initialize }),
            },
        });

        await application.hydrate(root);

        expect(initialize).toHaveBeenCalledOnce();
        expect(initialize.mock.calls[0]?.[0]).toBe(root);
    });

    it('loads module, presenter and action handlers in order', async () => {
        document.body.dataset.websonetteModule = 'public';
        document.body.dataset.websonettePresenter = 'Homepage';
        document.body.dataset.websonetteAction = 'default';
        const calls: string[] = [];
        const application = createLatteApplication({
            pages: {
                public: async () => ({ default: ({ key }) => { calls.push(key); } }),
                'public/Homepage': async () => ({ default: ({ key }) => { calls.push(key); } }),
                'public/Homepage/default': async () => ({ default: ({ key }) => { calls.push(key); } }),
            },
        });

        await application.start(document);

        expect(calls).toEqual([
            'public',
            'public/Homepage',
            'public/Homepage/default',
        ]);
    });

    it('runs feature lifecycle around hydration and navigation', async () => {
        const calls: string[] = [];
        const feature: LatteFeature = {
            initialize: () => { calls.push('initialize'); },
            hydrate: () => { calls.push('hydrate'); },
            navigate: () => { calls.push('navigate'); },
            destroy: () => { calls.push('destroy'); },
        };
        const application = createLatteApplication({ features: [feature] });

        await application.start(document);
        await application.destroy();

        expect(calls).toEqual([
            'initialize',
            'hydrate',
            'navigate',
            'destroy',
        ]);
    });
});
