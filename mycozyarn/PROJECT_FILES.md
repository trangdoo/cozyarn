Hướng dẫn cấu trúc dự án & ý nghĩa từng file
===========================================

Tài liệu này mô tả nhanh mục đích của các thư mục và file chính trong dự án, và chỉ dẫn nơi bạn sẽ viết code khi phát triển tính năng mới.

1. Gốc dự án (root)
- `artisan`: CLI của Laravel — dùng lệnh artisan (migration, serve, queue, ...).
- `composer.json`: khai báo phụ thuộc PHP và autoload.
- `package.json`: phụ thuộc frontend (npm) và script build.
- `phpunit.xml`: cấu hình cho PHPUnit.
- `vite.config.js`: cấu hình build frontend (Vite).
- `README.md`: mô tả chung dự án.
- `SETUP.md`: (mới) hướng dẫn cài đặt & chạy dự án.

2. Thư mục `app/` — nơi chính để code PHP ứng dụng
- `app/Http/Controllers/`: các `Controller` xử lý request/response. Viết controller để nhận request, gọi `Service` hoặc `Repository`, trả view hoặc JSON.
  - Ví dụ: [app/Http/Controllers](app/Http/Controllers/)
- `app/Http/Requests/`: các lớp `FormRequest` để validate dữ liệu đầu vào trước khi vào controller.
- `app/Interfaces/`: định nghĩa các interface cho repository (tách rời implementation), ví dụ: [app/Interfaces/AuthRepositoryInterface.php](app/Interfaces/AuthRepositoryInterface.php), [app/Interfaces/ProductRepositoryInterface.php](app/Interfaces/ProductRepositoryInterface.php). Khi viết repository, implement interface tương ứng.
- `app/Models/`: các model Eloquent (ORM) tương ứng bảng DB: `User`, `Product`, `Category`, `Order`, `Cart`, ... (ví dụ [app/Models/Product.php](app/Models/Product.php)).
- `app/Repositories/`: lớp tương tác trực tiếp với DB (có thể dùng Eloquent). Các `Repository` thường implement interface trong `app/Interfaces`.
- `app/Services/`: chứa business logic, orchestration giữa repository và các thao tác khác. Controller nên gọi Service thay vì thao tác DB trực tiếp.
- `app/Providers/`: service providers (ví dụ [app/Providers/AppServiceProvider.php](app/Providers/AppServiceProvider.php)) — đăng ký binding interface => implementation nếu cần.

Quy ước: giao tiếp giữa lớp bằng Dependency Injection — trong `AppServiceProvider` bind Interface -> Repository.

3. `bootstrap/`
- `bootstrap/app.php`: bootstrap ứng dụng Laravel.
- `bootstrap/cache/`: cache config và services (do framework sinh ra).

4. `config/`
- Chứa cấu hình ứng dụng (app, auth, database, mail, queue, session, ...). Chỉnh ở đây khi cần thay đổi cấu hình toàn cục.

5. `database/`
- `all_tables_and_seeds.mysql.sql`: file SQL backup/seed mà dự án kèm theo — có thể import trực tiếp.
- `factories/`: model factories để seed dữ liệu giả.
- `migrations/`: migration tạo cấu trúc DB (nếu dùng).
- `seeders/`: các seeder để populate dữ liệu mẫu.

6. `public/`
- `index.php`: entrypoint cho web server — trỏ document root tới `public/` khi deploy.

7. `resources/`
- `resources/css/`, `resources/js/`: code frontend (vite, mix), bạn sẽ chỉnh CSS/JS tại đây.
- `resources/views/`: các Blade views (HTML templates). Cấu trúc hiện có: `auth/`, `cart/`, `checkout/`, `components/`, `layouts/`, `product/`, `user/`.
  - Ví dụ: [resources/views/home.blade.php](resources/views/home.blade.php)

8. `routes/`
- `routes/web.php`: định nghĩa route web (GET/POST) liên kết tới controller.
- `routes/console.php`: lệnh artisan custom.

9. `storage/`
- Nơi lưu file tạm, cache, logs, uploaded files. Khi deploy cần đảm bảo thư mục có quyền ghi.

10. `tests/`
- `tests/TestCase.php`: base test class.
- `tests/Feature/` và `tests/Unit/`: viết feature và unit tests ở đây.

11. `vendor/`
- Thư viện bên thứ ba (do Composer quản lý). Không sửa trực tiếp.


Hướng dẫn nhanh cách thêm một feature (ví dụ: entity `X`)
---------------------------------------------------------
1) Tạo migration (nếu cần):

```bash
php artisan make:migration create_x_table --create=x
```

2) Tạo `Model`:

```bash
php artisan make:model X -m
```

3) Tạo `Interface` và `Repository`:
- Thêm interface: `app/Interfaces/XRepositoryInterface.php` — khai báo phương thức cần thiết.
- Tạo repository implementation: `app/Repositories/XRepository.php` implement interface.

4) Bind interface -> repository trong `AppServiceProvider`:

Trong `register()` hoặc `boot()`:

```php
$this->app->bind(
    App\\Interfaces\\XRepositoryInterface::class,
    App\\Repositories\\XRepository::class
);
```

5) Tạo `Service` (nếu cần): `app/Services/XService.php` để chứa business logic.

6) Tạo `Controller`:

```bash
php artisan make:controller XController --resource
```

7) Tạo `Request` validation:

```bash
php artisan make:request StoreXRequest
```

8) Thêm route vào `routes/web.php`:

```php
Route::resource('x', XController::class);
```

9) Tạo views trong `resources/views/x/`.


Các lệnh hữu ích
- `php artisan make:controller`, `make:model`, `make:request`, `make:migration`, `make:seeder`, `make:test`.
- `composer install`, `composer dump-autoload`.
- `npm install`, `npm run dev`, `npm run build`.
- `php artisan migrate`, `php artisan db:seed`.

Gợi ý cấu trúc code và chuẩn đặt tên
- Controller: `PascalCaseController` (ví dụ `ProductController`).
- Model: tên số ít, PascalCase (ví dụ `Product`).
- Table: số nhiều, snake_case (ví dụ `products`).
- Repository interface: `EntityRepositoryInterface`.
- Repository implementation: `EntityRepository`.
- Service: `EntityService`.

Lưu ý đặc biệt trong repo này
- Dự án đã tách `Interfaces`, `Repositories`, `Services` — tuân thủ pattern này để giữ code testable và dễ thay thế implementation.
- Nếu muốn thay đổi logic data access, chỉnh trong repository, không chỉnh controller.
