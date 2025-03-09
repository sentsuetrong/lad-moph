import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import { resolve } from 'path'
import fs from 'fs'

// สร้างไฟล์สำหรับเก็บข้อมูล debug
const debugFile = resolve(__dirname, 'logs/asset-debug.log')
// เคลียร์ไฟล์ debug ก่อนเริ่ม build
fs.writeFileSync(debugFile, '', 'utf8')

// Function สำหรับเขียนข้อมูล debug ลงไฟล์
const logToFile = (message: string) => {
  fs.appendFileSync(debugFile, message + '\n', 'utf8')
}

// https://vite.dev/config/
export default defineConfig({
  plugins: [vue(), tailwindcss()],
  resolve: {
    alias: {
      '@': resolve(__dirname, './src')
    }
  },
  build: {
    outDir: '../portal/frontend',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      output: {
        assetFileNames: (assetInfo) => {
          const fileName = assetInfo.names?.[0] || ''

          // บันทึกข้อมูล debug ลงไฟล์
          logToFile('=== Asset Debug ===')
          logToFile(`fileName: ${fileName}`)
          logToFile(`names: ${JSON.stringify(assetInfo.names)}`)
          logToFile(`type: ${assetInfo.type}`)
          logToFile('==================')

          const info = fileName.split('.')
          const extType = info[info.length - 1]
          if (/\.(png|jpe?g|svg|gif|tiff|bmp|ico)$/i.test(fileName)) {
            return `assets/images/[name]-[hash][extname]`
          }
          if (/\.(woff|woff2|eot|ttf|otf)$/i.test(fileName)) {
            return `assets/fonts/[name]-[hash][extname]`
          }
          if (extType === 'css') {
            return `assets/css/[name]-[hash][extname]`
          }
          return `assets/[name]-[hash][extname]`
        },
        chunkFileNames: 'assets/js/[name]-[hash].js',
        entryFileNames: 'assets/js/[name]-[hash].js'
      }
    }
  }
})