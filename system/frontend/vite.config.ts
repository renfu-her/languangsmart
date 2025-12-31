import path from 'path';
import { defineConfig, loadEnv } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, '.', '');
    return {
      server: {
        port: 3000,
        host: '0.0.0.0',
      },
      plugins: [react()],
      define: {
        'process.env.API_KEY': JSON.stringify(env.GEMINI_API_KEY),
        'process.env.GEMINI_API_KEY': JSON.stringify(env.GEMINI_API_KEY)
      },
      resolve: {
        alias: {
          '@': path.resolve(__dirname, '.'),
        }
      },
      base: '/',
      build: {
        // 構建到 public 目錄（根路徑 /）
        outDir: path.resolve(__dirname, '../../public'),
        emptyOutDir: false, // 不清空 public 目錄（保留 index.php, favicon.ico 等 Laravel 文件）
        rollupOptions: {
          input: path.resolve(__dirname, 'index.html'),
          output: {
            manualChunks: (id) => {
              // Separate node_modules into vendor chunks
              if (id.includes('node_modules')) {
                // React and React DOM in one chunk
                if (id.includes('react') || id.includes('react-dom')) {
                  return 'react-vendor';
                }
                // React Router in its own chunk
                if (id.includes('react-router')) {
                  return 'router-vendor';
                }
                // All other node_modules
                return 'vendor';
              }
            },
          },
        },
        chunkSizeWarningLimit: 600,
      },
    };
});
