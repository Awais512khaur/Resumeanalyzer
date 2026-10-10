CREATE TABLE IF NOT EXISTS auth_rate_limits (
    bucket_key CHAR(64) NOT NULL,
    attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    window_started_at DATETIME NOT NULL,
    blocked_until DATETIME DEFAULT NULL,
    PRIMARY KEY (bucket_key),
    KEY idx_auth_rate_limits_window (window_started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
