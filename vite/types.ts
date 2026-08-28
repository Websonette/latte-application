export interface AssetConvention {
    readonly scripts: readonly string[];
    readonly styles: readonly string[];
}

export interface WebsonetteLattePluginOptions {
    /** Component directories, relative to Vite root unless absolute. */
    readonly components?: readonly string[];
    /** Module directories, relative to Vite root unless absolute. */
    readonly pages?: readonly string[];
    readonly componentAssets?: Partial<AssetConvention>;
    readonly pageAssets?: Partial<AssetConvention>;
}

export interface ResolvedWebsonetteLattePluginOptions {
    readonly components: readonly string[];
    readonly pages: readonly string[];
    readonly componentAssets: AssetConvention;
    readonly pageAssets: AssetConvention;
}
