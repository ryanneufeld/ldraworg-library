const mix = require('laravel-mix');

mix.autoload({
    jquery: ['$', 'jQuery', 'window.jQuery'],
});
   
mix.js('resources/js/app.js', 'public/assets/js')
    .postCss('resources/css/app.css', 'public/assets/css')
    .postCss('resources/css/ldbi.css', 'public/assets/css')
    .version();
mix.js('resources/js/history.js', 'public/assets/js').sourceMaps().version();
mix.copy('resources/ldbi/js', 'public/assets/ldbi/js');
mix.copy('resources/ldbi/textures/cube', 'public/assets/ldbi/textures/cube');
mix.copy('resources/ldbi/textures/materials', 'public/assets/ldbi/textures/materials');
mix.copy('resources/js/ldbi.js', 'public/assets/js');
mix.copy('resources/js/ldraworgscene.js', 'public/assets/js');
mix.copy('resources/js/WebGL.js', 'public/assets/js');