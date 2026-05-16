# Changelog

Lịch sử phát hành của CozyYarn. Theo chuẩn [Semantic Versioning](https://semver.org/)
(`MAJOR.MINOR.PATCH`) và phong cách [Keep a Changelog](https://keepachangelog.com/).

## [1.0.0] — 2026-05-16

### Added — Tùy biến giao diện (Skin)
- Cài và cấu hình package `igaster/laravel-theme` (^2.0).
- 3 theme đã đăng ký trong [config/themes.php](config/themes.php):
  - `cozy` (mặc định) — giao diện gốc của shop, hồng-kem.
  - `mint` — tông xanh bạc hà, extend `cozy`.
  - `night` — chế độ tối tím-hồng, extend `cozy`.
- Trang quản trị `/admin/giao-dien` cho phép admin đổi skin runtime.
- Middleware `App\Http\Middleware\ApplyTheme` đọc theme đang chọn từ
  `storage/app/active_theme.txt` ở mỗi request.

### Added — Tùy biến chức năng (Plugin)
- Hệ plugin tự xây trong `app/Plugin/`:
  - `Plugin` (abstract) — base class, khai báo `key/name/description/version/boot()`.
  - `Hook` — dispatcher kiểu WordPress với 2 cơ chế: `render` (gộp HTML) và
    `filter` (pipe value qua listener).
  - `PluginManager` — discover, enable/disable, boot.
- Trạng thái plugin lưu `storage/app/plugins.json`.
- Trang quản trị `/admin/plugin` để bật/tắt plugin.
- 2 plugin mẫu:
  - `WelcomeBanner` — render hook `home.top`, hiển thị banner free-ship trang chủ.
  - `DiscountCode` — filter hook `checkout.total`, áp dụng mã `COZY10` (giảm 10%)
    và `YARN50K` (giảm 50.000 ₫ cho đơn từ 200.000 ₫).

### Added — Quản lý phiên bản
- File [VERSION](VERSION) chứa số phiên bản app.
- Helper `App\Support\AppVersion` đọc VERSION + git commit hash hiện hành.
- Phiên bản hiển thị ở footer admin dashboard và mục About.
- Tag git `v1.0.0`.

### Added — Triển khai (Docker)
- [Dockerfile](Dockerfile) build image PHP 8.3 FPM + Composer + Node build.
- [docker-compose.yml](docker-compose.yml) gồm 3 service: `app` (php-fpm),
  `web` (nginx), `db` (mysql 8).
- [docker/nginx.conf](docker/nginx.conf) cấu hình vhost chuẩn cho Laravel.

### Added — Tích hợp thanh toán SePay
- `POST /webhook/sepay` nhận webhook, xác thực `Authorization: Apikey <key>`.
- Đơn checkout giờ được ghi vào DB (`orders` table) để webhook khớp được bằng `id`.
- Trang `checkout.success` hiển thị VietQR + STK + nội dung CK khi `payment=bank`.

### Changed
- Layouts `public.blade.php` và `admin.blade.php` chèn `<link>` skin.css của theme đang dùng,
  load sau Vite để override.
- Trang home & checkout có thêm điểm cắm plugin (`home.top`, `checkout.payment_extra`).

### Notes
- Phần frontend (cart, đơn hàng, đánh giá, thông báo) vẫn dùng session làm storage
  như thiết kế ban đầu — DB chỉ giữ Order/Transaction cho thanh toán.
