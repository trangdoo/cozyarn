CREATE TABLE reviews (
    id INT IDENTITY(1,1) PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    rating INT NOT NULL CHECK(rating >= 1 AND rating <= 5),
    comment NVARCHAR(MAX),
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE(),
    CONSTRAINT FK_reviews_users FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT FK_reviews_products FOREIGN KEY (product_id) REFERENCES products(id)
);

