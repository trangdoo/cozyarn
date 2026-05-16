# Hướng dẫn triển khai CozyYarn

Hai cách phổ biến, **chọn 1**:

| Tiêu chí | A. Cloudflare Tunnel | B. VPS (DigitalOcean / Vultr / Hetzner / Contabo) |
|---|---|---|
| Chi phí | Miễn phí | ~$5–6/tháng |
| Thời gian setup | ~10 phút | ~30–60 phút |
| Cần domain riêng | Không (dùng `*.trycloudflare.com`) hoặc có | Nên có (hoặc dùng IP) |
| HTTPS | Có sẵn (Cloudflare ký) | Tự setup Let's Encrypt hoặc proxy qua Cloudflare |
| Máy chạy app | Laptop / PC local | VPS chạy 24/7 |
| Phù hợp | Demo BCCĐ, dev test, webhook SePay | Production thật, web public dài hạn |

**Khuyến nghị cho BCCĐ**: dùng **A. Cloudflare Tunnel** — giáo viên vẫn truy cập được URL HTTPS công khai từ bất kỳ đâu, không cần thuê VPS, SePay vẫn webhook được vào ứng dụng đang chạy trên laptop bạn.

---

## Checklist trước khi triển khai (cả 2 cách đều cần)

Cập nhật `.env` với giá trị production:

```bash
APP_ENV=production
APP_DEBUG=false                                  # ⚠️ bắt buộc tắt — tránh leak stack trace
APP_URL=https://cozyarn.your-domain.com          # URL công khai sẽ chạy
APP_KEY=base64:...                               # giữ nguyên nếu đã có, hoặc php artisan key:generate

# Database
DB_CONNECTION=mysql
DB_HOST=db                                       # 'db' nếu dùng docker-compose; '127.0.0.1' nếu xài MySQL local
DB_PORT=3306
DB_DATABASE=cozyarn
DB_USERNAME=cozyarn
DB_PASSWORD=<random-strong-password>
DB_ROOT_PASSWORD=<another-random-strong-password>

# SePay (xem CHANGELOG.md / SETUP.md)
SEPAY_API_KEY=<key từ trang SePay → Cài đặt → Webhook>
SEPAY_BANK=VCB
SEPAY_BANK_NAME="Vietcombank"
SEPAY_ACCOUNT_NUMBER=<số tài khoản nhận tiền>
SEPAY_ACCOUNT_NAME="CozyYarn"

# Session/cache nên dùng database hoặc redis cho prod
SESSION_DRIVER=database
CACHE_STORE=database
```

Sau khi sửa env:

```bash
php artisan config:clear
php artisan migrate                              # tạo bảng
php artisan storage:link                         # symlink public/storage
npm ci && npm run build                          # build asset Vite
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

---

## A. Triển khai bằng Cloudflare Tunnel ⭐ (khuyến nghị demo)

### A1. Cài cloudflared (Windows)

```powershell
# Cách 1: winget (Windows 10+ đã có sẵn)
winget install --id Cloudflare.cloudflared

# Cách 2: tải binary từ https://github.com/cloudflare/cloudflared/releases
# Lưu vào C:\cloudflared\cloudflared.exe rồi add vào PATH
```

Verify: `cloudflared --version`

### A2. Chuẩn bị Vite asset — chọn 1 trong 2 chiến lược

Đây là điểm dễ vướng nhất: `npm run dev` mặc định inject URL `localhost:5173` cho asset.
Trình duyệt người xem (qua tunnel) sẽ KHÔNG với tới `localhost:5173` của máy bạn → CSS/JS 404.

**Chiến lược 1 (khuyến nghị cho demo): dùng built asset**

Chỉ chạy `npm run build` 1 lần, KHÔNG chạy `npm run dev`. Blade `@vite()` sẽ inject path
tĩnh `/build/assets/home-{hash}.css`. Tunnel làm việc bình thường.

```powershell
cd e:\cozyarn\mycozyarn
npm ci
npm run build
# kết quả: public/build/manifest.json + public/build/assets/*.css|*.js
```

Khi muốn sửa CSS/JS → chạy lại `npm run build`. Không có HMR (phải F5 thủ công)
nhưng đổi lại tunnel ổn định, không lằng nhằng.

**Chiến lược 2 (dev iteration): tunnel cả app + Vite với HMR**

Nếu bạn muốn `npm run dev` (HMR, hot reload) hoạt động qua tunnel:

1. Sửa `.env`:
   ```bash
   APP_URL=https://cozyarn.your-domain.com
   VITE_HMR_HOST=cozyarn.your-domain.com
   ```
2. `vite.config.js` đã được cấu hình sẵn (xem `server.host = '0.0.0.0'` và `server.hmr`):
   khi có `VITE_HMR_HOST` → Vite tự tạo HMR WebSocket qua `wss://`.
