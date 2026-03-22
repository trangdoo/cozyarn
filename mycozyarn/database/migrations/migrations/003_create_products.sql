CREATE TABLE products (
    id INT IDENTITY(1,1) PRIMARY KEY,
    category_id INT,
    name NVARCHAR(255) NOT NULL,
    slug NVARCHAR(255) NOT NULL UNIQUE,
    description NVARCHAR(MAX),
    price DECIMAL(10, 2) NOT NULL,
    stock_quantity INT DEFAULT 0,
    thumbnail NVARCHAR(255),
    status NVARCHAR(20) DEFAULT 'active', -- active, inactive
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE(),
    CONSTRAINT FK_products_categories FOREIGN KEY (category_id) REFERENCES categories(id)
);
