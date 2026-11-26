USE login_mvc;

CREATE TABLE IF NOT EXISTS password_reset_tokens (
    email VARCHAR(150) PRIMARY KEY,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    CONSTRAINT fk_password_reset_email
        FOREIGN KEY (email) REFERENCES users(email) ON DELETE CASCADE
) ENGINE=InnoDB;