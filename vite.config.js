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
    host: '0.0.0.0', // Listen di semua IP
    port: 5173,
    // HMR auto-detect dari client - jadi bisa diakses dari mana saja
    hmr: {
      protocol: 'http',
      host: process.env.VITE_HMR_HOST, // Will be set by client if undefined
      port: process.env.VITE_HMR_PORT || 5173,
    }
  },
})