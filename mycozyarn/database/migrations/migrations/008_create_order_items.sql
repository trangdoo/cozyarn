CREATE TABLE order_items (
    id INT IDENTITY(1,1) PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE(),
    CONSTRAINT FK_order_items_orders FOREIGN KEY (order_id) REFERENCES orders(id),
    CONSTRAINT FK_order_items_products FOREIGN KEY (product_id) REFERENCES products(id)
);
