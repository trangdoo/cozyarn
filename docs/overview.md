🎯 1. Tổng quan hệ thống

Hệ thống là website thương mại điện tử bán sản phẩm handmade từ len, cho phép:

Người dùng xem và mua sản phẩm
Tương tác (đánh giá, chat, AI…)
Quản trị viên quản lý toàn bộ hệ thống


👥 2. Tác nhân (Actors)
Khách (Guest): chưa đăng nhập
Người dùng (User): đã đăng nhập
Quản trị viên (Admin): quản lý hệ thống
API AI: Calling api from my endpoint
3. Usecase

🎯 1. NHÓM: AUTH (XÁC THỰC)
🔐 1.1 Đăng ký

Actor: Guest

Flow:

1. Nhập thông tin (email, password…)
2. Hệ thống validate
3. Lưu tài khoản
4. Thông báo thành công
🔐 1.2 Đăng nhập

Actor: Guest

Flow:

1. Nhập email + password
2. Hệ thống kiểm tra
3. Nếu đúng → đăng nhập
4. Chuyển trang
🔐 1.3 Đăng xuất

Actor: User

Flow:

1. Click logout
2. Hệ thống xóa session
3. Quay về trang chủ
🔐 1.4 Quên mật khẩu

Actor: Guest/User

Flow:

1. Nhập email
2. Hệ thống gửi link reset
3. Người dùng nhập mật khẩu mới
4. Cập nhật thành công
🎯 2. NHÓM: PRODUCT (SẢN PHẨM)
🛍️ 2.1 Xem danh sách sản phẩm

Actor: Guest/User

Flow:

1. Truy cập trang sản phẩm
2. Hệ thống load danh sách
3. Hiển thị sản phẩm
🔍 2.2 Tìm kiếm sản phẩm

Actor: Guest/User

Flow:

1. Nhập từ khóa
2. Hệ thống tìm kiếm
3. Hiển thị kết quả

🎯 2.3 Lọc sản phẩm

Actor: Guest/User

Flow:

1. Chọn bộ lọc (giá, loại…)
2. Hệ thống lọc
3. Hiển thị danh sách

📄 2.4 Xem chi tiết sản phẩm

Actor: Guest/User

Flow:

1. Click sản phẩm
2. Hiển thị thông tin
3. Hiển thị đánh giá
🎯 3. NHÓM: CART (GIỎ HÀNG)
🛒 3.1 Thêm vào giỏ

Actor: User

Flow:

1. Click “Thêm vào giỏ”
2. Nếu chưa login → chuyển login
3. Nếu đã login → thêm sản phẩm
🛒 3.2 Xem giỏ hàng

Actor: User

Flow:

1. Mở giỏ hàng
2. Hiển thị danh sách
3. Hiển thị tổng tiền
🛒 3.3 Cập nhật giỏ hàng

Actor: User

Flow:

1. Thay đổi số lượng
2. Hệ thống cập nhật
3. Tính lại tổng tiền
🛒 3.4 Xóa sản phẩm

Actor: User

Flow:

1. Click xóa
2. Hệ thống xóa sản phẩm
3. Cập nhật giỏ

🎯 4. NHÓM: ORDER (ĐẶT HÀNG)
📦 4.1 Tạo đơn hàng

Actor: User

Flow:

1. Click đặt hàng
2. Nhập địa chỉ
3. Chọn thanh toán
4. Xác nhận
5. Tạo đơn
💳 4.2 Thanh toán

Actor: User

Flow:

1. Chọn phương thức (COD/online)
2. Xử lý thanh toán
3. Trả kết quả
📑 4.3 Xem đơn hàng

Actor: User

Flow:

1. Truy cập “Đơn hàng”
2. Hiển thị danh sách
3. Xem chi tiết
❌ 4.4 Hủy đơn

Actor: User

Flow:

1. Chọn đơn
2. Click hủy
3. Hệ thống cập nhật trạng thái
🎯 5. NHÓM: REVIEW
⭐ 5.1 Đánh giá sản phẩm

Actor: User

Flow:

1. Chọn sản phẩm
2. Nhập sao + comment
3. Gửi đánh giá
✏️ 5.2 Sửa / xóa đánh giá

Actor: User

Flow:

1. Chọn đánh giá
2. Sửa hoặc xóa
3. Cập nhật hệ thống
🎯 6. NHÓM: USER PROFILE
👤 6.1 Xem thông tin cá nhân

Actor: User

Flow:

1. Truy cập profile
2. Hiển thị thông tin
✏️ 6.2 Cập nhật hồ sơ

Actor: User

Flow:

1. Sửa thông tin
2. Lưu
3. Cập nhật thành công
🔒 6.3 Đổi mật khẩu

Actor: User

Flow:

1. Nhập mật khẩu cũ + mới
2. Xác nhận
3. Cập nhật
🎯 7. NHÓM: CHAT & AI
💬 7.1 Chat với shop

Actor: User

Flow:

1. Gửi tin nhắn
2. Admin nhận
3. Trả lời
🤖 7.2 Chatbot AI

Actor: Guest/User

Flow:

1. Nhập câu hỏi
2. AI xử lý
3. Trả lời
🎯 7.3 Gợi ý sản phẩm (AI)

Actor: User

Flow:

1. Xem sản phẩm
2. Hệ thống phân tích
3. Gợi ý sản phẩm liên quan
🎯 8. NHÓM: ADMIN
🛠️ 8.1 Quản lý sản phẩm

Actor: Admin

Flow:

1. Thêm / sửa / xóa sản phẩm
2. Cập nhật DB
3. Hiển thị thay đổi
📦 8.2 Quản lý đơn hàng

Actor: Admin

Flow:

1. Xem đơn
2. Xác nhận
3. Cập nhật trạng thái
⭐ 8.3 Quản lý đánh giá

Actor: Admin

Flow:

1. Xem đánh giá
2. Xóa vi phạm
👥 8.4 Quản lý người dùng

Actor: Admin

Flow:

1. Xem danh sách user
2. Khóa / mở tài khoản
📊 8.5 Thống kê

Actor: Admin

Flow:

1. Truy cập dashboard
2. Xem doanh thu
3. Xem sản phẩm bán chạy



**Công nghệ : Laravel + Tailwind CSS + MySQL. 
