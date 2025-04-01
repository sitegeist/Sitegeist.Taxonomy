const esbuild = require("esbuild");
const { cssModules } = require("esbuild-plugin-lightningcss-modules");
const extensibilityMap = require("@neos-project/neos-ui-extensibility/extensibilityMap.json");
const isWatch = process.argv.includes("--watch");

/** @type {import("esbuild").BuildOptions} */
const options = {
  logLevel: "info",
  bundle: true,
  target: "es2020",
  entryPoints: { Plugin: "src/index.js" },
  loader: { ".js": "tsx" },
  outdir: "../../../Public/JavaScript/TaxonomyEditor",
  alias: extensibilityMap,
  plugins: [
    cssModules({
      targets: {
        chrome: 80,
      },
      drafts: {
        nesting: true,
      },
    }),
  ],
};

if (isWatch) {
  esbuild.context(options).then((ctx) => ctx.watch());
} else {
  esbuild.build(options);
}
