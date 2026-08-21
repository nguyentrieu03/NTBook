import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // CSS
                'resources/css/app.css', 
                'resources/css/style.css',
                'resources/css/admin.css',
                'resources/css/modules/dashboard/style.css',
                'resources/css/modules/products/style.css',
                'resources/css/modules/products/form.css',
                'resources/css/modules/categories/style.css',

                // JS
                'resources/js/app.js',
                'resources/js/admin.js',
                'resources/js/vendor-fullcalendar.js',
                'resources/js/vendor-chartjs.js',
                'resources/js/vendors.js',
                'resources/js/2026.js',
                'resources/js/runtime.js',
                'resources/js/modules/dashboard/script.js',
                'resources/js/modules/products/script.js',
                'resources/js/modules/products/form.js',
                'resources/js/modules/categories/script.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0',    // nghe mọi interface, docker port mapping mới forward được
        port: 5173,
        strictPort: true,   // báo lỗi ngay nếu port bị chiếm, không tự chuyển sang 5174
        cors: true,         // app chạy ở cổng 8080, asset tải từ 5173 (khác origin) nên cần bật CORS
        hmr: {
            host: 'localhost', // browser dùng localhost để kết nối WebSocket HMR
            port: 5173,
        },
        // Windows host bind-mount vào container Linux: inotify KHÔNG bắn event,
        // nên phải poll thì Vite mới thấy file thay đổi (HMR/rebuild mới chạy).
        watch: {
            usePolling: true,
            interval: 300,
        },
    },
});
