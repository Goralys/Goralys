import { defineConfig } from "tsup";
import pkg from "esbuild-plugin-tsconfig-paths";

const { tsconfigPathsPlugin } = pkg;

export default defineConfig({
    entry: ["src/index.ts"],
    format: ["esm"],
    dts: true,
    esbuildPlugins: [tsconfigPathsPlugin()],
});
