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

mix.js('resources/js/administration/admin.js', 'public/js').version();
mix.js('resources/js/app.js', 'public/js').version()
mix.sass('resources/sass/app.scss', 'public/css').version()
mix.sass('resources/sass/administration/admin.scss', 'public/css').version() ;

// V2

mix.js('resources/v2/js/app.js', 'public/v2/js').version();
mix.sass('resources/v2/css/app.scss', 'public/v2/css').version();
