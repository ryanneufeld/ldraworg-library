import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel([
            'resources/css/app.css',
            'resources/css/markdown.css',
            'resources/js/app.js',
            'resources/js/ace.js',
        ]),
    ],
});