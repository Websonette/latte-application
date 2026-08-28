import type { LatteApplication } from './application.js';

export type Awaitable<T> = T | Promise<T>;

export interface ComponentContext {
    readonly application: LatteApplication;
    readonly firstMount: boolean;
    readonly name: string;
}

export type ComponentInitializer = (
    element: HTMLElement,
    context: ComponentContext,
) => Awaitable<void>;

export interface ComponentModule {
    readonly default?: ComponentInitializer;
}

export type ComponentLoader = () => Promise<ComponentModule>;
export type ComponentRegistry = Readonly<Record<string, ComponentLoader>>;

export interface PageMeta {
    readonly module?: string;
    readonly presenter?: string;
    readonly action?: string;
}

export interface PageContext extends PageMeta {
    readonly application: LatteApplication;
    readonly key: string;
    readonly root: ParentNode;
}

export type PageInitializer = (context: PageContext) => Awaitable<void>;

export interface PageModule {
    readonly default?: PageInitializer;
}

export type PageLoader = () => Promise<PageModule>;
export type PageRegistry = Readonly<Record<string, PageLoader>>;

export interface LatteFeature {
    initialize?(application: LatteApplication): Awaitable<void>;
    hydrate?(root: ParentNode, application: LatteApplication): Awaitable<void>;
    navigate?(meta: PageMeta, root: ParentNode, application: LatteApplication): Awaitable<void>;
    destroy?(application: LatteApplication): Awaitable<void>;
}

export interface LatteApplicationOptions {
    readonly components?: ComponentRegistry;
    readonly pages?: PageRegistry;
    readonly features?: readonly LatteFeature[];
    readonly onError?: (error: unknown) => void;
}
