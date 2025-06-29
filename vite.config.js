import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/book-search.js', 'resources/js/member-search.js', 'resources/js/loan-autocomplete.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
