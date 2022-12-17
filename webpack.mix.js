const mix = require('laravel-mix');
const webpack = require('webpack');

mix.copy('resources/ldbi', 'public/assets/ldbi');
mix.copy('resources/semantic/dist', 'public/assets/fomantic');
mix.copy('resources/js/*.js', 'public/assets/js');
mix.copy('resources/css/*.css', 'public/assets/css');
