CREATE TABLE carts (
    id INT IDENTITY(1,1) PRIMARY KEY,
    user_id INT NOT NULL,
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE(),
    CONSTRAINT FK_carts_users FOREIGN KEY (user_id) REFERENCES users(id)
);
