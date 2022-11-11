const mix = require('laravel-mix');
const webpack = require('webpack');

mix.js('resources/js/app.js', 'js');

mix.js('resources/js/edit.js', 'js');

mix.postCss('resources/css/app.css', 'css')
  .postCss('resources/css/edit.css', 'css');
