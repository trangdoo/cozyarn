import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            // Mọi file đang được @vite() tham chiếu trong resources/views/ phải có ở đây
            // — nếu không, production build sẽ thiếu file → manifest.json không có entry
            // → @vite() throw "Unable to locate file in Vite manifest".
            input: [
                // Default Laravel template (giữ phòng các view ẩn còn dùng)
                'resources/css/app.css',
                'resources/js/app.js',
                // Auth pages (login/register/forgot/reset)
                'resources/css/auth.css',
                'resources/js/auth-validate.js',
                // Public site (home/shop/product/blog/checkout/cart)
                'resources/css/home.css',
                'resources/js/home.js',
                'resources/js/public.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        // Bind 0.0.0.0 để cloudflared (hoặc bất kỳ tunnel/reverse-proxy nào) reach được Vite.
        // Localhost-only sẽ làm Vite vô hình với client truy cập qua tunnel.
        host: '0.0.0.0',
        // HMR (hot reload) chỉ cấu hình khi đi qua tunnel — set VITE_HMR_HOST trong .env.
        // Bỏ trống → HMR chạy local kiểu mặc định.
        hmr: process.env.VITE_HMR_HOST ? {
            host: process.env.VITE_HMR_HOST,
            clientPort: 443,
            protocol: 'wss',
        } : undefined,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