3. Cấu hình tunnel route CẢ Vite (xem phần A4 — config.yml có 2 hostname).

**Nếu không cần HMR qua tunnel** → cứ Chiến lược 1, đơn giản hơn nhiều.

### A3. Khởi động ứng dụng local

```powershell
cd e:\cozyarn\mycozyarn

# Terminal 1: backend Laravel
php artisan serve --host=127.0.0.1 --port=8000

# Terminal 2 (chỉ Chiến lược 2): Vite dev server
# npm run dev
```

Hoặc dùng Docker thay cho `artisan serve`:
```powershell
docker compose up -d --build       # nginx listen 8080
```

### A4. Tạo named tunnel với domain riêng

Yêu cầu trước:
- Một **domain** đã đăng ký (bất kỳ TLD nào — `.com`, `.xyz`, `.io.vn`... Mua rẻ ở Namecheap, Porkbun, P.A. Việt Nam).
- Domain đã được **add vào Cloudflare** và đổi nameserver sang Cloudflare (free plan đủ).
  - Vào https://dash.cloudflare.com → **Add site** → nhập domain → Cloudflare scan record cũ → ấn **Continue**.
  - Cloudflare đưa 2 nameserver (vd: `eva.ns.cloudflare.com`, `tom.ns.cloudflare.com`) → vào trang registrar đổi NS sang đó → đợi ~5–30 phút.
  - Verify: chạy `nslookup -type=NS your-domain.com` thấy 2 NS của Cloudflare.

**Tạo tunnel**:

```powershell
# 1. Login — mở browser, chọn domain (cookie cert lưu C:\Users\<bạn>\.cloudflared\cert.pem)
cloudflared tunnel login

# 2. Tạo tunnel có tên "cozyarn"
cloudflared tunnel create cozyarn
# → in ra UUID kiểu: Tunnel credentials written to ...\<uuid>.json
#   Copy giá trị UUID đó cho bước 3.
```

**Tạo file config**: `C:\Users\<bạn>\.cloudflared\config.yml`

```yaml
tunnel: cozyarn
credentials-file: C:\Users\<bạn>\.cloudflared\<UUID>.json

ingress:
  # Route 1: app chính
  - hostname: cozyarn.your-domain.com
    service: http://localhost:8000

  # Route 2: Vite dev server (CHỈ cần nếu dùng Chiến lược 2 — npm run dev)
  - hostname: vite-cozyarn.your-domain.com
    service: http://localhost:5173
    originRequest:
      noTLSVerify: true

  # Catch-all
  - service: http_status:404
```

```powershell
# 3. Tạo DNS CNAME tự động trên Cloudflare
cloudflared tunnel route dns cozyarn cozyarn.your-domain.com
# Nếu dùng Chiến lược 2:
cloudflared tunnel route dns cozyarn vite-cozyarn.your-domain.com

# 4. Chạy tunnel (giữ terminal mở, hoặc đăng ký service ở A6)
cloudflared tunnel run cozyarn
```

Mở `https://cozyarn.your-domain.com` từ browser bất kỳ → thấy trang chủ. HTTPS cert do Cloudflare cấp.

### A5. Cập nhật `.env` cho URL public

```bash
APP_URL=https://cozyarn.your-domain.com

# Chỉ cần nếu Chiến lược 2 (npm run dev):
VITE_HMR_HOST=cozyarn.your-domain.com
```

```powershell
php artisan config:clear
# Nếu đang dùng Chiến lược 2 → restart `npm run dev` để Vite đọc env mới.
```

> **Quan trọng**: nếu trước đó đã `php artisan config:cache` thì phải clear cache. Sai `APP_URL` sẽ làm: login 419 expired, asset URL build sai, redirect sai protocol.

### A6. SePay webhook qua tunnel

Vào trang SePay → Cài đặt → Webhook → Đặt URL:
```
https://cozyarn.your-domain.com/webhook/sepay
```

