-- Tổng hợp toàn bộ lệnh tạo bảng và seed cho MySQL

-- ===== MIGRATIONS =====

-- 001_create_users.sql
CREATE TABLE users (
	id INT AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(100) NOT NULL,
	email VARCHAR(100) NOT NULL UNIQUE,
	password VARCHAR(255) NOT NULL,
	phone VARCHAR(20),
	address TEXT,
	avatar VARCHAR(255),
	role VARCHAR(20) DEFAULT 'user', -- user, admin
	status VARCHAR(20) DEFAULT 'active', -- active, blocked
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
-- 002_create_categories.sql
CREATE TABLE categories (
	id INT AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(100) NOT NULL,
	slug VARCHAR(150) NOT NULL UNIQUE,
	description TEXT,
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
-- 003_create_products.sql
CREATE TABLE products (
	id INT AUTO_INCREMENT PRIMARY KEY,
	category_id INT,
	name VARCHAR(255) NOT NULL,
	slug VARCHAR(255) NOT NULL UNIQUE,
	description TEXT,
	price DECIMAL(10, 2) NOT NULL,
	stock_quantity INT DEFAULT 0,
	thumbnail VARCHAR(255),
	status VARCHAR(20) DEFAULT 'active', -- active, inactive
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	CONSTRAINT FK_products_categories FOREIGN KEY (category_id) REFERENCES categories(id)
);
-- 004_create_product_images.sql
CREATE TABLE product_images (
	id INT AUTO_INCREMENT PRIMARY KEY,
	product_id INT NOT NULL,
	image_url VARCHAR(255) NOT NULL,
	is_primary TINYINT(1) DEFAULT 0,
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	CONSTRAINT FK_product_images_products FOREIGN KEY (product_id) REFERENCES products(id)
);
-- 005_create_carts.sql
CREATE TABLE carts (
	id INT AUTO_INCREMENT PRIMARY KEY,
	user_id INT NOT NULL,
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	CONSTRAINT FK_carts_users FOREIGN KEY (user_id) REFERENCES users(id)
);
-- 006_create_cart_items.sql
CREATE TABLE cart_items (
	id INT AUTO_INCREMENT PRIMARY KEY,
	cart_id INT NOT NULL,
	product_id INT NOT NULL,
	quantity INT DEFAULT 1,
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	CONSTRAINT FK_cart_items_carts FOREIGN KEY (cart_id) REFERENCES carts(id),
	CONSTRAINT FK_cart_items_products FOREIGN KEY (product_id) REFERENCES products(id)
);
-- 007_create_orders.sql
CREATE TABLE orders (
	id INT AUTO_INCREMENT PRIMARY KEY,
	user_id INT NOT NULL,
	total_amount DECIMAL(10, 2) NOT NULL,
	shipping_address TEXT NOT NULL,
	payment_method VARCHAR(20) DEFAULT 'cod', -- cod, online
	payment_status VARCHAR(20) DEFAULT 'pending', -- pending, paid, failed
	status VARCHAR(20) DEFAULT 'pending', -- pending, confirmed, shipping, delivered, cancelled
	note TEXT,
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	CONSTRAINT FK_orders_users FOREIGN KEY (user_id) REFERENCES users(id)
);
-- 008_create_order_items.sql
CREATE TABLE order_items (
	id INT AUTO_INCREMENT PRIMARY KEY,
	order_id INT NOT NULL,
	product_id INT NOT NULL,
	quantity INT NOT NULL,
	price DECIMAL(10, 2) NOT NULL,
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	CONSTRAINT FK_order_items_orders FOREIGN KEY (order_id) REFERENCES orders(id),
	CONSTRAINT FK_order_items_products FOREIGN KEY (product_id) REFERENCES products(id)
);
-- 009_create_reviews.sql
CREATE TABLE reviews (
	id INT AUTO_INCREMENT PRIMARY KEY,
	user_id INT NOT NULL,
	product_id INT NOT NULL,
	rating INT NOT NULL CHECK(rating >= 1 AND rating <= 5),
	comment TEXT,
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	CONSTRAINT FK_reviews_users FOREIGN KEY (user_id) REFERENCES users(id),
	CONSTRAINT FK_reviews_products FOREIGN KEY (product_id) REFERENCES products(id)
);
-- 010_create_messages.sql
CREATE TABLE messages (
	id INT AUTO_INCREMENT PRIMARY KEY,
	sender_id INT NOT NULL,
	receiver_id INT,
	content TEXT NOT NULL,
	is_read TINYINT(1) DEFAULT 0,
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	CONSTRAINT FK_messages_sender FOREIGN KEY (sender_id) REFERENCES users(id),
	CONSTRAINT FK_messages_receiver FOREIGN KEY (receiver_id) REFERENCES users(id)
);

-- ===== SEEDS =====

-- 001_seed_users.sql
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@mycozyarn.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Customer 1', 'customer@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');
-- 002_seed_categories.sql
INSERT INTO categories (name, slug, description) VALUES
('Áo len', 'ao-len', 'Các mẫu áo len đan tay thủ công ấm áp'),
('Mũ len', 'mu-len', 'Mũ len dễ thương đa dạng kiểu dáng'),
('Khăn choàng', 'khan-choang', 'Khăn len cho mùa đông lông cừu siêu mềm'),
('Thú nỉ (Amigurumi)', 'thu-ni', 'Đồ chơi thú nỉ móc bằng tay');

-- Lưu ý: Bạn cần copy nội dung các file SQL trên vào đúng vị trí tương ứng trong file này để chạy một lần trên MySQL.
