let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.webpackConfig({
    node: {
        fs: "empty"
    },
    resolve: {
        alias: {
            "handlebars" : "handlebars/dist/handlebars.js"
        }
    },
});

mix.setPublicPath('htdocs');

mix

    .copy('resources/assets/js/jquery.payment.min.js', 'htdocs/js/jquery.payment.min.js')
    .copy('resources/assets/js/rangeslider.min.js', 'htdocs/js/rangeslider.min.js')
    .copy('resources/assets/js/recorder.js', 'htdocs/js/recorder.js')
    .copy('resources/assets/js/redactor.js', 'htdocs/js/redactor.js')
    .js('resources/assets/js/app.js', 'js')
    .sass('resources/assets/sass/app.scss', 'css')
    .copyDirectory('resources/assets/img', 'htdocs/img')
    .copyDirectory('resources/assets/audio', 'htdocs/audio');
