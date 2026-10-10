CREATE TABLE IF NOT EXISTS password_reset_otps (
    user_id INT(11) NOT NULL,
    otp_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (user_id),
    CONSTRAINT fk_password_reset_otps_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
