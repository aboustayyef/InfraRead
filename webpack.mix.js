const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js').postCss('resources/css/app.css', 'public/css', [
    require('postcss-import'),
    require('tailwindcss'),
    require('autoprefixer'),
]);
mix.sass('resources/sass/app_bulma.scss', 'public/css').version();
mix.sass('resources/sass/administration/admin_bootstrap.scss', 'public/css').version();
mix.js('resources/js/admin.js', 'public/js').version();

// V2 (columns view)

mix.js('resources/v2/js/app.js', 'public/v2/js').version();
mix.sass('resources/v2/css/app.scss', 'public/v2/css').version();