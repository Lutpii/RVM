import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import basicSsl from '@vitejs/plugin-basic-ssl'
import { resolve } from 'path'
import { readFileSync } from 'fs'

// The AI service (BackEnd/ai_service/app.py) requires an X-API-Key header on
// every route, including /stream — a plain <img> tag can't send custom
// headers, so on the real kiosk Nginx injects it when proxying /ai-stream ->
// the AI service (see deploy/nginx-rvm.conf). This dev proxy plays the same
// role for `npm run dev`, reading the key straight out of the AI service's
// own .env instead of duplicating it here so the two can't drift apart.
function readAiServiceApiKey() {
  try {
    const envPath = resolve(__dirname, '../BackEnd/ai_service/.env')
    const match = readFileSync(envPath, 'utf-8').match(/^AI_API_KEY=(.+)$/m)
    return match ? match[1].trim() : ''
  } catch {
    return ''
  }
}
const AI_SERVICE_API_KEY = readAiServiceApiKey()
const AI_SERVICE_URL = 'http://127.0.0.1:5000'

export default defineConfig({
  plugins: [vue(), basicSsl()],
  resolve: {
    alias: {
      '@': resolve(__dirname, 'src'),
    },
  },
  build: {
    rollupOptions: {
      output: {
        manualChunks(id) {
          if (id.includes('node_modules')) {
            if (id.includes('chart.js') || id.includes('vue-chartjs')) return 'vendor-charts'
            if (id.includes('jsqr') || id.includes('/qrcode/')) return 'vendor-qr'
            if (id.includes('axios')) return 'vendor-axios'
            if (id.includes('@phosphor-icons')) return 'vendor-icons'
            return 'vendor'
          }
        },
      },
    },
  },
  server: {
    host: true,
    port: 5173,
    https: true,
    proxy: {
      '/api': {
        target: 'http://127.0.0.1:8000',
        changeOrigin: true,
      },
      '/storage': {
        target: 'http://127.0.0.1:8000',
        changeOrigin: true,
      },
      // Mirrors nginx-rvm.conf's /ai-stream location for local dev, so the
      // kiosk item-scan step's live preview (<img :src="cameraStreamUrl">
      // in RvmSessionView.vue, pointed at /ai-stream) actually resolves to
      // something instead of a 404/black box.
      '/ai-stream': {
        target: AI_SERVICE_URL,
        changeOrigin: true,
        rewrite: () => '/stream',
        configure: (proxy) => {
          proxy.on('proxyReq', (proxyReq) => {
            proxyReq.setHeader('X-API-Key', AI_SERVICE_API_KEY)
          })
        },
      },
    },
  },
})
