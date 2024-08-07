const Encore = require('@symfony/webpack-encore')
const path = require('path')
const {CKEditorTranslationsPlugin} = require('@ckeditor/ckeditor5-dev-translations');
const {styles: ckeditorstyles} = require('@ckeditor/ckeditor5-dev-utils');


// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev')
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')

    .copyFiles([{from: './assets/images', to: 'images/[path][name].[ext]'}])

    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('app', './assets/js/App.tsx')
    .addStyleEntry('admin', './assets/scss/Admin.scss')

    //.addEntry('page1', './assets/js/page1.js')
    //.addEntry('page2', './assets/js/page2.js')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    .configureBabel((config) => {
        config.plugins.push('@babel/plugin-transform-runtime')
    })

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage'
        config.corejs = 3
    })

    // enables Sass/SCSS support
    .enableSassLoader()
    .enablePostCssLoader()

    // uncomment if you use TypeScript
    .enableTypeScriptLoader()
    .enableForkedTypeScriptTypesChecking()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()

    .enableReactPreset()

    /**
     * #######################################
     * CKEditor build specific configuration
     * #######################################
     *
     * NOTE: We could use a separate webpack config and build step for the CKEditor as it will
     *       only change on CKEditor updates/changes. This would speed up dev builds.
     *       We then would have to import the built CustomEditor in our React code.
     */
    .addPlugin(new CKEditorTranslationsPlugin({
        language: 'de',
        additionalLanguages: 'all',
        addMainLanguageTranslationsToAllAssets: true
    }))

    // Use raw-loader for CKEditor 5 SVG files.
    .addRule({
        test: /ckeditor5-[^/\\]+[/\\]theme[/\\]icons[/\\][^/\\]+\.svg$/,
        loader: 'raw-loader'
    })

    // Use raw-loader for Fontawesome raw SVG files.
    .addRule({
        test: /fortawesome\/fontawesome-pro\/svgs\//,
        loader: 'raw-loader'
    })

    // Configure other image loaders to exclude CKEditor 5 and Fontawesome raw SVG files.
    .configureLoaderRule('images', loader => {
        loader.exclude = /ckeditor5-[^/\\]+[/\\]theme[/\\]icons[/\\][^/\\]+\.svg$|fortawesome\/fontawesome-pro\/svgs\//;
    })

    // Configure PostCSS loader.
    .addLoader({
        test: /ckeditor5-[^/\\]+[/\\]theme[/\\].+\.css$/,
        loader: 'postcss-loader',
        options: {
            postcssOptions: ckeditorstyles.getPostCssConfig({
                themeImporter: {
                    themePath: require.resolve('@ckeditor/ckeditor5-theme-lark')
                }
            })
        }
    })

/*
 * #######################################
 * END CKEditor specific configuration
 * #######################################
 */


const config = Encore.getWebpackConfig()
config.resolve.alias = {
    StimulusControllers: path.resolve(
        __dirname,
        './assets/js/StimulusControllers'
    ),
    Components: path.resolve(__dirname, './assets/js/Components'),
    types: path.resolve(__dirname, './assets/js/types'),
}

module.exports = config
