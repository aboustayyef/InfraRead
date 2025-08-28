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

mix.browserSync({proxy: "http://infraread.test", browser: 'Firefox Developer Edition' })
   .js('resources/js/app.js', 'public/js')
   .postCss('resources/css/app.css', 'public/css', [
        require('postcss-import'),
        require('tailwindcss'),
        require('autoprefixer'),
    ])
   .options({
        processCssUrls: false
   });
mix.js('resources/js/admin.js', 'public/js').version();
