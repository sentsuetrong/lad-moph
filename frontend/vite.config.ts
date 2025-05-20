import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite' // หากใช้ Tailwind CSS v3+ กับ Vite โดยตรง
// หรือ import tailwindcss from 'tailwindcss' และ postcss from 'postcss' หากต้องการตั้งค่า postcss เอง
import { resolve } from 'path'
import fs from 'fs'

// --- Debug Logging Setup ---
// Ensure logs directory exists
const logsDir = resolve(__dirname, 'logs');
if (!fs.existsSync(logsDir)) {
  fs.mkdirSync(logsDir, { recursive: true });
  console.log(`Created logs directory: ${logsDir}`);
}
const debugFile = resolve(logsDir, 'asset-debug.log');
// Clear or create debug file
fs.writeFileSync(debugFile, `Log started at ${new Date().toISOString()}\n\n`, 'utf8');

const logToFile = (message: string) => {
  fs.appendFileSync(debugFile, message + '\n', 'utf8');
}
logToFile('Vite config loaded.');

// --- Cleanup Plugin ---
function cleanupPlugin() {
  return {
    name: 'cleanup-plugin',
    buildStart() {
      // ตรวจสอบว่า outDir ถูกกำหนดค่าใน config หลักหรือไม่
      // @ts-ignore (Vite's resolved config might not be available here yet in this exact form)
      const outDirConfig = (this.config?.build?.outDir) || '../portal/frontend';
      const frontendDir = resolve(__dirname, outDirConfig);

      logToFile(`[CleanupPlugin] Target directory for cleanup: ${frontendDir}`);
      if (fs.existsSync(frontendDir)) {
        console.log(`[CleanupPlugin] Cleaning up directory: ${frontendDir}`);
        logToFile(`[CleanupPlugin] Cleaning up directory: ${frontendDir}`);
        try {
          fs.rmSync(frontendDir, { recursive: true, force: true });
          logToFile(`[CleanupPlugin] Successfully cleaned directory: ${frontendDir}`);
        } catch (error: any) {
          console.error(`[CleanupPlugin] Error cleaning directory ${frontendDir}:`, error);
          logToFile(`[CleanupPlugin] Error cleaning directory ${frontendDir}: ${error.message}`);
        }
      } else {
        logToFile(`[CleanupPlugin] Directory not found, no cleanup needed: ${frontendDir}`);
      }
    }
  }
}

// https://vitejs.dev/config/
export default defineConfig(({ command, mode }) => {
  logToFile(`DefineConfig called. Command: ${command}, Mode: ${mode}`);

  // Base path สำหรับ application ของคุณเมื่อ deploy ไปยัง subfolder
  // ถ้า URL ของคุณคือ lad.moph.test/portal/ และ assets อยู่ใน /portal/frontend/
  // base ควรจะเป็น '/portal/frontend/'
  // สิ่งนี้สำคัญมากเพื่อให้ Vite สร้าง URL ของ asset ได้ถูกต้อง
  const base = 'http://localhost:5173/'; // '/portal/frontend/';
  logToFile(`Using base path: ${base}`);

  const outDir = '../portal/frontend'; // ควรตรงกับที่ cleanupPlugin และ server vhost ชี้ไป
  logToFile(`Using outDir: ${outDir}`);

  return {
    plugins: [
      cleanupPlugin(), // ควรมาก่อนเพื่อให้ทำงานก่อน build จริง
      vue(),
      tailwindcss(), // ตรวจสอบการตั้งค่า Tailwind ของคุณอีกครั้ง
    ],
    base: base, // <--- จุดสำคัญที่เพิ่มเข้ามา
    resolve: {
      alias: {
        '@': resolve(__dirname, './src')
      }
    },
    build: {
      outDir: outDir,
      emptyOutDir: false, // ตั้งเป็น false เพราะ cleanupPlugin จัดการแล้ว หรือจะใช้ true และลบ plugin ก็ได้
      manifest: true,
      cssMinify: 'lightningcss', // ดีแล้ว
      rollupOptions: {
        output: {
          assetFileNames: (assetInfo) => {
            // assetInfo.name คือชื่อไฟล์ดั้งเดิม (เช่น 'logo.png', 'styles.css')
            const originalName = assetInfo.name || '';

            logToFile('--- [Rollup Asset] ---');
            logToFile(`  Original Name: ${originalName}`);
            logToFile(`  Type: ${assetInfo.type}`); // 'asset'

            let subfolder = 'others';
            if (originalName) {
              if (/\.(png|jpe?g|svg|gif|tiff|bmp|ico)$/i.test(originalName)) {
                subfolder = 'images';
              } else if (/\.(woff|woff2|eot|ttf|otf)$/i.test(originalName)) {
                subfolder = 'fonts';
              } else if (originalName.endsWith('.css')) {
                subfolder = 'css';
              }
            }
            const path = `assets/${subfolder}/[name]-[hash][extname]`;
            logToFile(`  Output Path: ${path}`);
            logToFile('------------------------');
            return path;
          },
          chunkFileNames: 'assets/js/[name]-[hash].js',
          entryFileNames: 'assets/js/[name]-[hash].js'
        }
      }
    },
    server: {
      // CORS สำหรับ development server ของ Vite
      // อนุญาตให้ CodeIgniter (ที่รันบน origin อื่น) เรียก main.ts จาก Vite dev server ได้
      cors: {
        origin: '*' // หรือระบุ origin ของ CodeIgniter backend ของคุณ เช่น 'http://lad.moph.test'
      },
      // Proxy API requests ไปยัง CodeIgniter backend ของคุณ
      // ถ้า API ของคุณอยู่ที่ lad.moph.test/portal/api/...
      // target ควรเป็น 'http://lad.moph.test' และ rewrite path
      // หรือถ้า API อยู่ที่ lad.moph.test/api/... (ตามที่คุณตั้งไว้) ก็ถูกต้องแล้ว
      proxy: {
        '/api': { // Request จาก frontend ที่ขึ้นต้นด้วย /api
          target: 'http://lad.moph.test/api', // จะถูก proxy ไปที่ http://lad.moph.test/api/...
          changeOrigin: true,
          // secure: false, // ถ้า backend target เป็น https และมี self-signed certificate
          // rewrite: (path) => path.replace(/^\/api/, '/portal/api') // หาก API จริงอยู่ที่ /portal/api
          logLevel: 'debug', // ดู log ของ proxy ใน console
        }
      },
      // ทำให้ Vite dev server เข้าถึงได้จาก network (ไม่ใช่แค่ localhost)
      // host: '0.0.0.0', // หรือ true
      // port: 5173, // port มาตรฐานของ Vite
    },
    // เพิ่มการ logging ตอน build เสร็จ
    hooks: {
      'vite:buildEnd': (err?: Error) => {
        if (err) {
          logToFile(`Build failed: ${err.message}`);
        } else {
          logToFile('Build completed successfully.');
        }
      }
    }
  }
})