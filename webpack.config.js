var Encore = require('@symfony/webpack-encore');
var path = require('path');
// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    // Images
    .copyFiles({
      from: './assets/img',
      to: 'images/[path][name].[hash:8].[ext]',
      pattern: /\.(png|jpg|jpeg|svg)$/
    })

    /*
     * ENTRY CONFIG
     *
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('app', './assets/js/app.js')
    .addEntry('user-form', './assets/js/user-form.js')
    .addEntry('availability-form', './assets/js/availability-form.js')
    .addEntry('forecast', './assets/js/forecast.js')
    .addEntry('planning', './assets/js/planning.js')
    .addEntry('availability-table', './assets/js/availability-table.js')
    .addEntry('availabilitable-list', './assets/js/availabilitable-list.js')
    .addEntry('mission-type-form', './assets/js/mission-type-form.js')
    .addEntry('asset-type-form', './assets/js/asset-type-form.js')
    .addEntry('missions', './assets/js/missions.js')

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

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

    // enables Sass/SCSS support
    .enableSassLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()

    // uncomment if you use API Platform Admin (composer req api-admin)
    //.enableReactPreset()
    //.addEntry('admin', './assets/js/admin.js')

    // This alias fixes a bug in the daterangepicker import
    // See https://github.com/Eonasdan/bootstrap-datetimepicker/issues/1319
    .addAliases({'jquery': path.join(__dirname, 'node_modules/jquery/src/jquery')})
;

module.exports = Encore.getWebpackConfig();
