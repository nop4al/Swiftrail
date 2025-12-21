import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/js/app.js', 'resources/css/app.css'],
      refresh: true,
    }),
    vue(),
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './resources/js'),
    },
  },
  server: {
    host: '0.0.0.0',
    port: 5173,
    // HMR dengan auto-detect hostname
    middlewareMode: false,
    hmr: {
      protocol: 'http',
      host: 'localhost', // Default ke localhost
      port: 5173,
    }
  },
})