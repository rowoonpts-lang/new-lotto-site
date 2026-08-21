ALTER TABLE g5_config
ADD COLUMN cf_ip TEXT NULL;

CREATE TABLE IF NOT EXISTS l_super_admin_ip_log (
    lsail_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    mb_id VARCHAR(20) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    first_access_at DATETIME NOT NULL,
    last_access_at DATETIME NOT NULL,
    access_count INT UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (lsail_id),
    UNIQUE KEY uq_super_admin_ip (mb_id, ip_address),
    KEY idx_super_admin_ip_last_access (last_access_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
