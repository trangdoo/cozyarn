CREATE TABLE messages (
    id INT IDENTITY(1,1) PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT,
    content NVARCHAR(MAX) NOT NULL,
    is_read BIT DEFAULT 0,
    created_at DATETIME DEFAULT GETDATE(),
    CONSTRAINT FK_messages_sender FOREIGN KEY (sender_id) REFERENCES users(id),
    CONSTRAINT FK_messages_receiver FOREIGN KEY (receiver_id) REFERENCES users(id)
);
