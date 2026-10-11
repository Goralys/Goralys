import { defineConfig } from "tsup";

export default defineConfig({
    entry: ["src/index.ts"],
    format: ["esm"],
    dts: true,
    esbuildOptions(options) {
        options.loader = { ...options.loader, ".tsx": "tsx" };
    },
    banner: {
        js: '"use client";',
    },
});
