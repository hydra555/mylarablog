import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import fs from 'fs'; // Добавляем импорт модуля fs

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        host: 'afblog.loc',
        port: 5173,
        cors: true,
        https: {
            key: fs.readFileSync('D:/OS/OSPanel/data/ssl/projects/afblog.loc/cert.key'),
            cert: fs.readFileSync('D:/OS/OSPanel/data/ssl/projects/afblog.loc/cert.crt'),
        },
    },
});
