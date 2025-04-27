import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/calendario.js',
                'resources/js/calendar-instructor.js',
            ],
            refresh: true,
        }),
    ],
});
