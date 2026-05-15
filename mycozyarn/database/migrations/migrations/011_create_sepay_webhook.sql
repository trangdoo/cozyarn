CREATE TABLE transactions (
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,
    sepay_id        BIGINT NOT NULL UNIQUE,
    gateway         VARCHAR(100) NOT NULL,
    transaction_date DATETIME NOT NULL,
    account_number  VARCHAR(100),
    sub_account     VARCHAR(250),
    code            VARCHAR(250),
    amount_in       BIGINT NOT NULL DEFAULT 0,
    amount_out      BIGINT NOT NULL DEFAULT 0,
    accumulated     BIGINT NOT NULL DEFAULT 0,
    content         TEXT,
    reference_code  VARCHAR(255),
    body            JSON NOT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_account (account_number, transaction_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;