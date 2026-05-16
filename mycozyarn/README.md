# CozyYarn — Website bán đồ thủ công len & sợi

> Đồ án môn học — Website thương mại điện tử bán nguyên liệu đan móc thủ công (len, sợi, móc, kim, phụ kiện DIY), tích hợp thanh toán chuyển khoản tự động qua **SePay**, hệ thống **skin** đa chủ đề và **plugin** mở rộng.

| | |
|---|---|
| **Phiên bản** | v1.0.0 |
| **Framework** | Laravel 13 (PHP 8.3) |
| **CSDL** | MySQL 8 |
| **Build asset** | Vite + Tailwind v4 |
| **Triển khai** | Docker Compose / Cloudflare Tunnel |

---

## 1. Giới thiệu hệ thống

**CozyYarn** là website thương mại điện tử dành cho người yêu thích thủ công đan móc (knitting/crochet). Người dùng có thể:

- Duyệt sản phẩm theo danh mục (len, sợi cotton, móc, kim, kit DIY...), xem chi tiết, đánh giá.
- Thêm vào giỏ, mua ngay, đặt hàng với 2 phương thức thanh toán: **COD** hoặc **Chuyển khoản (VietQR + SePay webhook tự xác nhận)**.
- Quản lý đơn hàng cá nhân, xác nhận đã nhận, yêu cầu trả hàng, theo dõi lịch sử trạng thái.
- Chat trực tiếp với admin (hộp thư hỗ trợ), nhận thông báo realtime, viết bài review sản phẩm.

Phía **admin** có dashboard quản lý người dùng / sản phẩm / danh mục / blog / đơn hàng / chat / thông báo, kèm 2 module nâng cao: **Skin** (đổi giao diện runtime) và **Plugin** (bật/tắt tính năng mở rộng).

### Mục tiêu hệ thống
- Cung cấp trải nghiệm mua sắm online đầy đủ cho ngách sản phẩm thủ công.
- Tự động hóa khâu xác nhận thanh toán chuyển khoản (không cần admin thủ công đối soát).
- Demo kiến trúc Laravel có khả năng mở rộng: tách Interface/Repository/Service, plugin hook-based, theme manager.

### Đối tượng sử dụng
- **Khách (guest):** xem sản phẩm, đọc blog, đăng ký tài khoản.
- **Khách hàng (user):** mua hàng, theo dõi đơn, chat với shop, đánh giá sản phẩm.
- **Quản trị viên (admin):** quản lý toàn bộ hệ thống, đổi skin/plugin, xem doanh thu.

---

## 2. Danh sách thành viên & phân công nhiệm vụ

> **TODO:** Điền thông tin nhóm trước khi nộp.

| STT | Họ và tên | MSSV | Vai trò | Nhiệm vụ chính | Đóng góp |
|----:|---|---|---|---|---:|
| 1 | Tống Gia Bảo (Trưởng nhóm) | 23810310199 | Backend + DevOps | Auth, Checkout, SePay webhook, Docker, Deploy, Admin CRUD (product/category/blog/order), Chat  | 100% |
| 2 | Đỗ Thị Thuỳ Trang | 23810310199 | Frontend + QA | Layout, Tailwind, các trang public + user, Plugin, Skin UI, kiểm thử, viết tài liệu | 100% |

**Giảng viên hướng dẫn:** Giảng viên Cấn Đức Điệp

---

## 3. Danh sách chức năng chính

### Phía khách hàng (`/`)
- Đăng ký / đăng nhập / quên mật khẩu (mật khẩu hash SHA-256 phía client + bcrypt server).
- Trang chủ với danh sách sản phẩm nổi bật, sản phẩm mới, blog.
- Chi tiết sản phẩm + đánh giá sao + bình luận.
- Tìm kiếm, lọc theo danh mục.
- Giỏ hàng (thêm/sửa/xóa/clear).
- Mua ngay / thanh toán nhiều sản phẩm.
- Checkout: chọn COD hoặc chuyển khoản → hiện trang VietQR → SePay webhook tự đánh dấu đã thanh toán.
- Trang đơn hàng cá nhân: tabs "Đang xử lý / Đã hoàn tất / Đã hủy / Trả hàng", hủy đơn, yêu cầu trả hàng, xác nhận đã nhận.
- Thông báo (notifications), Chat 1-1 với admin.
- Blog: đọc bài, like, lưu bài đã thích.
- Trang tài khoản: đổi thông tin, đổi mật khẩu.

