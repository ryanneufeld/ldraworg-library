const mix = require('laravel-mix');
const webpack = require('webpack');

mix.copy('resources/ldbi/js', 'public/assets/ldbi/js');
mix.copy('resources/ldbi/textures/cube', 'public/assets/ldbi/textures/cube');
mix.copy('resources/ldbi/textures/materials', 'public/assets/ldbi/textures/materials');
mix.copy('resources/semantic/dist/*.min.*', 'public/assets/fomantic');
mix.copy('resources/semantic/dist/components/*.min.*', 'public/assets/fomantic/components');
mix.copy('resources/semantic/dist/themes/default/assets/fonts/*icons.woff*', 'public/assets/fomantic/themes/default/assets/fonts');
mix.copy('resources/js/*.js', 'public/assets/js');
mix.copy('resources/css/*.css', 'public/assets/css');
