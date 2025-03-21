import react from "@vitejs/plugin-react";
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';
import svgr from "vite-plugin-svgr";

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/index.tsx',
            refresh: true,
        }), 
        react(), 
        svgr(),
    ],
    resolve: {
        alias: {
            "@": "/resources"
        }
    },
    build: {
        sourcemap: true
    }
}); 