### Phía quản trị (`/admin`)
- Dashboard tổng quan (số đơn, doanh thu).
- CRUD: Người dùng / Sản phẩm / Danh mục / Bài viết blog.
- Quản lý đơn hàng: lọc theo trạng thái, đổi trạng thái, xem lịch sử trạng thái, xử lý yêu cầu trả hàng/hủy.
- Hộp thư chat: trả lời tin nhắn từ khách.
- Thông báo: gửi broadcast.
- **Skin** (`/admin/giao-dien`): đổi giao diện runtime giữa **Cozy / Mint / Night**.
- **Plugin** (`/admin/plugin`): bật/tắt plugin mở rộng (WelcomeBanner, DiscountCode).

### Tính năng nâng cao
- **Tích hợp SePay** — webhook tự động cập nhật trạng thái thanh toán khi khách chuyển khoản.
- **Theme system** — 3 skin chuyển đổi runtime, không cần build lại frontend.
- **Plugin system tự xây** — hook-based (kiểu WordPress) với 2 cơ chế `render` và `filter`.
- **Hash mật khẩu client-side** (SHA-256 trên trình duyệt trước khi gửi server).
- **Bảo vệ webhook bằng API key** (`Authorization: Apikey <key>`).
- **Versioning** — file `VERSION`, helper `AppVersion::full()` hiển thị `1.0.0 (build abc1234)` ở footer admin.
- **Triển khai Docker** — Dockerfile multi-stage + docker-compose 3 service (app/web/db).
- **Triển khai qua Cloudflare Tunnel** — đưa app local lên domain HTTPS công khai.

---

## 4. Công nghệ sử dụng

