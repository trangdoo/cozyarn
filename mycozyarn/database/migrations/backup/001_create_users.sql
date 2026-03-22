CREATE TABLE users (
    id INT IDENTITY(1,1) PRIMARY KEY,
    name NVARCHAR(100) NOT NULL,
    email NVARCHAR(100) NOT NULL UNIQUE,
    password NVARCHAR(255) NOT NULL,
    phone NVARCHAR(20),
    address NVARCHAR(MAX),
    avatar NVARCHAR(255),
    role NVARCHAR(20) DEFAULT 'user', -- user, admin
    status NVARCHAR(20) DEFAULT 'active', -- active, blocked
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE()
);