SePay sẽ gọi tunnel → cloudflared chuyển về localhost → Laravel xử lý. Test:
```powershell
# Từ máy khác (giả lập SePay)
curl -X POST https://cozyarn.your-domain.com/webhook/sepay `
  -H "Authorization: Apikey <SEPAY_API_KEY>" `
  -H "Content-Type: application/json" `
  -d '{"id":1,"gateway":"VCB","transferType":"in","transferAmount":50000,"code":"1"}'
```

### A7. Chạy tunnel như Windows service (tự khởi động cùng máy)

```powershell
# Run as Administrator
cloudflared service install
# Tunnel sẽ chạy nền cùng Windows; không cần mở terminal nữa
# Service đọc config.yml ở đường dẫn mặc định C:\Users\<bạn>\.cloudflared\

# Stop / start / uninstall:
sc stop cloudflared
sc start cloudflared
cloudflared service uninstall
```

### A8. Quick tunnel (URL random, dùng khi KHÔNG có domain)

Nếu bạn chưa có domain nào (hoặc chưa kịp đổi nameserver), có thể bỏ qua A4–A5 và dùng:

```powershell
cloudflared tunnel --url http://localhost:8000
```
→ in URL kiểu `https://random-words-xyz.trycloudflare.com`. **Tunnel sống cùng terminal — đóng terminal = ngắt**. Mỗi lần chạy URL random khác. Không demo dài hạn được, nhưng đủ test webhook 1 lần.

---

## B. Triển khai lên VPS

### B1. Mua VPS

Gợi ý cho student/demo:
- **Vultr** Cloud Compute $6/tháng (1 vCPU, 1GB RAM, Singapore/Tokyo region)
- **DigitalOcean** Droplet $6/tháng
- **Hetzner CX11** ~€4/tháng (Đức/Phần Lan, latency cao hơn từ VN nhưng rẻ nhất)
- **Contabo** Cloud VPS 10 ~€4/tháng (8GB RAM nhưng oversold)

Chọn OS: **Ubuntu 22.04 LTS** (hoặc 24.04).

### B2. SSH vào VPS + cài Docker

```bash
ssh root@<vps-ip>

# Update + cài Docker
apt update && apt upgrade -y
curl -fsSL https://get.docker.com | bash
apt install -y docker-compose-plugin git

# Verify
docker --version
docker compose version
```

### B3. Clone repo + cấu hình

```bash
mkdir -p /var/www && cd /var/www
git clone <repo-url> cozyarn
cd cozyarn/mycozyarn                              # nếu repo có folder con

# Copy + chỉnh env theo checklist ở trên
cp .env.example .env
nano .env                                         # chỉnh APP_ENV, APP_URL, DB_*, SEPAY_*

# Sinh APP_KEY (nếu chưa có)
docker run --rm -v "$PWD:/app" -w /app composer:2 composer install --no-dev --optimize-autoloader
docker run --rm -v "$PWD:/app" -w /app php:8.3-cli php artisan key:generate
```

### B4. Khởi động stack

```bash
docker compose up -d --build

# Lần đầu: chạy migration + storage link
docker compose exec app php artisan migrate --force
docker compose exec app php artisan storage:link

# Verify
docker compose ps                                 # tất cả service phải healthy/running
curl http://localhost:8080                        # test local trên VPS
```

### B5. Mở port + setup domain

**Mở port 80/443 trong firewall:**
```bash
ufw allow 80/tcp && ufw allow 443/tcp && ufw allow 22/tcp && ufw enable
```

**Trỏ domain:**
- Vào DNS provider (Cloudflare, Namecheap, ...) → tạo A record:
  - `cozyarn.your-domain.com` → IP của VPS
- Đợi DNS propagate (vài phút).

**Đổi nginx listen port từ 8080 → 80** (sửa [docker-compose.yml](docker-compose.yml)):
```yaml
  web:
    ports:
      - "80:80"                                   # thay vì "8080:80"
```

Reload: `docker compose up -d`

### B6. HTTPS

**Cách dễ nhất**: proxy qua Cloudflare (free).
1. Add domain vào Cloudflare → đổi nameserver → đợi.
2. Bật "Proxy" ở DNS record (cloud icon orange).
3. SSL/TLS mode: **Full** (Cloudflare ↔ VPS plain HTTP, browser ↔ Cloudflare HTTPS).
4. Truy cập `https://cozyarn.your-domain.com` ✓.

