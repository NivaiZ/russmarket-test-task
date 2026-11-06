import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'

// https://vite.dev/config/
export default defineConfig(({ mode }) => ({
  plugins: [
    vue(),
    ...(mode === 'development' ? [vueDevTools()] : []),
    {
      name: 'basic-auth-styles',
      transformIndexHtml: {
        order: 'post',
        handler(html) {
          return html.replace(/<link\b[^>]*>/g, (tag) => {
            if (!tag.includes('rel="stylesheet"') || !tag.includes('href="/')) {
              return tag
            }

            return tag.replace(/\s+crossorigin(?:="[^"]*")?/, '')
          })
        },
      },
    },
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    proxy: {
      '/api': 'http://127.0.0.1:8000',
    },
  },
}))
