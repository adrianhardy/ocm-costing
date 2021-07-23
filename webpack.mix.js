const mix = require('laravel-mix');

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

mix.copy('resources/assets', 'public/assets');

mix
    .js('resources/js/app.js', 'public/js')
    .sourceMaps(false)
    .extract(['axios', 'vue', 'bootstrap-vue', 'bootstrap', 'jquery', 'popper.js', 'vue-select', 'laravel-echo', 'pusher-js', 'date-fns'])
    .sass('resources/sass/app.scss', 'public/css')
    .sass('resources/sass/mobile.scss', 'public/css');

if (mix.inProduction()) {
    mix.version();
}
