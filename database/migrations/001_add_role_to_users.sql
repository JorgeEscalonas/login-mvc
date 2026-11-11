USE login_mvc;

ALTER TABLE users
    ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'user' AFTER password;

UPDATE users SET role = 'admin' WHERE email = 'admin@example.com';