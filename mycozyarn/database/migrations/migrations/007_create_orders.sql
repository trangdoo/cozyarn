CREATE TABLE orders (
    id INT IDENTITY(1,1) PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    shipping_address NVARCHAR(MAX) NOT NULL,
    payment_method NVARCHAR(20) DEFAULT 'cod', -- cod, online
    payment_status NVARCHAR(20) DEFAULT 'pending', -- pending, paid, failed
    status NVARCHAR(20) DEFAULT 'pending', -- pending, confirmed, shipping, delivered, cancelled
    note NVARCHAR(MAX),
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE(),
    CONSTRAINT FK_orders_users FOREIGN KEY (user_id) REFERENCES users(id)
);
