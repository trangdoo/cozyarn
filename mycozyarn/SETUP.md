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

14a) Bảo mật mật khẩu — client-side hashing
- Trình duyệt sẽ băm SHA-256(password) trước khi gửi lên server (xem `resources/js/auth-validate.js`).
- Server cũng băm SHA-256 nếu input chưa phải hex 64 ký tự (`app/Support/ClientPasswordNormalizer.php`),
  rồi bcrypt giá trị đó để lưu DB. Đường nào cũng cho `bcrypt(SHA256(plaintext))`.
- Yêu cầu: **HTTPS** trong production (Web Crypto API yêu cầu secure context).
- Nếu DB từ phiên bản cũ (chỉ có bcrypt(plaintext)) → các tài khoản cũ phải đặt lại mật khẩu
  qua flow "Quên mật khẩu" hoặc admin update; hoặc chạy lại `php artisan migrate:fresh --seed` trên môi trường dev.

15) Triển khai ra production (tổng quan)
- Thiết lập web server (Nginx/Apache) trỏ vào public/.
- Cài đặt biến môi trường trên máy chủ.
- Chạy composer install --no-dev --optimize-autoloader và npm run build.
- Thiết lập cache config: php artisan config:cache, route: php artisan route:cache, views: php artisan view:cache.

16) Triển khai bằng Docker (khuyến nghị)

Yêu cầu: Docker Desktop / Docker Engine + Compose plugin.

```bash
# Build image + chạy stack (app php-fpm, nginx, mysql)
docker compose up -d --build

# Lần đầu: chạy migration + tạo storage link
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link

# Mở trình duyệt
# http://localhost:8080
```

Stack gồm 3 service (xem [docker-compose.yml](docker-compose.yml)):
- `app`   — PHP 8.3 FPM + Composer/Node build sẵn (Dockerfile multi-stage)
- `web`   — Nginx 1.27 alpine, cấu hình tại [docker/nginx.conf](docker/nginx.conf)
- `db`    — MySQL 8.0, persist data ở volume `cozyarn-db`

Để deploy lên VPS:
```bash
# Trên VPS đã có Docker
git clone <repo>.git cozyarn && cd cozyarn/mycozyarn
cp .env.example .env  # rồi sửa APP_URL, DB_*, SEPAY_* cho phù hợp
docker compose up -d --build
```

17) Quản lý phiên bản
- Phiên bản app trong file [VERSION](VERSION) (semver) — hiển thị ở footer admin sidebar.
- Changelog đầy đủ trong [CHANGELOG.md](CHANGELOG.md).
- Mỗi release đánh git tag: `git tag v1.0.0 && git push --tags`.
- Helper `App\Support\AppVersion::full()` trả về `1.0.0 (build abc1234)`.

18) Tùy biến giao diện (Skin)
- 3 theme đăng ký sẵn trong [config/themes.php](config/themes.php): `cozy` (mặc định), `mint`, `night`.
- Admin → "Giao diện" để đổi runtime.
- Thêm skin mới:
  1. Tạo `public/themes/{key}/skin.css`
  2. Thêm `'{key}' => ['extends' => 'cozy', 'views-path' => 'themes/{key}', 'asset-path' => 'themes/{key}']` vào `config/themes.php`
  3. Thêm metadata vào `App\Support\ThemeManager::META`
  4. `php artisan theme:refresh-cache`

19) Tùy biến chức năng (Plugin)
- Plugin sống trong `app/Plugins/{Tên}/Plugin.php`, kế thừa `App\Plugin\Plugin`.
- Đăng ký listener vào `App\Plugin\Hook` ở method `boot()`.
- Admin → "Plugin" để bật/tắt. Trạng thái lưu `storage/app/plugins.json`.
- Hook có sẵn:
  - `home.top` (render) — chèn HTML lên đầu layout public.
  - `checkout.payment_extra` (render) — chèn UI dưới mục payment ở checkout.
  - `checkout.total` (filter) — biến đổi tổng tiền checkout (vd: chiết khấu).
- 2 plugin mẫu: `WelcomeBanner`, `DiscountCode` — xem [app/Plugins/](app/Plugins/).

---

Nếu bạn muốn, mình có thể:
- Thêm hướng dẫn cụ thể cho Windows/WSL
- Tạo script setup.sh / setup.ps1 tự động hóa các bước
