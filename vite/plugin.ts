import type { Plugin, ResolvedConfig, ViteDevServer } from 'vite';
import {
    generateRegistrySource,
    isInsideSourceRoot,
    resolvePluginOptions,
    resolveSourceRoots,
} from './registry-generator.js';
import type { WebsonetteLattePluginOptions } from './types.js';

export const virtualRegistryId = 'virtual:websonette/latte-registry';
const resolvedVirtualRegistryId = `\0${virtualRegistryId}`;

function invalidateRegistry(server: ViteDevServer): void {
    const registry = server.moduleGraph.getModuleById(resolvedVirtualRegistryId);
    if (registry !== undefined) {
        server.moduleGraph.invalidateModule(registry);
    }

    server.hot.send({ type: 'full-reload' });
}

export function websonetteLatte(
    userOptions: WebsonetteLattePluginOptions = {},
): Plugin {
    const options = resolvePluginOptions(userOptions);
    let config: ResolvedConfig | undefined;

    return {
        name: 'websonette-latte',

        configResolved(resolvedConfig) {
            config = resolvedConfig;
        },

        resolveId(id) {
            if (id === virtualRegistryId) {
                return resolvedVirtualRegistryId;
            }

            return undefined;
        },

        load(id) {
            if (id !== resolvedVirtualRegistryId) {
                return undefined;
            }

            if (config === undefined) {
                throw new Error('Websonette Latte plugin has not received resolved Vite configuration.');
            }

            return generateRegistrySource(config.root, options);
        },

        configureServer(server) {
            if (config === undefined) {
                return;
            }

            const roots = resolveSourceRoots(config.root, options);
            server.watcher.add(roots);

            const handleStructureChange = (file: string): void => {
                if (isInsideSourceRoot(file, roots)) {
                    invalidateRegistry(server);
                }
            };

            server.watcher.on('add', handleStructureChange);
            server.watcher.on('unlink', handleStructureChange);
            server.watcher.on('addDir', handleStructureChange);
            server.watcher.on('unlinkDir', handleStructureChange);
        },
    };
}