**Cách "đúng chuẩn" (Let's Encrypt trực tiếp trên VPS)**:
```bash
apt install -y certbot
docker compose stop web                           # tạm dừng nginx để certbot dùng port 80
certbot certonly --standalone -d cozyarn.your-domain.com

# Sau khi có cert, mount cert vào nginx + sửa docker/nginx.conf
# (thêm listen 443 ssl + ssl_certificate + ssl_certificate_key)
docker compose up -d web
```

### B7. SePay webhook

Trang SePay → Cài đặt → Webhook → URL = `https://cozyarn.your-domain.com/webhook/sepay`.

### B8. Auto-restart khi VPS reboot

Đã có sẵn `restart: unless-stopped` trong [docker-compose.yml](docker-compose.yml). Sau reboot, `docker compose up` tự chạy lại tất cả service.

---

## Kiểm tra sau khi deploy

Checklist 5 phút:

```bash
# 1. Health endpoint
curl -I https://cozyarn.your-domain.com/up
# Expect: HTTP/2 200

# 2. Trang chủ load đúng skin (hiện đang là mint)
curl -s https://cozyarn.your-domain.com/ | grep -o 'data-theme-skin="[^"]*"'
# Expect: data-theme-skin="mint"

# 3. Plugin banner hiển thị
curl -s https://cozyarn.your-domain.com/ | grep -c "Miễn phí vận chuyển cho mọi đơn"
# Expect: 1

# 4. Admin redirect về login (cần đăng nhập)
curl -s -o /dev/null -w "%{http_code}\n" https://cozyarn.your-domain.com/admin
# Expect: 302

# 5. Webhook reject request không có API key
curl -s -X POST https://cozyarn.your-domain.com/webhook/sepay -d '{}' \
  -H "Content-Type: application/json" \
  -w "\n%{http_code}\n"
# Expect: 401 {"success":false,"message":"Unauthorized"}
```

Nếu cả 5 đều OK → deploy thành công.

---

## Troubleshooting nhanh

**"419 Page Expired"** sau khi đăng nhập:
- Sai `APP_URL` trong .env — phải match URL người dùng truy cập (kể cả https vs http).
- Sau khi sửa: `php artisan config:clear`.

**Webhook 401 dù đã set SEPAY_API_KEY**:
- Header SePay gửi: `Authorization: Apikey <key>` (chữ hoa A, có space). Verify trong logs Laravel.
- Sau khi sửa `.env`: `docker compose exec app php artisan config:clear`.

**CSS không load qua tunnel/Cloudflare**:
- Vite build chưa chạy → chỉ có file source. Phải `npm run build` (KHÔNG `npm run dev`).
- Verify: `ls public/build/manifest.json` phải tồn tại.

**Skin không đổi**:
- File `storage/app/active_theme.txt` không writable → check permission.
- Trên VPS: `docker compose exec app chown -R www-data:www-data storage`.

**SePay webhook không match được order**:
- Order ID phải là số nguyên (Order trong DB), không phải hex.
- Test: vào `/dat-hang-thanh-cong/{id}` thấy ID dạng `1`, `2`, `3` (không phải `DH1A2B3C`).

**Container app exit 0 ngay sau start**:
- Sai opcache config / thiếu env. Check log: `docker compose logs app`.

---

## Production hardening (tuỳ chọn, sau khi demo OK)

1. **Backup DB hàng ngày**:
   ```bash
   # crontab -e
   0 2 * * * docker compose -f /var/www/cozyarn/mycozyarn/docker-compose.yml exec -T db mysqldump -uroot -p$DB_ROOT_PASSWORD cozyarn | gzip > /var/backups/cozyarn-$(date +\%F).sql.gz
   ```

2. **Log rotation**: cấu hình `LOG_CHANNEL=daily` trong .env (đã default).

3. **Monitoring**: dùng UptimeRobot / Better Stack / Healthchecks.io ping `/up` mỗi 5 phút.

4. **Bảo mật**:
   - Đổi SSH port khác 22.
   - Cài fail2ban.
   - Disable root SSH login, chỉ cho phép key auth.

5. **Performance**:
   - Bật Cloudflare cache cho asset (`public/build/*`, `public/themes/*`, `public/images/*`).
   - Bật Brotli/Gzip ở Cloudflare.
