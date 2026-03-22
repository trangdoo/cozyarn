CREATE TABLE product_images (
    id INT IDENTITY(1,1) PRIMARY KEY,
    product_id INT NOT NULL,
    image_url NVARCHAR(255) NOT NULL,
    is_primary BIT DEFAULT 0,
    created_at DATETIME DEFAULT GETDATE(),
    CONSTRAINT FK_product_images_products FOREIGN KEY (product_id) REFERENCES products(id)
);
