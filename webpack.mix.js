const mix = require('laravel-mix');
const webpack = require('webpack');
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

/*
mix.js('resources/js/app.js', 'public/js')
    .postCss('resources/css/app.css', 'public/css', [
        //
    ]);
*/

mix.js('resources/js/app.js', 'js')
  .postCss('resources/css/app.css', 'css')
  .webpackConfig({
    plugins: [
      new webpack.ProvidePlugin({
        'jQuery': 'jquery'
      })
    ],
  });