| Mảng | Công nghệ |
|---|---|
| **Backend** | PHP 8.3, Laravel 13, Eloquent ORM, Blade templating |
| **CSDL** | MySQL 8.0 |
| **Frontend** | Tailwind CSS v4, Vite, JavaScript thuần (Web Crypto API cho SHA-256) |
| **Theme** | [igaster/laravel-theme](https://github.com/igaster/laravel-theme) ^2.0 |
| **Thanh toán** | [SePay](https://sepay.vn/) (VietQR + webhook) |
| **Container** | Docker + Docker Compose, Nginx 1.27 alpine, PHP 8.3 FPM |
| **Tunnel** | Cloudflare Tunnel (cloudflared) |
| **Dev tools** | Composer, npm, Laravel Pint, PHPUnit, Laravel Pail |
| **Versioning** | Semantic Versioning + Keep a Changelog |

---

## 5. Kiến trúc hệ thống

```
┌──────────────────────────────────────────────────────────────┐
│                          BROWSER                             │
│  Blade view + Tailwind CSS + Vite bundle + skin override     │
└──────────────────┬───────────────────────────────────────────┘
                   │ HTTPS
┌──────────────────▼───────────────────────────────────────────┐
│                  Nginx (web container)                       │
│              static + reverse proxy → php-fpm                │
└──────────────────┬───────────────────────────────────────────┘
                   │ FastCGI
┌──────────────────▼───────────────────────────────────────────┐
│              PHP-FPM 8.3 (app container)                     │
│  ┌────────────────────────────────────────────────────────┐  │
│  │  Routes → Controllers → Services → Repositories        │  │
│  │  Middleware: auth, ApplyTheme, RoleAdmin               │  │
│  │  Plugin hooks: home.top, checkout.payment_extra,       │  │
│  │                checkout.total                          │  │
│  └────────────────────────────────────────────────────────┘  │
│  Session (file)   ◄──── Cart, orders UI, reviews, chat       │
│       │                                                      │
└───────┼──────────────────────────────────────────────────────┘
        │                                  ▲
        │ syncPaymentFromDb()              │
        ▼                                  │ POST /webhook/sepay
┌──────────────────────────────────┐   ┌───┴────────┐
│        MySQL 8 (db container)    │   │   SePay    │
│  orders, transactions, users…    │◄──┤   (bank)   │
└──────────────────────────────────┘   └────────────┘
```

**Pattern chính:**
- **Hybrid storage:** UI dùng session (cart, đơn hàng, review, chat) — chỉ `orders` + `transactions` lưu DB để webhook SePay khớp được bằng `order_id`.
- **Repository pattern:** `App\Interfaces\*` → `App\Repositories\*` (bind trong `AppServiceProvider`).
- **Plugin hook pattern:** `App\Plugin\Hook::render()` / `Hook::filter()` — kiểu WordPress, dispatcher tự xây.
- **Theme manager:** `App\Support\ThemeManager` wrap igaster/laravel-theme, persist active theme ở `storage/app/active_theme.txt`.

Chi tiết kiến trúc xem [PROJECT_FILES.md](PROJECT_FILES.md).

---

## 6. Hướng dẫn cài đặt

### Yêu cầu hệ thống
- PHP **8.3+**, Composer 2.x
- Node.js **18+**, npm
- MySQL **8.0+** (hoặc MariaDB 10.6+)
- (Tùy chọn) Docker Desktop hoặc Docker Engine + Compose plugin

### Cài thủ công (local dev)

```bash
# 1. Clone
git clone <repo-url> cozyarn
cd cozyarn/mycozyarn

# 2. Cài phụ thuộc
composer install
npm install

# 3. Tạo .env
cp .env.example .env       # PowerShell: copy .env.example .env
php artisan key:generate

# 4. Cấu hình DB trong .env (DB_DATABASE, DB_USERNAME, DB_PASSWORD)
#    + Cấu hình SePay nếu cần test thanh toán (SEPAY_API_KEY, SEPAY_BANK, ...)

# 5. Migrate + storage link
php artisan migrate --seed
php artisan storage:link

# 6. Build asset
npm run build              # production
# hoặc npm run dev cho hot-reload

# 7. Chạy server
php artisan serve          # http://127.0.0.1:8000
```

### Cài bằng Docker (khuyến nghị)

```bash
cp .env.example .env       # chỉnh DB_*, SEPAY_*
docker compose up -d --build

# Lần đầu
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link

# → mở http://localhost:8080
```

Chi tiết & xử lý lỗi: xem [SETUP.md](SETUP.md) và [DEPLOY.md](DEPLOY.md).

---

## 7. Hướng dẫn chạy project

| Lệnh | Mục đích |
|---|---|
| `php artisan serve` | Chạy server Laravel ở `http://127.0.0.1:8000` |
| `npm run dev` | Vite dev server + HMR (cần chạy song song với `serve`) |
| `npm run build` | Build asset production (`public/build/`) |
| `php artisan migrate --seed` | Tạo bảng + seed dữ liệu mẫu |
| `php artisan migrate:fresh --seed` | Reset DB + seed lại từ đầu |
| `php artisan test` | Chạy test (PHPUnit) |
| `php artisan theme:refresh-cache` | Refresh cache khi thêm skin mới |
| `docker compose up -d --build` | Khởi động full stack qua Docker |
| `docker compose logs -f app` | Xem log container app |

**Script tổng hợp** (đã định nghĩa trong `composer.json`):
```bash
composer setup       # composer install + .env + key + migrate + npm install + npm build
composer dev         # chạy đồng thời server / queue / pail / vite (cần concurrently)
```

---

## 8. Tài khoản demo

Sau khi `php artisan migrate --seed`, các tài khoản sau được tạo sẵn:

| Vai trò | Email | Mật khẩu |
|---|---|---|
| Admin | `tonggiabao8825@gmail.com` | `@Tonggiabao88` |
| User mẫu | `tonggiabao.media@gmail.com` | `@Tonggiabao88` |

> **TODO:** Đối chiếu lại với `database/seeders/` thực tế; nếu seeder của bạn dùng credential khác thì cập nhật bảng trên.

**Tài khoản SePay sandbox** (chỉ cần nếu muốn test thanh toán end-to-end):
- Đăng ký tại https://sepay.vn → vào **Cài đặt → Webhook** → set URL `https://<your-host>/webhook/sepay` + lấy `SEPAY_API_KEY` để đưa vào `.env`.

---

## 9. Hình ảnh minh họa hệ thống

> **TODO:** Chụp lại các màn hình chính của hệ thống, đặt vào thư mục `docs/screenshots/` và liên kết bên dưới.

| Màn hình | Ảnh |
|---|---|
| Trang chủ (skin Cozy) | `![Home Cozy](docs/screenshots/home-cozy.png)` |
| Trang chủ (skin Mint / Night) | `![Home Mint](docs/screenshots/home-mint.png)` |
| Chi tiết sản phẩm | `![Product](docs/screenshots/product.png)` |
| Giỏ hàng | `![Cart](docs/screenshots/cart.png)` |
| Checkout + VietQR | `![Checkout](docs/screenshots/checkout.png)` |
| Admin dashboard | `![Admin](docs/screenshots/admin.png)` |
| Admin → Quản lý Skin | `![Skin Admin](docs/screenshots/admin-skin.png)` |
| Admin → Quản lý Plugin | `![Plugin Admin](docs/screenshots/admin-plugin.png)` |

---

## 10. Link demo

- **Video demo (Google Drive / YouTube):** _TODO: dán link sau khi quay xong_
- **Bản deploy online:** _TODO: dán link sau khi deploy (vd: `https://cozyarn.your-domain.com`)_
- **Source code GitHub:** _TODO: dán link repo public_

---

## 11. Kết quả thực hiện

### Đã hoàn thành (v1.0.0)
- ✓ Hệ thống auth đầy đủ (đăng ký, đăng nhập, quên mật khẩu, hash SHA-256 + bcrypt)
- ✓ Catalog sản phẩm + tìm kiếm + danh mục
- ✓ Giỏ hàng & checkout với 2 phương thức thanh toán
- ✓ **Tích hợp SePay** — webhook khớp đơn theo `order_id` numeric
- ✓ Quản lý đơn hàng phía user (hủy / trả hàng / xác nhận nhận)
- ✓ Chat 1-1 user ↔ admin
- ✓ Blog + like
- ✓ Thông báo
- ✓ Admin CRUD (user / product / category / blog / order / chat / notification)
- ✓ **3 skin** (Cozy / Mint / Night) — đổi runtime qua admin
- ✓ **Plugin system** + 2 plugin mẫu (WelcomeBanner, DiscountCode)
- ✓ **Versioning** + Changelog
- ✓ **Dockerfile + docker-compose** sẵn sàng deploy

### Hướng phát triển
- Tích hợp thêm cổng thanh toán: MoMo, VNPay, ZaloPay.
- Đẩy realtime chat lên Reverb/Pusher (hiện đang polling).
- Migrate session storage → DB hoàn toàn để hỗ trợ multi-server.
- Thêm gợi ý sản phẩm dựa trên lịch sử mua.
- Mobile app (React Native / Flutter).
- Test coverage > 70% (hiện chỉ có placeholder test).

---

## 12. Cấu trúc thư mục (rút gọn)

```
mycozyarn/
├── app/
│   ├── Http/Controllers/      # Controllers (public + Admin/)
│   ├── Http/Middleware/       # ApplyTheme, RoleAdmin
│   ├── Models/                # User, Order, Transaction, ...
│   ├── Plugin/                # Plugin abstract + Hook dispatcher
│   ├── Plugins/               # WelcomeBanner, DiscountCode (plugin mẫu)
│   ├── Repositories/ Interfaces/ Services/
│   └── Support/               # Cart, ThemeManager, AppVersion, ...
├── config/
│   └── themes.php             # khai báo skin Cozy/Mint/Night
├── database/
│   ├── migrations/
│   └── seeders/
├── public/
│   ├── build/                 # Vite output
│   └── themes/{cozy,mint,night}/skin.css
├── resources/
│   ├── shop.php               # data sản phẩm tĩnh
│   ├── blog.php               # data bài viết tĩnh
│   ├── css/  js/
│   └── views/                 # Blade templates
├── routes/web.php
├── storage/app/active_theme.txt
├── storage/app/plugins.json
├── docker-compose.yml  Dockerfile  docker/nginx.conf
├── VERSION  CHANGELOG.md
├── SETUP.md  DEPLOY.md  PROJECT_FILES.md
└── README.md  (file này)
```

Chi tiết: [PROJECT_FILES.md](PROJECT_FILES.md).

---

## 13. Tài liệu tham khảo

- [Laravel 13 docs](https://laravel.com/docs/13.x)
- [igaster/laravel-theme](https://github.com/igaster/laravel-theme)
- [SePay webhook documentation](https://docs.sepay.vn/lap-trinh-cong-thanh-toan.html)
- [Tailwind CSS v4](https://tailwindcss.com/docs)
- [Cloudflare Tunnel docs](https://developers.cloudflare.com/cloudflare-one/connections/connect-networks/)

---

## License

Dự án phục vụ mục đích học tập (đồ án môn học). Code Laravel framework gốc giữ giấy phép MIT.
