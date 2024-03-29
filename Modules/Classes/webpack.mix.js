const { mix } = require('laravel-mix');
require('laravel-mix-merge-manifest');

mix.setPublicPath('../../../public_html').mergeManifest();

mix.js(__dirname + '/Resources/assets/js/app.js', 'js/classes.js')
    .sass( __dirname + '/Resources/assets/sass/app.scss', 'css/classes.css');

if (mix.inProduction()) {
    mix.version();
}