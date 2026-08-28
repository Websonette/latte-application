declare module 'virtual:websonette/latte-registry' {
    import type { ComponentRegistry, PageRegistry } from '../frontend/types.js';

    export const components: ComponentRegistry;
    export const pages: PageRegistry;
}