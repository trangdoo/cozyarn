CREATE TABLE cart_items (
    id INT IDENTITY(1,1) PRIMARY KEY,
    cart_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE(),
    CONSTRAINT FK_cart_items_carts FOREIGN KEY (cart_id) REFERENCES carts(id),
    CONSTRAINT FK_cart_items_products FOREIGN KEY (product_id) REFERENCES products(id)
);
