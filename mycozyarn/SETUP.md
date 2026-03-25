Hướng dẫn cài đặt & chạy dự án
=================================

Mô tả ngắn: hướng dẫn này có các bước cơ bản để cài đặt, cấu hình và chạy ứng dụng Laravel có trong repository.

1) Yêu cầu hệ thống
- PHP 8.0+ (hoặc phiên bản tương thích với Laravel của dự án)
- Composer
- Node.js (16+) và npm hoặc pnpm
- MySQL / MariaDB (hoặc database tương thích)

2) Clone dự án

Mở terminal và chạy:

```bash
git clone <repository-url>
cd <repository-folder>
```

3) Cài đặt phụ thuộc PHP

```bash
composer install
```

4) Cài đặt phụ thuộc frontend

```bash
npm install
# hoặc pnpm install
```

5) Thiết lập file môi trường

Sao chép file mẫu và chỉnh thông tin kết nối DB, mail, URL:

```bash
cp .env.example .env
# Trên Windows (PowerShell): copy .env.example .env
```

Chỉnh các biến quan trọng trong .env:
- APP_NAME, APP_URL
- DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD

6) Tạo application key

```bash
php artisan key:generate
```

7) Thiết lập storage (nếu cần)

```bash
php artisan storage:link
```

8) Chuẩn bị cơ sở dữ liệu

Hai lựa chọn:
- Chạy migration & seeder:

```bash
php artisan migrate --seed
```

- Hoặc import file SQL có sẵn (nếu repository có file SQL):

Sử dụng MySQL client hoặc phpMyAdmin để import `database/all_tables_and_seeds.mysql.sql`.

9) Chạy build frontend (dev hoặc production)

```bash
# Chạy trong dev (hot-reload)
npm run dev

# Hoặc build production
npm run build
```

10) Chạy server (local)

```bash
php artisan serve
# Mặc định: http://127.0.0.1:8000
```

11) Chạy test

```bash
./vendor/bin/phpunit
# hoặc
php artisan test
```

12) Lệnh thường dùng
- composer install — cài phụ thuộc PHP
- npm install — cài phụ thuộc frontend
- php artisan migrate --seed — chạy migration + seed
- php artisan storage:link — tạo symbolic link cho storage
- npm run dev / npm run build — build frontend
- php artisan serve — chạy server dev

13) Ghi chú cho Windows
- Trên Windows, dùng PowerShell hoặc WSL nếu gặp vấn đề quyền hoặc đường dẫn. Một số lệnh Linux (ví dụ cp) cần dùng lệnh tương đương Windows (copy) hoặc chạy trong Git Bash/WSL.

14) Triển khai ra production (tổng quan)
- Thiết lập web server (Nginx/Apache) trỏ vào public/.
- Cài đặt biến môi trường trên máy chủ.
- Chạy composer install --no-dev --optimize-autoloader và npm run build.
- Thiết lập cache config: php artisan config:cache, route: php artisan route:cache, views: php artisan view:cache.

---

Nếu bạn muốn, mình có thể:
- Thêm hướng dẫn cụ thể cho Windows/WSL
- Tạo script setup.sh / setup.ps1 tự động hóa các bước
